# iBUConnect
### Project Documentation

**Prepared by:** Exequiel Lustan
**Degree Program:** Master of Information System
**Institution:** Bicol University
**Academic Year:** 2025–2026

---

## Table of Contents

1. [Executive Summary](#1-executive-summary)
2. [Problem Statement](#2-problem-statement)
3. [Project Objectives](#3-project-objectives)
4. [System Architecture](#4-system-architecture)
5. [Technology Stack](#5-technology-stack)
6. [Database Design](#6-database-design)
7. [System Modules](#7-system-modules)
8. [Key Features and Uniqueness](#8-key-features-and-uniqueness)
9. [Security and Compliance](#9-security-and-compliance)
10. [Benefits and Impact](#10-benefits-and-impact)
11. [Conclusion](#11-conclusion)

---

## 1. Executive Summary

**iBUConnect** is a full-stack web application developed to modernize and centralize the handling of service requests across all administrative offices of Bicol University. The system replaces fragmented, paper-based, and email-driven workflows with a unified digital platform that provides real-time tracking, structured communication, document exchange, and data-driven accountability.

iBUConnect is built as a multi-surface platform: a **Public Information and Ticket Tracker** for unauthenticated users, a **Client Portal** for students and university personnel to submit and monitor requests, and an **Admin Panel** for office staff to process, assign, forward, and resolve those requests. The system is designed to scale across multiple offices while maintaining strict role-based access control and auditability.

---

## 2. Problem Statement

University administrative services traditionally rely on manual, in-person, or email-based processes for handling student and personnel requests. This approach presents several operational challenges:

- **Lack of transparency** — requesters have no visibility into the status or progress of their submitted requests.
- **Fragmented communication** — correspondence is spread across email threads, physical forms, and verbal exchanges, making follow-ups difficult.
- **No accountability mechanism** — there is no formal audit trail to attribute responsibility, track response times, or measure office performance.
- **Inefficient inter-office coordination** — requests that require involvement from multiple offices have no structured handoff process, leading to duplicated effort or dropped requests.
- **Unstructured file management** — document submissions are inconsistent, uncompressed, and difficult to retrieve.

---

## 3. Project Objectives

1. Provide a centralized, role-aware digital platform for submitting, tracking, and resolving service requests.
2. Implement a structured inter-office forwarding mechanism with transparent credit attribution for performance tracking.
3. Deliver real-time bidirectional communication between requesters and office staff.
4. Enforce a complete audit trail via a ticket history timeline for every state change.
5. Automate file validation and optimize storage through a file management pipeline.
6. Integrate university identity through Google OAuth to eliminate redundant account creation.
7. Comply with Philippine data protection requirements by providing accessible legal documentation.

---

## 4. System Architecture

iBUConnect follows a **monolithic full-stack architecture** built on the TALL stack (Tailwind CSS, Alpine.js, Laravel, Livewire), with a dedicated administration layer powered by Filament.

```
┌───────────────────────────────────────────────────────────┐
│                      Web Browser                          │
├─────────────────────────┬─────────────────────────────────┤
│ Public / Tracker / Portal │       Admin Panel             │
│  (Livewire + FluxUI)    │    (Filament v5 Resources)      │
├─────────────────────────┴─────────────────────────────────┤
│                    Laravel 13 Core                        │
│   Routing · Middleware · Policies · Events · Jobs         │
├──────────────┬────────────────────────┬───────────────────┤
│  Fortify     │   Spatie Permission    │   Google Socialite│
│  (Auth)      │   (RBAC)               │   (OAuth)         │
├──────────────┴────────────────────────┴───────────────────┤
│              MySQL / MariaDB Database                     │
└───────────────────────────────────────────────────────────┘
```

**Request Flow:**
1. A user authenticates via Google OAuth or standard credentials.
2. First-time users are guided through a structured onboarding flow before portal access is granted.
3. Requests are submitted through a multi-step wizard and stored with a generated ticket identifier.
4. Office staff manage requests through the Filament admin panel, with all state changes recorded to a history log.
5. Requesters may also use the public `/track` page to verify a ticket by number and last name, view the public thread, reply as a guest, and upload requested documents.
6. Real-time updates are delivered via Livewire's reactive component model (`wire:poll` fallback for shared hosting).

---

## 5. Technology Stack

| Layer | Technology | Version |
|---|---|---|
| Language | PHP | 8.4 |
| Framework | Laravel | 13 |
| Admin Panel | Filament | 5 |
| Frontend Reactivity | Livewire | 4 |
| UI Components | Flux UI (Livewire) | 2 |
| CSS Framework | Tailwind CSS | 4 |
| Authentication | Laravel Fortify | 1 |
| OAuth | Laravel Socialite | 5 |
| Authorization | Spatie Laravel Permission | 7 |
| Testing | Pest PHP | 4 |
| Code Quality | Laravel Pint | 1 |
| Development Tools | Laravel Sail, Pail | 1 |

---

## 6. Database Design

The database is normalized around the ticket as the central entity. Key tables and their roles are described below.

### Core Tables

| Table | Purpose |
|---|---|
| `users` | University personnel and students; extended with Google OAuth and onboarding columns |
| `offices` | University administrative units, each with configurable service offerings |
| `service_categories` | Logical groupings of services per office (e.g., Academic Records, Financial Aid) |
| `service_types` | Individual requestable services with descriptions and document requirements |
| `service_type_fields` | Dynamic form field definitions per service type (text, select, file, checkbox, etc.) |
| `tickets` | Central request record; stores status, priority, subject, custom field data, and timestamps |
| `ticket_histories` | Immutable audit log of every ticket state transition, assignment, and forwarding event |
| `ticket_messages` | Conversation thread per ticket, supporting public replies, guest replies, internal staff notes, and staff attachment requests |
| `ticket_attachments` | File uploads linked to a ticket or message, tracking disk location, MIME type, size, and nullable uploader for public tracker uploads |
| `forwarding_logs` | Records inter-office transfers including credit attribution and forwarding notes |
| `canned_responses` | Reusable message templates maintained by administrators for common replies |

### Roles and Permissions

Authorization is managed through Spatie Laravel Permission using the following roles:

| Role | Scope |
|---|---|
| `super_admin` | Full system access across all offices |
| `office_admin` | Administrative access scoped to their assigned office |
| `staff` | Operational access to tickets within their office |
| `client` | Portal access; can submit and track their own requests |

---

## 7. System Modules

### 7.1 Public Information Module

Accessible without authentication, this module presents a directory of university offices and their available services. Users may browse by office, view service descriptions and required documents, navigate directly to the request submission form for a specific service, track an existing ticket through `/track`, or review the system-wide workflow through the Project Overview page.

**Components:** `OfficeList`, `OfficeDetail`, `TicketTracker` (Livewire), public layout with legal pages, and `pages.project-overview`.

### 7.1.1 Project Overview Page

The `/project-overview` page provides a Cloudflare-inspired visual systems map for academic presentation and stakeholder review. It separates the system into tabbed workflow diagrams for submission, public tracking, staff work, forwarding, messaging, and reporting so each major function can be understood without overlapping arrows or mixed responsibilities.

The page is intentionally separate from the operational portal so evaluators can understand the complete system without needing a staff or student account. It includes:

- Tabbed flowcharts for each major system function.
- Aligned left-to-right process steps that match the actual operational order.
- Data-written and visibility summaries for each workflow.
- A feature matrix covering the major modules.
- A data-path section showing how `tickets`, `ticket_histories`, `ticket_messages`, `ticket_attachments`, and `forwarding_logs` relate to each request.

### 7.1.2 Public Ticket Tracker

The public tracker allows requesters to access ticket status and conversation history without signing in. A requester enters the ticket number and their last name; successful verification stores the ticket ID in the session and shows only non-internal ticket details. From the tracker, requesters can:

- View the ticket subject, office, service type, status, and public message thread.
- Reply to staff through guest messages stored with `sender_id = null` and `guest_name` set to the requester name.
- Upload documents only when a staff message has `requests_attachment = true`.
- Clear the tracker session and search for another ticket.

The lookup flow uses Laravel's `RateLimiter` to reduce brute-force attempts and returns a generic error when either the ticket number or last name does not match.

### 7.2 Authentication Module

Supports dual authentication paths: Google OAuth (primary, using university accounts) and standard email/password with two-factor authentication (TOTP). First-time users entering through Google OAuth are routed through a mandatory onboarding flow before gaining portal access.

**Components:** `GoogleAuthController`, `GoogleLogin` (Filament), Fortify configuration, `EnsureOnboardingComplete` middleware.

### 7.3 Client Portal Module

The student and personnel-facing interface for submitting and monitoring service requests.

**Sub-modules:**

- **Onboarding** — Collects required profile information from new users before portal access.
- **Ticket Submission** — A four-step wizard (Office → Category → Service Type → Form Fields → Review) with dynamic field rendering per service type.
- **Ticket List** — Paginated list of the authenticated user's tickets with status indicators.
- **Ticket Detail** — Displays the full request, a vertical status timeline, and a public message thread with office staff.

**Components:** `CreateTicket`, `TicketList`, `TicketDetail`, `Onboarding`, `OnboardingNotice`.

### 7.4 Admin Panel Module

The staff-facing management interface built on Filament v5.

**Filament Resources:**

| Resource | Manages |
|---|---|
| `TicketResource` | Ticket list, detail view, messaging, status management |
| `OfficeResource` | Office records and staff assignments |
| `ServiceCategoryResource` | Service category configuration |
| `ServiceTypeResource` | Service type definitions and dynamic form fields |
| `UserResource` | User account management and role assignment |
| `CannedResponseResource` | Reusable response templates |

**Filament Actions:**

| Action | Function |
|---|---|
| `AssignTicketAction` | Assigns a ticket to a specific staff member with notifications |
| `ForwardTicketAction` | Transfers a ticket to another office with credit attribution selection |

**Widgets:** `TicketStatsOverview` — dashboard summary of pending, in-progress, and resolved ticket counts.

### 7.5 Messaging Module

A shared communication layer accessible from both the portal and the admin panel.

- **Public Thread** — Visible to both requester and staff.
- **Public Tracker Replies** — Guest replies from `/track` are attributed with the requester's name and shown to staff with a "Via public tracker" badge.
- **Internal Notes** — Staff-only annotations hidden from the requester.
- **Canned Responses** — Staff may select pre-written templates to populate the message field.
- **Attachment Requests** — Staff can flag a public reply as requesting an attachment, which exposes an upload form on the public tracker for that specific message.
- **Seen Indicators** — Messages are marked with a timestamp when the recipient opens the ticket.

### 7.6 Legal and Compliance Module

Static pages fulfilling institutional and regulatory transparency requirements under the Philippine Data Privacy Act.

| Page | Content |
|---|---|
| Privacy Policy | Data collection, processing, and retention practices |
| Terms of Service | Acceptable use and user obligations |
| Data Protection Policy | Rights of data subjects and system safeguards |
| Transparency Report | Institutional accountability statement |
| Cookies Policy | Cookie usage disclosure |

---

## 8. Key Features and Uniqueness

### 8.1 Dynamic, Service-Aware Request Forms

Unlike static contact forms or generic ticketing systems, iBUConnect generates a tailored form for each service type. Administrators define custom fields (text, number, dropdown, checkbox, file upload) per service without writing any code. This allows the system to capture precisely the information each office requires for a given request.

### 8.2 Structured Inter-Office Forwarding with Credit Attribution

A distinguishing feature of iBUConnect is its formal inter-office forwarding mechanism. When a ticket must be handled by a different office, staff may forward it through a structured action that requires selecting a **credit type**:

- **Accept Credit** — Both the originating and receiving offices receive performance metric credit for the resolution.
- **Reference Only** — Credit remains with the originating office; the receiving office acts in an advisory capacity.

Every forwarding action is recorded in a dedicated `forwarding_logs` table with full metadata, enabling objective institutional performance measurement that is not possible in informal routing workflows.

### 8.3 Immutable Ticket History Timeline

Every meaningful event in a ticket's lifecycle — creation, assignment, status change, forwarding, note addition, resolution, and closure — is recorded as an immutable row in the `ticket_history` table. This timeline is rendered as a visual, vertical progress tracker in the client portal, giving requesters full transparency into what happened to their request and when, without requiring staff intervention.

### 8.4 Auto-Generated Structured Ticket Identifiers

Each ticket receives a human-readable identifier generated from the office acronym, a type code, and a sequential number (e.g., `OADA-T-26-0001`). This makes verbal and written reference to specific requests unambiguous across offices and communication channels.

### 8.5 Public Ticket Tracking Without Account Friction

iBUConnect now includes a public `/track` page for requesters who need to follow up without logging in. The tracker uses ticket number plus requester last-name verification, stores the verified ticket in the PHP session, and reveals only public ticket details. This keeps follow-up lightweight while preserving privacy controls for internal staff notes.

### 8.6 Staff-Requested Guest Attachments

Office staff can request an attachment directly from a public reply. When that flag is set, the public tracker displays an upload control scoped to the requesting message. Uploaded files are linked back to the ticket and message with a nullable uploader, making it clear that the file came from the public tracker rather than an authenticated account.

### 8.7 Onboarding Gate with Middleware Enforcement

New users authenticated through Google are not immediately granted portal access. A dedicated onboarding flow collects institutional profile information before the `EnsureOnboardingComplete` middleware grants access to protected routes. This ensures data completeness for all active accounts.

### 8.8 Role-Scoped Staff Access

Staff accounts are restricted by office membership enforced at the policy and query level. A staff member cannot view, respond to, or act on tickets belonging to another office. This access control model mirrors real-world institutional boundaries and prevents information leakage between departments.

### 8.9 Internal Notes Invisible to Requesters

The messaging module supports an internal note toggle that renders staff communications invisible to the requester. This allows teams to coordinate, escalate, or annotate tickets without exposing deliberative content to end users — a common requirement in professional service desk operations.

---

## 9. Security and Compliance

| Concern | Implementation |
|---|---|
| Authentication | Google OAuth + Fortify (password, 2FA/TOTP) |
| Authorization | Spatie Permission RBAC; model Policies per resource |
| SQL Injection | Eloquent ORM with parameterized queries throughout |
| Mass Assignment | PHP 8.4 `#[Fillable]` attribute on all models |
| Soft Deletes | Tickets use `SoftDeletes`; historical data is preserved |
| Input Validation | Server-side validation on all Livewire and Filament actions |
| File Uploads | MIME type and size validation on all attachment inputs |
| Session Security | Laravel session management; CSRF protection on all forms |
| Public Tracker Protection | Ticket lookup requires ticket number plus requester last name, uses generic mismatch errors, and is rate-limited |
| Data Privacy | Compliant legal pages per the Philippine Data Privacy Act |
| Onboarding Gate | Middleware blocks portal access until profile is complete |

---

## 10. Benefits and Impact

### For Students and University Personnel
- Eliminates the need for in-person follow-ups or repeated email inquiries.
- Provides a single interface to track all submitted requests and their current status.
- Allows public ticket follow-up by ticket number and last name when a full portal sign-in is unnecessary.
- Delivers a chronological timeline of every action taken on a request.
- Enables direct, documented communication with the responsible office.
- Supports staff-requested document uploads from the public tracker.

### For Office Staff
- Replaces unstructured email inboxes with a prioritized, filterable ticket queue.
- Reduces repetitive typing through canned response templates.
- Enables clean handoffs to other offices through the forwarding mechanism.
- Provides internal annotation capability without exposing deliberations to requesters.
- Allows staff to request specific attachments from requesters and see public tracker replies clearly attributed.

### For University Administration
- Centralizes all service request data into a single queryable system.
- Enables office performance measurement through credit attribution on inter-office forwarding.
- Produces an auditable history of every action taken across all offices.
- Reduces paper consumption and manual filing overhead.
- Supports evidence-based decisions on staffing and service load distribution.

---

## 11. Conclusion

iBUConnect addresses a concrete institutional need: the absence of a structured, transparent, and accountable digital workflow for university service requests. By combining a public tracker, client-facing portal, and staff-facing administration panel under a single, integrated codebase, iBUConnect provides a coherent experience for all stakeholders while maintaining strict separation of concerns at the data and authorization layers.

The system's distinguishing contributions — structured inter-office forwarding with credit attribution, an immutable ticket history timeline, service-aware dynamic forms, public ticket tracking, staff-requested attachments, and role-scoped access control — address limitations not typically found in generic help desk solutions. These features reflect the specific operational realities of a multi-office academic institution and position iBUConnect as a purpose-built solution rather than a repurposed commercial tool.

The architecture is designed for deployability on Hostinger shared hosting or VPS environments, with Livewire's polling mechanism as a fallback for real-time updates, ensuring practical viability within the university's existing infrastructure constraints.

---

*Document prepared for academic project presentation purposes.*
*Bicol University — Master of Information System Program*
