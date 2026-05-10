# Employee Onboarding & Verification Design

**Date:** 2026-05-11
**Status:** Approved

## Overview

After a new user signs in with Google for the first time, they are shown a one-time onboarding screen asking whether they are a student or an employee. Students proceed directly to the portal. Employees select their office and can immediately use the portal as a student while their verification is pending. A notice banner appears in the portal until the request is resolved.

---

## Data Model

Three new nullable columns on the `users` table:

| Column | Type | Description |
|---|---|---|
| `onboarding_status` | enum(`pending_employee`, `rejected`) nullable | Null means fully resolved (student or approved staff). |
| `pending_office_id` | FK → `offices` nullable | The office the user applied to. Cleared on approval or rejection. |
| `onboarding_completed_at` | timestamp nullable | Set when the user completes the initial role-selection screen. Null = onboarding not yet shown. |

New PHP enum: `App\Enums\OnboardingStatus` with cases `PendingEmployee` and `Rejected`.

**State transitions:**

```
New user (Google login)
  → student role assigned, onboarding_completed_at = null
  → /portal/onboarding shown

User picks "Student"
  → onboarding_completed_at = now()
  → redirect to /portal/tickets

User picks "Employee" + selects office
  → onboarding_status = pending_employee
  → pending_office_id = <selected>
  → onboarding_completed_at = now()
  → redirect to /portal/tickets (portal is accessible, notice banner shown)

Admin approves
  → onboarding_status = null, pending_office_id = null
  → syncRoles(['staff']), attach to office
  → approval email sent to user

Admin rejects
  → onboarding_status = rejected, pending_office_id = null
  → rejection email sent to user (notice banner shown in portal)

User re-applies (from rejected banner)
  → onboarding_status = pending_employee, pending_office_id = <new office>
  → notification sent to new office's admins, banner updated to pending
```

**Migration note:** Existing users must have `onboarding_completed_at` backfilled to their `created_at` so they are not shown the onboarding screen again.

---

## Onboarding Flow (Portal)

### Routes

| Route | Component | Guard |
|---|---|---|
| `GET /portal/onboarding` | `Livewire\Portal\Onboarding` | auth, `onboarding_completed_at = null` |

All other `/portal/*` routes remain accessible regardless of `onboarding_status`.

### Middleware

The existing portal middleware is extended with one rule only:

```
if onboarding_completed_at = null → redirect /portal/onboarding
```

Pending and rejected employees are NOT redirected — they use the portal normally as students.

### Screen 1 — Role Selection (`/portal/onboarding`)

Two cards: **Student** and **Employee**. User picks one.

- Student: calls `chooseStudent()` → sets `onboarding_completed_at`, redirects to `/portal/tickets`
- Employee: shows step 2 inline (office picker)

### Screen 2 — Office Picker (inline on same page, step 2)

Searchable select of all active offices. Submit button calls `submitEmployeeRequest(officeId)`:
- Sets `onboarding_status = pending_employee`, `pending_office_id`, `onboarding_completed_at`
- Dispatches `EmployeeVerificationRequestedNotification` to all office admins of the selected office
- Redirects to `/portal/tickets`

### Notice Banner (portal layout)

A persistent notice banner is rendered in the portal layout (`layouts/portal.blade.php`) when `onboarding_status` is not null. It sits above the main content area.

**Pending state banner:**
> ⏳ Your request to join **Registrar's Office** is pending verification. You can submit tickets while you wait.

**Rejected state banner:**
> ✗ Your request to join **Registrar's Office** was not approved.
> [Apply to a Different Office] [Continue as Student]

**Apply to a Different Office** — reveals an inline office picker in the banner. User selects a new office and submits, which sets `onboarding_status = pending_employee` with the new `pending_office_id` and sends the notification to the new office's admins.

**Continue as Student** — clears `onboarding_status` (sets to null), keeps `student` role, dismisses the banner.

The banner is a Livewire component (`Livewire\Portal\OnboardingNotice`) included in the portal layout.

---

## Admin Approval Experience (Filament)

### New Resource: `EmployeeRequestResource`

- Model: `User` scoped to `onboarding_status = pending_employee`
- Navigation group: `Users`, navigation sort after `UserResource`
- RBAC scope:
  - `super_admin` → sees all pending requests
  - `office_admin` → sees only requests for their offices (`pending_office_id IN user's office IDs`)
  - `staff` → no access

### Table Columns

- User name, email
- Office name (via `pendingOffice` relation)
- `created_at` (when they applied)

### Record Actions

**ApproveEmployeeAction:**
1. Wraps in DB transaction
2. `$user->syncRoles(['staff'])`
3. `$user->offices()->attach($user->pending_office_id)`
4. `$user->update(['onboarding_status' => null, 'pending_office_id' => null])`
5. Dispatches `EmployeeVerificationResultNotification($user, approved: true)`

**RejectEmployeeAction:**
1. `$user->update(['onboarding_status' => OnboardingStatus::Rejected, 'pending_office_id' => null])`
2. Dispatches `EmployeeVerificationResultNotification($user, approved: false)`

---

## Email Notifications

### `EmployeeVerificationRequestedNotification`

- **Recipients:** all users with `office_admin` role in the selected office
- **Trigger:** user submits employee request
- **Content:** "Juan dela Cruz is requesting verification as an employee of Registrar's Office. [Review in Admin Panel →]"
- **Channel:** `mail`

### `EmployeeVerificationResultNotification`

- **Recipient:** the employee who applied
- **Trigger:** admin approves or rejects
- **Content (approved):** "Your affiliation with Registrar's Office has been verified. You now have staff access. [Go to Portal →]"
- **Content (rejected):** "Your request to join Registrar's Office was not approved. Sign in and check your portal for next steps."
- **Channel:** `mail`

Both notifications use Laravel's `Notification` facade with the `Mail` channel. No queue needed.

**Edge case:** If the selected office has no `office_admin` users, the `EmployeeVerificationRequestedNotification` is sent to nobody. The request still appears in the Filament admin panel for super admins, who can approve or reject it.

---

## Implementation Checklist

1. `OnboardingStatus` enum
2. Migration: add `onboarding_status`, `pending_office_id`, `onboarding_completed_at` to users; backfill existing rows
3. Update `User` model: casts, `pendingOffice` relation
4. Extend portal middleware: redirect to `/portal/onboarding` when `onboarding_completed_at = null`
5. `Livewire\Portal\Onboarding` component (two-step: role picker → office picker)
6. `Livewire\Portal\OnboardingNotice` banner component (pending + rejected states, re-apply flow)
7. Include `OnboardingNotice` in portal layout blade
8. `EmployeeRequestResource` with RBAC scope
9. `ApproveEmployeeAction` Filament action
10. `RejectEmployeeAction` Filament action
11. `EmployeeVerificationRequestedNotification` (mail)
12. `EmployeeVerificationResultNotification` (mail)
13. Update `GoogleAuthController` callback: leave `onboarding_completed_at` null for new users
14. Tests: onboarding flow, middleware redirect, approve/reject actions, banner states, notifications
