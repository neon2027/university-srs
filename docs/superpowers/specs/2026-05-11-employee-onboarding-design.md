# Employee Onboarding & Verification Design

**Date:** 2026-05-11
**Status:** Approved

## Overview

After a new user signs in with Google for the first time, they are shown a one-time onboarding screen asking whether they are a student or an employee. Students proceed directly to the portal. Employees select their office and are blocked pending verification by their office admin.

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
  → redirect to /portal/pending (blocked screen)

Admin approves
  → onboarding_status = null, pending_office_id = null
  → syncRoles(['staff']), attach to office
  → approval email sent to user

Admin rejects
  → onboarding_status = rejected, pending_office_id = null
  → rejection email sent to user

User re-applies (from rejected screen)
  → onboarding_status = pending_employee, pending_office_id = <new office>
  → back to blocked/pending screen
```

**Migration note:** Existing users must have `onboarding_completed_at` backfilled to their `created_at` so they are not shown the onboarding screen again.

---

## Onboarding Flow (Portal)

### Routes

| Route | Component | Guard |
|---|---|---|
| `GET /portal/onboarding` | `Livewire\Portal\Onboarding` | auth, `onboarding_completed_at = null` |
| `GET /portal/pending` | `Livewire\Portal\PendingVerification` | auth, `onboarding_status = pending_employee` |
| `GET /portal/rejected` | `Livewire\Portal\RejectedVerification` | auth, `onboarding_status = rejected` |

### Middleware

A new `EnsureOnboardingComplete` middleware, applied to all `/portal/*` routes except `/portal/onboarding`, `/portal/pending`, and `/portal/rejected`:

```
if onboarding_completed_at = null → redirect /portal/onboarding
if onboarding_status = pending_employee → redirect /portal/pending
if onboarding_status = rejected → redirect /portal/rejected
```

### Screen 1 — Role Selection (`/portal/onboarding`)

Two cards: **Student** and **Employee**. User picks one.

- Student: calls `chooseStudent()` → sets `onboarding_completed_at`, redirects to `/portal/tickets`
- Employee: shows step 2 inline (office picker)

### Screen 2 — Office Picker (inline on same page, step 2)

Searchable select of all active offices. Submit button calls `submitEmployeeRequest(officeId)`:
- Sets `onboarding_status = pending_employee`, `pending_office_id`, `onboarding_completed_at`
- Dispatches `EmployeeVerificationRequestedNotification` to all office admins of the selected office
- Redirects to `/portal/pending`

### Screen 3a — Pending (`/portal/pending`)

Shows office name and a "waiting for approval" message. Sign out link only. No portal access.

### Screen 3b — Rejected (`/portal/rejected`)

Shows rejection message. Two actions:
- **Apply to a Different Office** → reveals an inline office picker on the same page. User selects a new office and submits, which sets `onboarding_status = pending_employee` with the new `pending_office_id`, sends the notification to the new office's admins, and redirects to `/portal/pending`.
- **Continue as Student** → clears `onboarding_status` (sets to null), keeps `student` role, redirects to `/portal/tickets`.

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
- **Content (approved):** "Your affiliation with Registrar's Office has been verified. You can now access the portal. [Go to Portal →]"
- **Content (rejected):** "Your request to join Registrar's Office was not approved. You may apply to a different office or continue as a student. [Try Again →]"
- **Channel:** `mail`

Both notifications use Laravel's `Notification` facade with the `Mail` channel. No queue needed.

**Edge case:** If the selected office has no `office_admin` users, the `EmployeeVerificationRequestedNotification` is sent to nobody. The request still appears in the Filament admin panel for super admins, who can approve or reject it.

---

## Implementation Checklist

1. `OnboardingStatus` enum
2. Migration: add `onboarding_status`, `pending_office_id`, `onboarding_completed_at` to users; backfill existing rows
3. Update `User` model: casts, `pendingOffice` relation
4. `EnsureOnboardingComplete` middleware + register on portal route group
5. `Livewire\Portal\Onboarding` component (two-step: role picker → office picker)
6. `Livewire\Portal\PendingVerification` component
7. `Livewire\Portal\RejectedVerification` component (with re-apply + continue as student)
8. `EmployeeRequestResource` with RBAC scope
9. `ApproveEmployeeAction` Filament action
10. `RejectEmployeeAction` Filament action
11. `EmployeeVerificationRequestedNotification` (mail)
12. `EmployeeVerificationResultNotification` (mail)
13. Update `GoogleAuthController` callback: do not set `onboarding_completed_at` for new users (leave null to trigger onboarding)
14. Tests: onboarding flow, middleware redirects, approve/reject actions, notifications
