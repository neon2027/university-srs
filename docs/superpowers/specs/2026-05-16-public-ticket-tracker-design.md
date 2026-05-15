# Public Ticket Tracker — Design Spec

**Date:** 2026-05-16
**Status:** Approved

---

## Overview

A public-facing ticket tracking page where anyone can look up the status of their service request without logging in. The visitor enters their ticket number and last name. On success, they can read the full conversation thread, reply with text, and upload attachment files when the admin has requested one.

---

## Route & Navigation

| Element | Value |
|---|---|
| Route path | `GET /track` |
| Route name | `track.ticket` |
| Livewire component | `App\Livewire\Public\TicketTracker` |
| Layout | `components.layouts.public` |

**Public layout header:** Add a "Track Ticket" link in the top nav bar, between the logo and the Sign In button.

**Footer:** Add "Track your ticket" link under the Support section alongside Technical Support, Help Center, FAQs.

---

## Database Schema Changes

### Migration 1 — `ticket_messages`

- Make `sender_id` **nullable** (currently NOT NULL). Guest replies have no user account.
- Add `guest_name` varchar(255) nullable — populated when `sender_id` is null; stores the requester's full name for admin attribution.
- Add `requests_attachment` boolean, default `false` — admin toggles this to signal the guest must upload a file.

### Migration 2 — `ticket_attachments`

- Make `uploader_id` **nullable** (currently NOT NULL). Guest uploads have no user account.

---

## Livewire Component — `TicketTracker`

### Session key

`tracker.ticket_id` — stores the integer primary key of the verified ticket. Cleared when the guest clicks "Search another ticket" or closes their browser (standard session lifetime).

### State 1: Lookup Form

Shown when `session('tracker.ticket_id')` is absent.

**Inputs:**
- Ticket Number — text input, e.g. `OSS-T-26-0001` (the `ulid` column)
- Last Name — text input

**Validation and lookup logic:**
1. Validate both fields as required strings.
2. Rate-limit: 10 attempts per minute per IP using `RateLimiter::attempt('ticket-tracker:' . request()->ip(), 10, ...)`. On failure, return a generic error.
3. Look up the ticket: `Ticket::where('ulid', $ticketNumber)->with('requester')->first()`
4. If not found, return a generic error ("Ticket not found or details do not match") — do not reveal which field was wrong.
5. Extract the requester's last name: last word of `$ticket->requester->name`, case-insensitive, trimmed.
6. Compare with the submitted last name. If they do not match, return the same generic error.
7. On match: store `session(['tracker.ticket_id' => $ticket->id])` and transition to State 2.

### State 2: Ticket Detail

Shown when `session('tracker.ticket_id')` is present. Always re-fetches the ticket from DB using the session's `ticket_id` (not the raw input).

**Displayed information:**
- Ticket number (`ulid`), subject, status badge (colored per `TicketStatus::color()`), office name, service type name, submitted date.
- Public message thread: `ticket->messages()->where('is_internal_note', false)->with('sender')->oldest()->get()`.
  - Messages where `sender_id` is not null → show sender's name/avatar.
  - Messages where `sender_id` is null → show `guest_name` with a "Via public tracker" indicator.
- For each admin message where `requests_attachment = true`, render an upload button directly below that message.

**Text reply:**
- Textarea at the bottom of the thread.
- On submit: create `TicketMessage` with `sender_id = null`, `guest_name = $ticket->requester->name`, `body = $input`, `is_internal_note = false`.
- Validation: required, max 5000 characters.

**File upload (per attachment-requested message):**
- Each upload is linked to the specific message that requested it.
- On submit: validate MIME type (pdf, jpg, jpeg, png, gif, webp, doc, docx, xlsx, csv), max 10 MB.
- Store file, then create `TicketAttachment` with `uploader_id = null`, `ticket_id`, `ticket_message_id` (the requesting message's id), disk, path, original_filename, mime_type, size_bytes.

**"Search another ticket" action:**
- Calls `session()->forget('tracker.ticket_id')` and returns to State 1.

---

## Admin Panel Changes (Filament)

### "Request attachment" toggle on message form

When an admin composes a reply on a ticket in the Filament panel, a toggle labeled "Request attachment from requester" is available. When enabled, `requests_attachment = true` is saved on the message.

### Guest message attribution

In the Filament ticket conversation view, messages with `sender_id = null` are attributed to `guest_name` and display a small badge ("Via public tracker") so the admin can distinguish them from portal-sent messages.

---

## Security

| Concern | Mitigation |
|---|---|
| Brute-force ticket lookup | Rate limit: 10 attempts/min/IP via Laravel `RateLimiter` |
| Last name guessing | Generic error message — does not reveal which field failed |
| Ticket enumeration | Session stores only one ticket ID; no list/search exposed |
| Unauthorized access across tickets | State 2 always re-fetches by session `ticket_id`, never by raw URL param |
| Malicious file uploads | Strict MIME allowlist + 10 MB max enforced by Livewire `WithFileUploads` |
| Internal note exposure | `is_internal_note = false` filter applied before rendering thread |

**Last name matching rules:**
- Extracted as the last whitespace-delimited word of `users.name`
- Comparison: `strtolower(trim($input)) === strtolower(trim($lastName))`

---

## File / Class Summary

| Path | Purpose |
|---|---|
| `app/Livewire/Public/TicketTracker.php` | Livewire component (lookup + detail) |
| `resources/views/livewire/public/ticket-tracker.blade.php` | Component view |
| `database/migrations/..._make_sender_id_nullable_add_guest_fields_to_ticket_messages_table.php` | Nullable sender, guest_name, requests_attachment |
| `database/migrations/..._make_uploader_id_nullable_on_ticket_attachments_table.php` | Nullable uploader |
| `resources/views/components/layouts/public.blade.php` | Add nav + footer links |
| `routes/web.php` | Add `/track` route |
| Admin Filament resource | Add requests_attachment toggle to reply form |

---

## Out of Scope

- Email notifications to the guest when the admin replies (no email address stored for unauthenticated guests)
- Pagination of the message thread (all messages loaded at once; typical ticket threads are short)
- Attachment download by guest (attachments are uploaded but guest cannot browse/download previously uploaded files in this iteration)
