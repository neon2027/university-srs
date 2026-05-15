# BUSRS Data Model Design

**Date:** 2026-05-08
**Project:** Bicol University Service Request System (BUSRS)
**Scope:** Database schema for all core entities — users, offices, service catalog, tickets, and support tables.

---

## Stack

- Laravel 13, PHP 8.4
- Filament v5 (admin panel)
- Livewire 4 + Flux UI 2 (client portal)
- `spatie/laravel-permission` for RBAC
- Google OAuth only — no password auth
- Laravel Reverb for real-time WebSockets; `wire:poll` as Hostinger fallback

---

## Section 1: Users & Offices

### Roles (spatie/laravel-permission)

Four roles, no custom permissions layer beyond role-based guards:

| Role | Description |
|---|---|
| `student` | Default role; can submit and track tickets |
| `staff` | Handles assigned tickets within their office(s) |
| `office_admin` | Manages their office's staff, services, and canned responses |
| `super_admin` | Full system access; manages offices, roles, system-wide config |

### `users`

Extends the existing users table. The `password` column is dropped (Google OAuth only).

```
id, name, email (unique), google_id (unique), avatar (nullable),
remember_token, email_verified_at, timestamps, soft_deletes
```

### `offices`

```
id, name, slug (unique), description (nullable), email (nullable),
is_active (bool, default true), sort_order (int, default 0), timestamps
```

### `office_user` (pivot)

Staff can belong to multiple offices. `is_primary` marks the default office for UI and notification routing.

```
id, office_id (FK → offices), user_id (FK → users),
is_primary (bool, default false), timestamps
unique(office_id, user_id)
```

**Filament management:** Office admin assigns staff via a modal action on the Office resource. Staff without a primary office assignment are flagged in the dashboard.

---

## Section 2: Service Catalog

### `service_categories`

Groups services by theme within an office (e.g., "Enrollment", "Infrastructure").

```
id, office_id (FK → offices), name, slug (unique), description (nullable),
is_active (bool, default true), sort_order (int, default 0), timestamps
```

### `service_types`

The actual requestable services (e.g., "Grade Report Request").

```
id, service_category_id (FK → service_categories), name, slug (unique),
description (nullable), sla_days (int, nullable), is_active (bool, default true),
sort_order (int, default 0), timestamps
```

`sla_days` sets the expected resolution window and feeds office performance metrics.

### `service_type_fields`

Field definitions that drive the multi-step Livewire form for a given service type.

```
id, service_type_id (FK → service_types), label, field_type (enum),
options (JSON, nullable — for select/checkbox choices), is_required (bool, default false),
sort_order (int, default 0), timestamps
```

`field_type` enum values: `text`, `textarea`, `select`, `checkbox`, `file`, `date`

Student answers are stored as JSON in `tickets.custom_fields`, keyed by `service_type_fields.id`.

---

## Section 3: Tickets

### `tickets`

The core ticket record. Uses a ULID as a public-facing reference (`TKT-01JV...`) so students can share or track without exposing sequential IDs.

```
id, ulid (unique, public reference), requester_id (FK → users),
office_id (FK → offices), service_type_id (FK → service_types),
assigned_to (FK → users, nullable), status (enum), priority (enum),
subject, description, custom_fields (JSON), resolved_at (nullable),
closed_at (nullable), timestamps, soft_deletes
```

**`status` enum:** `pending`, `assigned`, `in_progress`, `on_hold`, `forwarded`, `resolved`, `closed`, `cancelled`

**`priority` enum:** `low`, `normal`, `high`, `urgent`

### `ticket_history`

Append-only event log that powers the Status Timeline in the client portal. Never updated, only inserted.

```
id, ticket_id (FK → tickets), actor_id (FK → users),
event_type (enum), from_status (nullable), to_status (nullable),
note (nullable), timestamps
```

**`event_type` enum:** `created`, `status_changed`, `assigned`, `forwarded`, `note_added`, `resolved`, `closed`

### `ticket_messages`

Real-time chat between requester and office staff.

```
id, ticket_id (FK → tickets), sender_id (FK → users), body,
is_internal_note (bool, default false — staff-only, invisible to student),
is_canned_response (bool, default false), seen_at (nullable), timestamps
```

### `ticket_attachments`

Files linked to tickets or individual messages. Tracks both original and compressed sizes for storage reporting.

```
id, ticket_id (FK → tickets), ticket_message_id (FK → ticket_messages, nullable),
uploader_id (FK → users), disk, path, original_filename, mime_type,
size_bytes (int), compressed_size_bytes (int), timestamps
```

All uploads pass through the mandatory compression pipeline (`Intervention Image` / `spatie/laravel-medialibrary`).

---

## Section 4: Support Tables

### `forwarding_logs`

Tracks inter-office ticket forwarding and the credit logic decision.

```
id, ticket_id (FK → tickets), from_office_id (FK → offices),
to_office_id (FK → offices), forwarded_by (FK → users),
accepted_by (FK → users, nullable), credit_type (enum, nullable),
note (nullable), forwarded_at, responded_at (nullable), timestamps
```

**`credit_type` enum:** `accept_credit`, `reference_only`

- Row is created with `credit_type = null` when Office A forwards to Office B.
- Office B's admin sets `credit_type` and `responded_at` when accepting.
- `accept_credit` → counts toward both offices' performance metrics.
- `reference_only` → credit stays with Office A only.

### `canned_responses`

Pre-written replies for common scenarios. Scoped to an office or system-wide.

```
id, office_id (FK → offices, nullable), title, body,
created_by (FK → users), is_active (bool, default true), timestamps
```

- `office_id = null` → system-wide, managed by `super_admin`, visible to all staff.
- `office_id` set → visible only to that office's staff.

---

## Full Table Inventory

| Group | Tables |
|---|---|
| Users & Offices | `users`, `offices`, `office_user` |
| Service Catalog | `service_categories`, `service_types`, `service_type_fields` |
| Tickets | `tickets`, `ticket_history`, `ticket_messages`, `ticket_attachments` |
| Support | `forwarding_logs`, `canned_responses` |

---

## Key Design Decisions

- **Google OAuth only:** `password` column dropped from `users`; `google_id` + `avatar` added.
- **ULID on tickets:** Public-facing reference avoids sequential ID exposure; internal `id` stays for FK relations.
- **Hybrid dynamic fields:** `service_type_fields` defines the form schema; `tickets.custom_fields` (JSON) stores answers — avoids EAV complexity while keeping Filament field management straightforward.
- **Append-only history:** `ticket_history` is never updated, only inserted, preserving a complete audit trail for the Status Timeline.
- **Forwarding credit deferred:** `credit_type` is null until Office B responds, allowing the system to show "pending acceptance" state in the dashboard.
- **Staff multi-office:** `office_user` pivot with `is_primary` supports staff who cover multiple offices without duplicating user records.
