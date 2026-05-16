<x-layouts.public title="Project Overview — iBUConnect">
    @php
        $flows = [
            'submission' => [
                'label' => 'Submission',
                'eyebrow' => 'Requester Portal',
                'title' => 'A signed-in user submits a service request through the portal wizard.',
                'summary' => 'This is the main request creation path. It starts from the public site, moves through identity and onboarding, then creates a structured ticket record.',
                'writes' => ['users', 'tickets', 'ticket_histories', 'ticket_attachments'],
                'visibility' => 'Requester sees the ticket in the portal. Staff see it in the office-scoped admin queue.',
                'steps' => [
                    ['title' => 'Public site', 'text' => 'Browse offices, services, requirements, and legal pages.', 'tone' => 'orange'],
                    ['title' => 'Sign in', 'text' => 'Authenticate using Google OAuth or supported credentials.', 'tone' => 'sky'],
                    ['title' => 'Onboarding', 'text' => 'Complete required profile data before portal access.', 'tone' => 'violet'],
                    ['title' => 'Portal wizard', 'text' => 'Choose office, category, service, and dynamic form fields.', 'tone' => 'emerald'],
                    ['title' => 'Ticket created', 'text' => 'Generate ticket number, save request data, and start history.', 'tone' => 'slate'],
                    ['title' => 'Requester tracking', 'text' => 'Requester follows status and messages from the portal.', 'tone' => 'sky'],
                ],
            ],
            'tracking' => [
                'label' => 'Tracking',
                'eyebrow' => 'Public Ticket Tracker',
                'title' => 'A requester checks an existing ticket without signing in.',
                'summary' => 'This path is for follow-ups. It verifies ticket number plus requester last name, then opens a session-based detail view with only public ticket content.',
                'writes' => ['ticket_messages', 'ticket_attachments', 'session'],
                'visibility' => 'Internal notes stay hidden. Public replies and requested uploads are visible to staff.',
                'steps' => [
                    ['title' => 'Open /track', 'text' => 'Requester opens the public Track Ticket page.', 'tone' => 'orange'],
                    ['title' => 'Verify details', 'text' => 'Enter ticket number and last name; attempts are rate-limited.', 'tone' => 'sky'],
                    ['title' => 'Session unlock', 'text' => 'Correct lookup stores the verified ticket ID in session.', 'tone' => 'violet'],
                    ['title' => 'Read thread', 'text' => 'Show ticket status and non-internal conversation messages.', 'tone' => 'emerald'],
                    ['title' => 'Reply or upload', 'text' => 'Guest replies are saved; uploads appear only when staff request them.', 'tone' => 'slate'],
                    ['title' => 'Clear search', 'text' => 'Requester can end the session and search another ticket.', 'tone' => 'sky'],
                ],
            ],
            'staff' => [
                'label' => 'Staff Work',
                'eyebrow' => 'Admin Panel',
                'title' => 'Office staff process tickets from queue to resolution.',
                'summary' => 'This path is role-scoped. Staff can only act on tickets they are assigned to or tickets belonging to their office, unless they are super admins.',
                'writes' => ['tickets', 'ticket_messages', 'ticket_histories', 'canned_responses'],
                'visibility' => 'Public replies go to requesters. Internal notes remain staff-only.',
                'steps' => [
                    ['title' => 'Admin queue', 'text' => 'Staff open the Filament ticket resource and review office tickets.', 'tone' => 'orange'],
                    ['title' => 'Triage', 'text' => 'Inspect request details, attachments, and timeline history.', 'tone' => 'sky'],
                    ['title' => 'Assign', 'text' => 'Assign to staff or keep it in the office workflow.', 'tone' => 'violet'],
                    ['title' => 'Respond', 'text' => 'Send public replies, use canned responses, or add internal notes.', 'tone' => 'emerald'],
                    ['title' => 'Request file', 'text' => 'Flag a reply as needing an attachment from the requester.', 'tone' => 'slate'],
                    ['title' => 'Resolve', 'text' => 'Update status, resolve, or close with audit history preserved.', 'tone' => 'sky'],
                ],
            ],
            'forwarding' => [
                'label' => 'Forwarding',
                'eyebrow' => 'Inter-office Coordination',
                'title' => 'A ticket moves to another office with accountability preserved.',
                'summary' => 'Forwarding is not just reassignment. It records who sent it, who receives it, why it moved, and how credit is attributed.',
                'writes' => ['forwarding_logs', 'ticket_histories', 'tickets'],
                'visibility' => 'Staff see the handoff trail. Reports can separate accepted-credit work from reference-only assistance.',
                'steps' => [
                    ['title' => 'Need identified', 'text' => 'Staff determine another office must handle or advise.', 'tone' => 'orange'],
                    ['title' => 'Choose office', 'text' => 'Select the receiving office and add forwarding notes.', 'tone' => 'sky'],
                    ['title' => 'Credit type', 'text' => 'Mark the handoff as accept-credit or reference-only.', 'tone' => 'violet'],
                    ['title' => 'Log transfer', 'text' => 'Write forwarding log and ticket history entries.', 'tone' => 'emerald'],
                    ['title' => 'Receiving queue', 'text' => 'The destination office continues processing the ticket.', 'tone' => 'slate'],
                    ['title' => 'Metrics updated', 'text' => 'Credit attribution supports performance reporting.', 'tone' => 'sky'],
                ],
            ],
            'messaging' => [
                'label' => 'Messaging',
                'eyebrow' => 'Conversation Layer',
                'title' => 'Every conversation stays attached to the ticket.',
                'summary' => 'Messages can come from authenticated portal users, admin staff, or verified public tracker guests. Attachment requests are scoped to staff messages.',
                'writes' => ['ticket_messages', 'ticket_attachments'],
                'visibility' => 'Requester-facing messages are public. Internal notes and staff coordination are hidden from requesters.',
                'steps' => [
                    ['title' => 'Message source', 'text' => 'Portal requester, public tracker guest, or staff writes a message.', 'tone' => 'orange'],
                    ['title' => 'Message saved', 'text' => 'Store sender, guest name, body, flags, and timestamps.', 'tone' => 'sky'],
                    ['title' => 'Staff badge', 'text' => 'Guest tracker replies show a Via public tracker badge.', 'tone' => 'violet'],
                    ['title' => 'Internal note check', 'text' => 'Internal notes are excluded from requester and tracker views.', 'tone' => 'emerald'],
                    ['title' => 'Attachment request', 'text' => 'Staff can expose an upload form for a specific message.', 'tone' => 'slate'],
                    ['title' => 'File attached', 'text' => 'Uploaded files link back to both ticket and message.', 'tone' => 'sky'],
                ],
            ],
            'reporting' => [
                'label' => 'Reporting',
                'eyebrow' => 'Accountability',
                'title' => 'Operational activity becomes auditable reporting data.',
                'summary' => 'The system records the work as it happens, then uses those records for dashboard counts, office accountability, and traceable decision-making.',
                'writes' => ['ticket_histories', 'forwarding_logs', 'tickets'],
                'visibility' => 'Staff and administrators get operational summaries while preserving the full ticket trail.',
                'steps' => [
                    ['title' => 'Event occurs', 'text' => 'Creation, assignment, message, forwarding, or status update.', 'tone' => 'orange'],
                    ['title' => 'History saved', 'text' => 'Write immutable ticket history and related metadata.', 'tone' => 'sky'],
                    ['title' => 'Forwarding credit', 'text' => 'Office credit is recorded when tickets are transferred.', 'tone' => 'violet'],
                    ['title' => 'Dashboard counts', 'text' => 'Widgets summarize pending, in-progress, and resolved work.', 'tone' => 'emerald'],
                    ['title' => 'Office review', 'text' => 'Admins review service load and handoff patterns.', 'tone' => 'slate'],
                    ['title' => 'Decision support', 'text' => 'Data supports staffing and process improvements.', 'tone' => 'sky'],
                ],
            ],
        ];

        $toneClasses = [
            'orange' => 'border-orange-300 bg-orange-50 text-orange-800',
            'sky' => 'border-sky-300 bg-sky-50 text-sky-800',
            'emerald' => 'border-emerald-300 bg-emerald-50 text-emerald-800',
            'violet' => 'border-violet-300 bg-violet-50 text-violet-800',
            'slate' => 'border-slate-300 bg-slate-50 text-slate-800',
        ];

        $features = [
            ['name' => 'Public Directory', 'detail' => 'Browse offices and service offerings before signing in.'],
            ['name' => 'Legal Pages', 'detail' => 'Terms, privacy, cookies, data protection, and transparency pages.'],
            ['name' => 'Authentication', 'detail' => 'Google OAuth, Fortify support, and role-based access.'],
            ['name' => 'Onboarding Gate', 'detail' => 'Collects required profile data before portal entry.'],
            ['name' => 'Dynamic Forms', 'detail' => 'Service-specific custom fields and document requirements.'],
            ['name' => 'Ticket Timeline', 'detail' => 'Immutable history for creation, assignment, forwarding, and status changes.'],
            ['name' => 'Admin Panel', 'detail' => 'Filament resources for tickets, offices, services, users, and canned responses.'],
            ['name' => 'Forwarding Logs', 'detail' => 'Inter-office routing with credit attribution.'],
            ['name' => 'Messaging', 'detail' => 'Public replies, staff-only notes, guest replies, and seen indicators.'],
            ['name' => 'Attachment Requests', 'detail' => 'Staff can request documents and public users can upload them from the tracker.'],
            ['name' => 'Public Tracker', 'detail' => 'Lookup by ticket number and last name with rate-limited verification.'],
            ['name' => 'Dashboard Metrics', 'detail' => 'Operational counts for pending, in-progress, and resolved work.'],
        ];
    @endphp

    <style>
        .flow-stage {
            position: relative;
        }

        @media (min-width: 1024px) {
            .flow-stage:not(:last-child)::after {
                content: "";
                position: absolute;
                top: 52px;
                right: -25px;
                width: 48px;
                height: 2px;
                background: #fb923c;
            }

            .flow-stage:not(:last-child)::before {
                content: "";
                position: absolute;
                top: 47px;
                right: -29px;
                border-bottom: 6px solid transparent;
                border-left: 8px solid #fb923c;
                border-top: 6px solid transparent;
                z-index: 10;
            }
        }
    </style>

    <section class="border-b border-gray-200 bg-[#0f172a] text-white">
        <div class="mx-auto grid min-h-[520px] max-w-6xl items-center gap-10 px-4 py-16 lg:grid-cols-[0.85fr_1.15fr] lg:px-6">
            <div>
                <p class="mb-4 text-xs font-bold uppercase tracking-[0.28em] text-orange-300">Project Overview</p>
                <h1 class="max-w-xl text-4xl font-black tracking-normal sm:text-5xl">Clear workflows for every major iBUConnect function.</h1>
                <p class="mt-5 max-w-2xl text-base leading-8 text-slate-300">
                    The system has several paths, not one tangled route. Use the tabs to inspect submission, public tracking, staff processing, forwarding, messaging, and reporting as separate flows.
                </p>
                <div class="mt-8 grid grid-cols-3 gap-3 text-center">
                    <div class="border border-white/10 bg-white/[0.04] p-4">
                        <p class="text-2xl font-black text-orange-300">6</p>
                        <p class="mt-1 text-xs font-semibold text-slate-300">Workflow tabs</p>
                    </div>
                    <div class="border border-white/10 bg-white/[0.04] p-4">
                        <p class="text-2xl font-black text-orange-300">36</p>
                        <p class="mt-1 text-xs font-semibold text-slate-300">Mapped steps</p>
                    </div>
                    <div class="border border-white/10 bg-white/[0.04] p-4">
                        <p class="text-2xl font-black text-orange-300">1</p>
                        <p class="mt-1 text-xs font-semibold text-slate-300">Ticket record</p>
                    </div>
                </div>
            </div>

            <div class="relative overflow-hidden border border-white/10 bg-white/[0.03] p-6 shadow-2xl">
                <div class="absolute inset-0 opacity-20" style="background-image: linear-gradient(rgba(255,255,255,.16) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.16) 1px, transparent 1px); background-size: 34px 34px;"></div>
                <div class="relative z-10 grid gap-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="border border-orange-300 bg-orange-50 p-4 text-orange-900">
                            <p class="text-xs font-black uppercase tracking-widest">Public</p>
                            <p class="mt-2 text-sm font-semibold">Directory and tracker</p>
                        </div>
                        <div class="border border-sky-300 bg-sky-50 p-4 text-sky-900">
                            <p class="text-xs font-black uppercase tracking-widest">Portal</p>
                            <p class="mt-2 text-sm font-semibold">Submission and requester updates</p>
                        </div>
                        <div class="border border-violet-300 bg-violet-50 p-4 text-violet-900">
                            <p class="text-xs font-black uppercase tracking-widest">Admin</p>
                            <p class="mt-2 text-sm font-semibold">Triage, messages, forwarding</p>
                        </div>
                        <div class="border border-emerald-300 bg-emerald-50 p-4 text-emerald-900">
                            <p class="text-xs font-black uppercase tracking-widest">Data</p>
                            <p class="mt-2 text-sm font-semibold">Tickets, history, files, reports</p>
                        </div>
                    </div>
                    <div class="border border-white/10 bg-[#111827]/80 p-4">
                        <p class="text-sm font-bold text-white">Main operating idea</p>
                        <p class="mt-2 text-sm leading-7 text-slate-300">Every feature either creates, updates, verifies, routes, discusses, attaches to, or reports on a ticket. The tabs below separate those responsibilities into readable process maps.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-white py-14" x-data="{ active: 'submission' }">
        <div class="mx-auto max-w-6xl px-4 lg:px-6">
            <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.24em] text-orange-600">Workflow Tabs</p>
                    <h2 class="mt-2 text-2xl font-black text-gray-950">Major system functions</h2>
                </div>
                <p class="max-w-xl text-sm leading-7 text-gray-600">Each tab is aligned left-to-right in the actual order the system executes or presents the process.</p>
            </div>

            <div class="mb-6 flex gap-2 overflow-x-auto border-b border-gray-200 pb-3">
                @foreach ($flows as $key => $flow)
                    <button
                        type="button"
                        x-on:click="active = '{{ $key }}'"
                        x-bind:class="active === '{{ $key }}' ? 'border-orange-400 bg-orange-50 text-orange-800' : 'border-gray-200 bg-white text-gray-600 hover:border-gray-300 hover:bg-gray-50'"
                        class="shrink-0 border px-4 py-2 text-sm font-bold transition-colors"
                    >
                        {{ $flow['label'] }}
                    </button>
                @endforeach
            </div>

            @foreach ($flows as $key => $flow)
                <article x-show="active === '{{ $key }}'" x-cloak class="border border-gray-200 bg-white shadow-sm">
                    <div class="grid gap-6 border-b border-gray-200 bg-gray-50 p-6 lg:grid-cols-[0.7fr_1.3fr]">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.22em] text-orange-600">{{ $flow['eyebrow'] }}</p>
                            <h3 class="mt-3 text-2xl font-black text-gray-950">{{ $flow['title'] }}</h3>
                            <p class="mt-3 text-sm leading-7 text-gray-600">{{ $flow['summary'] }}</p>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div class="border border-gray-200 bg-white p-4">
                                <p class="text-xs font-black uppercase tracking-widest text-gray-500">Data written</p>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @foreach ($flow['writes'] as $table)
                                        <span class="border border-gray-200 bg-gray-50 px-2.5 py-1 font-mono text-xs font-bold text-gray-700">{{ $table }}</span>
                                    @endforeach
                                </div>
                            </div>
                            <div class="border border-gray-200 bg-white p-4">
                                <p class="text-xs font-black uppercase tracking-widest text-gray-500">Visibility rule</p>
                                <p class="mt-3 text-sm leading-6 text-gray-600">{{ $flow['visibility'] }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-4 p-6 lg:grid-cols-6">
                        @foreach ($flow['steps'] as $index => $step)
                            <div class="flow-stage border p-4 {{ $toneClasses[$step['tone']] }}">
                                <div class="mb-4 flex h-8 w-8 items-center justify-center bg-white/80 text-sm font-black">{{ $index + 1 }}</div>
                                <h4 class="text-sm font-black">{{ $step['title'] }}</h4>
                                <p class="mt-2 text-sm leading-6 text-gray-700">{{ $step['text'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <section class="border-y border-gray-200 bg-gray-50 py-14">
        <div class="mx-auto max-w-6xl px-4 lg:px-6">
            <div class="mb-8">
                <p class="text-xs font-bold uppercase tracking-[0.24em] text-orange-600">Complete Coverage</p>
                <h2 class="mt-2 text-2xl font-black text-gray-950">All major features in one operating model</h2>
            </div>

            <div class="grid gap-px overflow-hidden border border-gray-200 bg-gray-200 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($features as $feature)
                    <div class="bg-white p-5">
                        <h3 class="text-sm font-black text-gray-950">{{ $feature['name'] }}</h3>
                        <p class="mt-2 text-sm leading-6 text-gray-600">{{ $feature['detail'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="border-b border-gray-200 bg-[#0f172a] py-14 text-white">
        <div class="mx-auto max-w-6xl px-4 lg:px-6">
            <div class="mb-8">
                <p class="text-xs font-bold uppercase tracking-[0.24em] text-orange-300">Database Schema</p>
                <h2 class="mt-2 text-2xl font-black">Entity Relationship Diagram</h2>
                <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-300">All 13 application tables and their foreign key relationships. Scroll horizontally if needed.</p>
            </div>

            <div class="overflow-x-auto rounded-lg border border-white/10 bg-white p-4">
                <pre class="mermaid">
erDiagram
    users {
        bigint id PK
        string name
        string email UK
        string google_id UK
        string onboarding_status
        bigint pending_office_id FK
        timestamp onboarding_completed_at
    }
    offices {
        bigint id PK
        string name
        string slug UK
        string citizen_charter
        boolean is_active
        int sort_order
    }
    office_user {
        bigint id PK
        bigint office_id FK
        bigint user_id FK
        boolean is_primary
    }
    service_categories {
        bigint id PK
        bigint office_id FK
        string name
        string slug UK
        boolean is_active
        int sort_order
    }
    service_types {
        bigint id PK
        bigint service_category_id FK
        string name
        string slug UK
        string work_instruction
        int sla_days
        boolean is_active
    }
    service_type_fields {
        bigint id PK
        bigint service_type_id FK
        string label
        string field_type
        json options
        boolean is_required
        int sort_order
    }
    tickets {
        bigint id PK
        string ulid UK
        bigint requester_id FK
        bigint office_id FK
        bigint service_type_id FK
        bigint assigned_to FK
        string status
        string priority
        string subject
        json custom_fields
        timestamp resolved_at
        timestamp deleted_at
    }
    ticket_history {
        bigint id PK
        bigint ticket_id FK
        bigint actor_id FK
        string event_type
        string from_status
        string to_status
        text note
        json meta
    }
    ticket_messages {
        bigint id PK
        bigint ticket_id FK
        bigint sender_id FK
        string guest_name
        text body
        boolean is_internal_note
        boolean requests_attachment
        timestamp seen_at
    }
    ticket_attachments {
        bigint id PK
        bigint ticket_id FK
        bigint ticket_message_id FK
        bigint uploader_id FK
        string path
        string original_filename
        string mime_type
        bigint size_bytes
    }
    forwarding_logs {
        bigint id PK
        bigint ticket_id FK
        bigint from_office_id FK
        bigint to_office_id FK
        bigint forwarded_by FK
        bigint accepted_by FK
        string credit_type
        text note
        timestamp forwarded_at
    }
    ticket_ratings {
        bigint id PK
        bigint ticket_id FK
        bigint rater_id FK
        tinyint overall_rating
        tinyint service_rating
        tinyint staff_rating
        text comment
    }
    canned_responses {
        bigint id PK
        bigint office_id FK
        bigint created_by FK
        string title
        text body
        boolean is_active
    }

    users ||--o{ tickets : "requester"
    users ||--o{ tickets : "assignee"
    users ||--o{ office_user : "member"
    users ||--o{ ticket_history : "actor"
    users ||--o{ ticket_messages : "sender"
    users ||--o{ ticket_attachments : "uploader"
    users ||--o{ forwarding_logs : "forwarded_by"
    users ||--o{ forwarding_logs : "accepted_by"
    users ||--o{ ticket_ratings : "rater"
    users ||--o{ canned_responses : "created_by"
    users }o--o| offices : "pending_office"
    offices ||--o{ office_user : "members"
    offices ||--o{ service_categories : "owns"
    offices ||--o{ tickets : "handles"
    offices ||--o{ forwarding_logs : "from"
    offices ||--o{ forwarding_logs : "to"
    offices ||--o{ canned_responses : "scoped_to"
    service_categories ||--o{ service_types : "contains"
    service_types ||--o{ service_type_fields : "fields"
    service_types ||--o{ tickets : "requested_as"
    tickets ||--o{ ticket_history : "timeline"
    tickets ||--o{ ticket_messages : "thread"
    tickets ||--o{ ticket_attachments : "files"
    tickets ||--o{ forwarding_logs : "routed_via"
    tickets ||--o| ticket_ratings : "rated_as"
    ticket_messages ||--o{ ticket_attachments : "linked_file"
                </pre>
            </div>
        </div>
    </section>

    <section class="bg-white py-14">
        <div class="mx-auto max-w-6xl px-4 lg:px-6">
            <div class="grid gap-8 lg:grid-cols-[0.85fr_1.15fr]">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.24em] text-orange-600">Data Path</p>
                    <h2 class="mt-2 text-2xl font-black text-gray-950">What every request leaves behind</h2>
                    <p class="mt-4 text-sm leading-7 text-gray-600">iBUConnect keeps the operational record unified. Tickets, messages, attachments, forwarding logs, and history entries all point back to the same request, so the requester experience and office accountability stay aligned.</p>
                </div>

                <div class="grid gap-3">
                    <div class="grid gap-3 sm:grid-cols-[160px_1fr]">
                        <div class="bg-[#0f172a] px-4 py-3 text-sm font-black text-white">tickets</div>
                        <div class="border border-gray-200 px-4 py-3 text-sm text-gray-600">Main request record with requester, office, service type, status, priority, subject, and custom fields.</div>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-[160px_1fr]">
                        <div class="bg-orange-500 px-4 py-3 text-sm font-black text-white">ticket_histories</div>
                        <div class="border border-gray-200 px-4 py-3 text-sm text-gray-600">Immutable timeline for creation, assignment, forwarding, notes, status updates, and resolution.</div>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-[160px_1fr]">
                        <div class="bg-sky-500 px-4 py-3 text-sm font-black text-white">ticket_messages</div>
                        <div class="border border-gray-200 px-4 py-3 text-sm text-gray-600">Public replies, internal notes, canned responses, guest names, and attachment-request flags.</div>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-[160px_1fr]">
                        <div class="bg-emerald-500 px-4 py-3 text-sm font-black text-white">ticket_attachments</div>
                        <div class="border border-gray-200 px-4 py-3 text-sm text-gray-600">Uploaded files from authenticated users or public tracker guests, linked to tickets and optional messages.</div>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-[160px_1fr]">
                        <div class="bg-violet-500 px-4 py-3 text-sm font-black text-white">forwarding_logs</div>
                        <div class="border border-gray-200 px-4 py-3 text-sm text-gray-600">Inter-office transfers with reason, sender, receiver, and credit attribution.</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/mermaid@11/dist/mermaid.min.js"></script>
    <script>mermaid.initialize({ startOnLoad: true, theme: 'neutral', er: { layoutDirection: 'LR', fontSize: 12 } });</script>
@endpush
</x-layouts.public>
