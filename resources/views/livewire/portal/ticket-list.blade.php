<div class="portal-inbox">
    <style>
        .portal-inbox,
        .portal-inbox * {
            box-sizing: border-box;
        }

        .portal-inbox {
            overflow: hidden;
            min-height: 720px;
            border: 1px solid #dbe3ee;
            border-radius: 18px;
            background: #f8fafc;
            color: #102033;
            box-shadow: 0 18px 40px rgba(15, 23, 42, .16);
        }

        .pi-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 14px 18px;
            border-bottom: 1px solid #dbe3ee;
            background: #ffffff;
        }

        .pi-title {
            margin: 0;
            color: #0f172a;
            font-size: 18px;
            font-weight: 800;
        }

        .pi-subtitle {
            margin: 3px 0 0;
            color: #64748b;
            font-size: 13px;
        }

        .pi-new {
            display: inline-flex;
            align-items: center;
            border-radius: 8px;
            background: #f97316;
            color: #ffffff;
            padding: 9px 13px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 800;
        }

        .pi-grid {
            display: grid;
            grid-template-columns: 320px minmax(0, 1fr) 300px;
            min-height: 660px;
            gap: 1px;
            background: #dbe3ee;
        }

        .pi-column {
            background: #ffffff;
        }

        .pi-main {
            background: #f8fafc;
            padding: 18px;
        }

        .pi-side {
            background: #ffffff;
            padding: 18px;
        }

        .pi-list-head {
            padding: 14px 16px;
            border-bottom: 1px solid #e2e8f0;
        }

        .pi-search {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #f8fafc;
            padding: 9px 10px;
            color: #334155;
            font-size: 13px;
            outline: 0;
        }

        .pi-list {
            max-height: 606px;
            overflow: auto;
        }

        .pi-ticket {
            display: block;
            padding: 14px 16px;
            border-bottom: 1px solid #eef2f7;
            color: inherit;
            text-decoration: none;
        }

        .pi-ticket:hover {
            background: #f8fafc;
        }

        .pi-ticket-title {
            margin: 0;
            color: #0f172a;
            font-size: 14px;
            font-weight: 800;
            line-height: 1.35;
        }

        .pi-ticket-meta {
            margin: 5px 0 0;
            color: #64748b;
            font-size: 12px;
            line-height: 1.45;
        }

        .pi-status {
            display: inline-flex;
            margin-top: 9px;
            border-radius: 999px;
            background: #eff6ff;
            color: #1d4ed8;
            padding: 4px 8px;
            font-size: 11px;
            font-weight: 800;
        }

        .pi-empty {
            display: grid;
            min-height: 520px;
            place-items: center;
            padding: 32px;
            text-align: center;
        }

        .pi-empty h2 {
            margin: 0;
            color: #0f172a;
            font-size: 24px;
            font-weight: 850;
        }

        .pi-empty p {
            margin: 10px auto 0;
            max-width: 420px;
            color: #64748b;
            line-height: 1.6;
        }

        .pi-card {
            border: 1px solid #dbe3ee;
            border-radius: 12px;
            background: #ffffff;
            padding: 18px;
            box-shadow: 0 5px 18px rgba(15, 23, 42, .05);
        }

        .pi-card + .pi-card {
            margin-top: 14px;
        }

        .pi-card-title {
            margin: 0;
            color: #0f172a;
            font-size: 16px;
            font-weight: 850;
        }

        .pi-card-text {
            margin: 8px 0 0;
            color: #475569;
            font-size: 14px;
            line-height: 1.6;
        }

        .pi-pill-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 14px;
        }

        .pi-pill {
            border-radius: 999px;
            background: #f1f5f9;
            color: #334155;
            padding: 6px 10px;
            font-size: 12px;
            font-weight: 750;
        }

        .pi-kb-title {
            margin: 0 0 12px;
            color: #475569;
            font-size: 12px;
            font-weight: 850;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .pi-kb-item {
            display: flex;
            gap: 10px;
            padding: 12px 0;
            border-bottom: 1px solid #eef2f7;
        }

        .pi-kb-icon {
            display: grid;
            width: 34px;
            height: 34px;
            place-items: center;
            border-radius: 8px;
            background: #e0f2fe;
            color: #0369a1;
            font-size: 13px;
            font-weight: 900;
            flex: 0 0 auto;
        }

        .pi-kb-icon svg {
            width: 18px;
            height: 18px;
            stroke-width: 2;
        }

        .pi-kb-item strong {
            display: block;
            color: #0f172a;
            font-size: 13px;
        }

        .pi-kb-item span {
            display: block;
            margin-top: 3px;
            color: #64748b;
            font-size: 12px;
            line-height: 1.45;
        }

        .dark .portal-inbox {
            border-color: #27272a;
            background: #18181b;
            color: #e4e4e7;
            box-shadow: 0 18px 40px rgba(0, 0, 0, .35);
        }

        .dark .pi-topbar,
        .dark .pi-column,
        .dark .pi-side,
        .dark .pi-card {
            border-color: #27272a;
            background: #09090b;
        }

        .dark .pi-grid {
            background: #27272a;
        }

        .dark .pi-main,
        .dark .pi-search {
            background: #0f0f12;
        }

        .dark .pi-title,
        .dark .pi-ticket-title,
        .dark .pi-empty h2,
        .dark .pi-card-title,
        .dark .pi-kb-item strong {
            color: #f4f4f5;
        }

        .dark .pi-subtitle,
        .dark .pi-ticket-meta,
        .dark .pi-empty p,
        .dark .pi-card-text,
        .dark .pi-kb-title,
        .dark .pi-kb-item span {
            color: #a1a1aa;
        }

        .dark .pi-list-head,
        .dark .pi-ticket,
        .dark .pi-kb-item {
            border-color: #27272a;
        }

        .dark .pi-ticket:hover,
        .dark .pi-pill {
            background: #18181b;
        }

        .dark .pi-search {
            border-color: #3f3f46;
            color: #e4e4e7;
        }

        .dark .pi-status {
            background: rgba(37, 99, 235, .18);
            color: #93c5fd;
        }

        .dark .pi-kb-icon {
            background: rgba(14, 165, 233, .16);
            color: #7dd3fc;
        }

        @media (max-width: 1100px) {
            .pi-grid {
                grid-template-columns: 300px minmax(0, 1fr);
            }

            .pi-side {
                grid-column: 1 / -1;
            }
        }

        @media (max-width: 760px) {
            .pi-topbar {
                align-items: flex-start;
                flex-direction: column;
            }

            .pi-grid {
                grid-template-columns: 1fr;
            }

            .pi-list {
                max-height: none;
            }
        }
    </style>

    <header class="pi-topbar">
        <div>
            <h1 class="pi-title">My Requests</h1>
            <p class="pi-subtitle">Track requests, reply to staff, and review updates in one workspace.</p>
        </div>
        <a href="{{ route('portal.tickets.create') }}" wire:navigate class="pi-new">New Request</a>
    </header>

    <div class="pi-grid">
        <aside class="pi-column">
            <div class="pi-list-head">
                <input class="pi-search" value="Recent requests" readonly>
            </div>

            <div class="pi-list">
                @forelse ($tickets as $ticket)
                    <a href="{{ route('portal.tickets.show', $ticket->ulid) }}" wire:navigate class="pi-ticket">
                        <p class="pi-ticket-title">{{ $ticket->subject }}</p>
                        <p class="pi-ticket-meta">
                            <span class="font-mono">{{ $ticket->ulid }}</span><br>
                            {{ $ticket->office->name }} · {{ $ticket->updated_at->diffForHumans() }}
                        </p>
                        <span class="pi-status">{{ $ticket->status->label() }}</span>
                    </a>
                @empty
                    <div class="pi-empty">
                        <div>
                            <h2>No requests yet</h2>
                            <p>Submit your first request and it will appear here with status updates and message history.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </aside>

        <main class="pi-main">
            <section class="pi-card">
                <h2 class="pi-card-title">Request Center</h2>
                <p class="pi-card-text">
                    Select a request from the left to open its conversation, timeline, and service details. Staff replies will appear in the ticket thread.
                </p>
                <div class="pi-pill-row">
                    <span class="pi-pill">{{ $tickets->count() }} total</span>
                    <span class="pi-pill">{{ $tickets->where('status', \App\Enums\TicketStatus::Resolved)->count() }} resolved</span>
                    <span class="pi-pill">{{ $tickets->where('status', \App\Enums\TicketStatus::Pending)->count() }} pending</span>
                </div>
            </section>

            <section class="pi-card">
                <h2 class="pi-card-title">How it works</h2>
                <p class="pi-card-text">Create a request, answer the service questions, then use the ticket thread to coordinate with the assigned office.</p>
            </section>
        </main>

        <aside class="pi-side">
            <h3 class="pi-kb-title">Knowledge base</h3>
            <div class="pi-kb-item">
                <div class="pi-kb-icon"><x-heroicon-o-credit-card /></div>
                <div>
                    <strong>Payment posting</strong>
                    <span>Prepare your reference number, payment date, and proof of payment.</span>
                </div>
            </div>
            <div class="pi-kb-item">
                <div class="pi-kb-icon"><x-heroicon-o-academic-cap /></div>
                <div>
                    <strong>Academic records</strong>
                    <span>Include the term, course code, and supporting files when needed.</span>
                </div>
            </div>
            <div class="pi-kb-item">
                <div class="pi-kb-icon"><x-heroicon-o-computer-desktop /></div>
                <div>
                    <strong>IT support</strong>
                    <span>Describe the system, device, location, and exact issue you are seeing.</span>
                </div>
            </div>
        </aside>
    </div>
</div>
