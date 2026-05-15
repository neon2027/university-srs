<div class="portal-thread" wire:poll.5s>
    @php
        $initials = \Illuminate\Support\Str::of(auth()->user()->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => \Illuminate\Support\Str::substr($word, 0, 1))
            ->implode('');
    @endphp

    <style>
        .portal-thread,
        .portal-thread * {
            box-sizing: border-box;
        }

        .portal-thread {
            overflow: hidden;
            min-height: 760px;
            border: 1px solid #dbe3ee;
            border-radius: 18px;
            background: #f8fafc;
            color: #102033;
            box-shadow: 0 18px 40px rgba(15, 23, 42, .16);
        }

        .pt-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 12px 16px;
            border-bottom: 1px solid #dbe3ee;
            background: #ffffff;
        }

        .pt-breadcrumb {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
            color: #64748b;
            font-size: 13px;
        }

        .pt-id {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            color: #1d4ed8;
            font-weight: 800;
        }

        .pt-action {
            display: inline-flex;
            align-items: center;
            border-radius: 8px;
            background: #f97316;
            color: #ffffff;
            padding: 9px 12px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 800;
        }

        .pt-grid {
            display: grid;
            grid-template-columns: 310px minmax(0, 1fr) 310px;
            min-height: 706px;
            gap: 1px;
            background: #dbe3ee;
        }

        .pt-rail,
        .pt-center,
        .pt-side {
            background: #ffffff;
        }

        .pt-center {
            background: #f8fafc;
            padding: 18px;
        }

        .pt-side {
            padding: 18px;
        }

        .pt-rail-head {
            padding: 14px 16px;
            border-bottom: 1px solid #eef2f7;
        }

        .pt-rail-title {
            margin: 0;
            color: #0f172a;
            font-size: 14px;
            font-weight: 850;
        }

        .pt-ticket-link {
            display: block;
            padding: 14px 16px;
            border-bottom: 1px solid #eef2f7;
            color: inherit;
            text-decoration: none;
        }

        .pt-ticket-link:hover,
        .pt-ticket-link-active {
            background: #f1f7ff;
        }

        .pt-ticket-title {
            margin: 0;
            color: #0f172a;
            font-size: 13px;
            font-weight: 800;
            line-height: 1.35;
        }

        .pt-ticket-meta {
            margin: 5px 0 0;
            color: #64748b;
            font-size: 12px;
            line-height: 1.4;
        }

        .pt-status-dot {
            display: inline-block;
            width: 7px;
            height: 7px;
            margin-right: 5px;
            border-radius: 999px;
            background: #2563eb;
        }

        .pt-card {
            overflow: hidden;
            border: 1px solid #dbe3ee;
            border-radius: 12px;
            background: #ffffff;
            box-shadow: 0 5px 18px rgba(15, 23, 42, .05);
        }

        .pt-card-head {
            padding: 18px 20px;
            border-bottom: 1px solid #dbe3ee;
        }

        .pt-title-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
        }

        .pt-title {
            margin: 0;
            color: #0f172a;
            font-size: 22px;
            font-weight: 850;
            line-height: 1.25;
        }

        .pt-subtitle {
            margin: 7px 0 0;
            color: #64748b;
            font-size: 13px;
        }

        .pt-badge-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 14px;
        }

        .pt-badge {
            border-radius: 999px;
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
            padding: 5px 10px;
            font-size: 12px;
            font-weight: 850;
        }

        .pt-badge-priority {
            background: #fff7ed;
            color: #c2410c;
            border-color: #fed7aa;
        }

        .pt-request {
            padding: 20px;
            border-bottom: 1px solid #dbe3ee;
            background: #e8f3ff;
        }

        .pt-author {
            display: flex;
            gap: 12px;
            align-items: center;
            margin-bottom: 14px;
        }

        .pt-avatar {
            display: grid;
            width: 42px;
            height: 42px;
            place-items: center;
            border-radius: 10px;
            background: #ddd6fe;
            color: #5b21b6;
            font-size: 13px;
            font-weight: 900;
            flex: 0 0 auto;
        }

        .pt-author-name {
            margin: 0;
            color: #0f172a;
            font-size: 14px;
            font-weight: 850;
        }

        .pt-description {
            white-space: pre-wrap;
            color: #1e293b;
            font-size: 14px;
            line-height: 1.65;
        }

        .pt-messages {
            padding: 20px;
        }

        .pt-section-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 14px;
        }

        .pt-section-title {
            margin: 0;
            color: #0f172a;
            font-size: 15px;
            font-weight: 850;
        }

        .pt-count {
            border-radius: 999px;
            background: #f1f5f9;
            color: #475569;
            padding: 5px 10px;
            font-size: 12px;
            font-weight: 800;
        }

        .pt-message-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-height: 28rem;
            overflow-y: auto;
            padding: 4px 8px 4px 0;
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 transparent;
        }

        .pt-message {
            display: flex;
            align-items: flex-end;
            gap: 8px;
            max-width: 78%;
            align-self: flex-start;
        }

        .pt-message-mine {
            align-self: flex-end;
            flex-direction: row-reverse;
        }

        .pt-bubble-avatar {
            display: grid;
            width: 30px;
            height: 30px;
            place-items: center;
            border-radius: 999px;
            background: #e2e8f0;
            color: #475569;
            font-size: 11px;
            font-weight: 900;
            flex: 0 0 auto;
        }

        .pt-message-mine .pt-bubble-avatar {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .pt-bubble-content {
            min-width: 0;
        }

        .pt-bubble {
            border-radius: 18px 18px 18px 6px;
            background: #f1f5f9;
            color: #0f172a;
            padding: 10px 13px;
            white-space: pre-wrap;
            overflow-wrap: anywhere;
            font-size: 14px;
            line-height: 1.5;
            box-shadow: 0 2px 8px rgba(15, 23, 42, .04);
        }

        .pt-message-mine .pt-bubble {
            border-radius: 18px 18px 6px 18px;
            background: #2563eb;
            color: #ffffff;
        }

        .pt-bubble-meta {
            margin-top: 4px;
            color: #64748b;
            font-size: 11px;
        }

        .pt-message-mine .pt-bubble-meta {
            text-align: right;
        }

        .pt-compose {
            margin-top: 16px;
            border: 1px solid #dbe3ee;
            border-radius: 10px;
            background: #ffffff;
            padding: 14px;
        }

        .pt-compose textarea {
            display: block;
            width: 100%;
            min-height: 104px;
            resize: vertical;
            border: 1px solid #cbd5e1;
            border-radius: 9px;
            background: #ffffff;
            color: #0f172a;
            padding: 10px 12px;
            font-size: 14px;
            line-height: 1.6;
            outline: 0;
        }

        .pt-compose textarea:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, .12);
        }

        .pt-compose-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-top: 12px;
        }

        .pt-helper {
            color: #64748b;
            font-size: 12px;
        }

        .pt-send {
            border: 0;
            border-radius: 9px;
            background: #111827;
            color: #ffffff;
            padding: 10px 14px;
            font-size: 13px;
            font-weight: 850;
            cursor: pointer;
        }

        .pt-error {
            margin-top: 8px;
            color: #b91c1c;
            font-size: 13px;
            font-weight: 700;
        }

        .pt-panel {
            padding: 18px 0;
            border-bottom: 1px solid #dbe3ee;
        }

        .pt-panel:first-child {
            padding-top: 0;
        }

        .pt-panel:last-child {
            border-bottom: 0;
        }

        .pt-panel-title {
            margin: 0 0 14px;
            color: #475569;
            font-size: 12px;
            font-weight: 900;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .pt-detail-list {
            display: grid;
            gap: 14px;
        }

        .pt-detail-list dt {
            color: #64748b;
            font-size: 12px;
            font-weight: 800;
        }

        .pt-detail-list dd {
            margin: 4px 0 0;
            color: #0f172a;
            font-size: 14px;
            font-weight: 850;
            overflow-wrap: anywhere;
        }

        .pt-kb-item,
        .pt-timeline-item {
            display: flex;
            gap: 10px;
            padding: 11px 0;
            border-bottom: 1px solid #eef2f7;
        }

        .pt-kb-icon,
        .pt-timeline-dot {
            display: grid;
            width: 32px;
            height: 32px;
            place-items: center;
            border-radius: 8px;
            background: #e0f2fe;
            color: #0369a1;
            font-size: 12px;
            font-weight: 900;
            flex: 0 0 auto;
        }

        .pt-kb-icon svg {
            width: 17px;
            height: 17px;
            stroke-width: 2;
        }

        .pt-timeline-dot {
            width: 10px;
            height: 10px;
            margin-top: 5px;
            border-radius: 999px;
            background: #2563eb;
        }

        .pt-kb-item strong,
        .pt-timeline-item strong {
            display: block;
            color: #0f172a;
            font-size: 13px;
        }

        .pt-kb-item span,
        .pt-timeline-item span {
            display: block;
            margin-top: 3px;
            color: #64748b;
            font-size: 12px;
        }

        .dark .portal-thread {
            border-color: #27272a;
            background: #18181b;
            color: #e4e4e7;
            box-shadow: 0 18px 40px rgba(0, 0, 0, .35);
        }

        .dark .pt-topbar,
        .dark .pt-rail,
        .dark .pt-side,
        .dark .pt-card,
        .dark .pt-compose {
            border-color: #27272a;
            background: #09090b;
        }

        .dark .pt-grid {
            background: #27272a;
        }

        .dark .pt-center {
            background: #0f0f12;
        }

        .dark .pt-title,
        .dark .pt-rail-title,
        .dark .pt-ticket-title,
        .dark .pt-section-title,
        .dark .pt-author-name,
        .dark .pt-detail-list dd,
        .dark .pt-kb-item strong,
        .dark .pt-timeline-item strong {
            color: #f4f4f5;
        }

        .dark .pt-breadcrumb,
        .dark .pt-subtitle,
        .dark .pt-ticket-meta,
        .dark .pt-helper,
        .dark .pt-panel-title,
        .dark .pt-detail-list dt,
        .dark .pt-kb-item span,
        .dark .pt-timeline-item span {
            color: #a1a1aa;
        }

        .dark .pt-rail-head,
        .dark .pt-ticket-link,
        .dark .pt-card-head,
        .dark .pt-request,
        .dark .pt-panel,
        .dark .pt-kb-item,
        .dark .pt-timeline-item {
            border-color: #27272a;
        }

        .dark .pt-ticket-link:hover,
        .dark .pt-ticket-link-active,
        .dark .pt-count {
            background: #18181b;
        }

        .dark .pt-badge {
            border-color: #1d4ed8;
            background: rgba(37, 99, 235, .18);
            color: #93c5fd;
        }

        .dark .pt-badge-priority {
            border-color: #c2410c;
            background: rgba(234, 88, 12, .14);
            color: #fdba74;
        }

        .dark .pt-request {
            border-color: #1d4ed8;
            background: rgba(30, 64, 175, .22);
        }

        .dark .pt-message-list {
            scrollbar-color: #52525b transparent;
        }

        .dark .pt-description {
            color: #e4e4e7;
        }

        .dark .pt-bubble-avatar {
            background: #27272a;
            color: #d4d4d8;
        }

        .dark .pt-message-mine .pt-bubble-avatar {
            background: rgba(37, 99, 235, .22);
            color: #bfdbfe;
        }

        .dark .pt-bubble {
            background: #27272a;
            color: #f4f4f5;
        }

        .dark .pt-message-mine .pt-bubble {
            background: #2563eb;
            color: #ffffff;
        }

        .dark .pt-bubble-meta {
            color: #a1a1aa;
        }

        .dark .pt-compose textarea {
            border-color: #3f3f46;
            background: #18181b;
            color: #f4f4f5;
        }

        .dark .pt-send {
            background: #2563eb;
        }

        .dark .pt-avatar {
            background: rgba(124, 58, 237, .22);
            color: #c4b5fd;
        }

        .dark .pt-kb-icon {
            background: rgba(14, 165, 233, .16);
            color: #7dd3fc;
        }

        @media (max-width: 1180px) {
            .pt-grid {
                grid-template-columns: 300px minmax(0, 1fr);
            }

            .pt-side {
                grid-column: 1 / -1;
            }
        }

        @media (max-width: 820px) {
            .pt-topbar,
            .pt-title-row,
            .pt-compose-actions {
                align-items: flex-start;
                flex-direction: column;
            }

            .pt-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <header class="pt-topbar">
        <div class="pt-breadcrumb">
            <a href="{{ route('portal.tickets.index') }}" wire:navigate>All requests</a>
            <span>/</span>
            <span class="pt-id">{{ $ticket->ulid }}</span>
        </div>

        <a href="{{ route('portal.tickets.create') }}" wire:navigate class="pt-action">New Request</a>
    </header>

    <div class="pt-grid">
        <aside class="pt-rail">
            <div class="pt-rail-head">
                <p class="pt-rail-title">Requests</p>
            </div>

            @foreach ($recentTickets as $recent)
                <a href="{{ route('portal.tickets.show', $recent->ulid) }}" wire:navigate
                    class="pt-ticket-link {{ $recent->id === $ticket->id ? 'pt-ticket-link-active' : '' }}">
                    <p class="pt-ticket-title">{{ $recent->subject }}</p>
                    <p class="pt-ticket-meta">
                        <span class="pt-status-dot"></span>{{ $recent->status->label() }}<br>
                        {{ $recent->ulid }} · {{ $recent->updated_at->diffForHumans() }}
                    </p>
                </a>
            @endforeach
        </aside>

        <main class="pt-center">
            <section class="pt-card">
                <div class="pt-card-head">
                    <div class="pt-title-row">
                        <div>
                            <h1 class="pt-title">{{ $ticket->subject }}</h1>
                            <p class="pt-subtitle">{{ $ticket->office->name }} · {{ $ticket->serviceType->name }}</p>
                        </div>
                        <span class="pt-id">{{ $ticket->ulid }}</span>
                    </div>

                    <div class="pt-badge-row">
                        <span class="pt-badge">{{ $ticket->status->label() }}</span>
                        <span class="pt-badge pt-badge-priority">{{ $ticket->priority->label() }} priority</span>
                    </div>
                </div>

                <div class="pt-request">
                    <div class="pt-author">
                        <div class="pt-avatar">{{ $initials }}</div>
                        <div>
                            <p class="pt-author-name">{{ auth()->user()->name }} submitted this request</p>
                            <p class="pt-subtitle">{{ $ticket->created_at->format('M j, Y g:ia') }}</p>
                        </div>
                    </div>
                    <div class="pt-description">{{ $ticket->description }}</div>
                </div>

                <div class="pt-messages">
                    <div class="pt-section-head">
                        <h2 class="pt-section-title">Conversation</h2>
                        <span class="pt-count">{{ $messages->count() }} {{ \Illuminate\Support\Str::plural('message', $messages->count()) }}</span>
                    </div>

                    <div class="pt-message-list">
                        @forelse ($messages as $message)
                            @php
                                $senderInitials = \Illuminate\Support\Str::of($message->sender->name)
                                    ->explode(' ')
                                    ->take(2)
                                    ->map(fn ($word) => \Illuminate\Support\Str::substr($word, 0, 1))
                                    ->implode('');
                                $isMine = $message->sender_id === auth()->id();
                            @endphp

                            <article class="pt-message {{ $isMine ? 'pt-message-mine' : '' }}">
                                <div class="pt-bubble-avatar">{{ $senderInitials }}</div>
                                <div class="pt-bubble-content">
                                    <div class="pt-bubble">{{ $message->body }}</div>
                                    <div class="pt-bubble-meta">
                                        {{ $isMine ? 'You' : $message->sender->name }} · {{ $message->created_at->format('M j, g:ia') }}
                                        @if ($isMine && $message->seen_at)
                                            · Seen
                                        @endif
                                    </div>
                                </div>
                            </article>
                        @empty
                            <p class="pt-helper">No messages yet. Send a reply below to start the conversation.</p>
                        @endforelse
                    </div>

                    <form wire:submit.prevent="sendMessage" class="pt-compose">
                        <textarea wire:model="messageBody" wire:keydown.ctrl.enter="sendMessage" placeholder="Type a reply..."></textarea>
                        @error('messageBody')
                            <p class="pt-error">{{ $message }}</p>
                        @enderror
                        <div class="pt-compose-actions">
                            <span class="pt-helper">Press Ctrl+Enter to send</span>
                            <button type="submit" class="pt-send">Send Reply</button>
                        </div>
                    </form>
                </div>
            </section>
        </main>

        <aside class="pt-side">
            <section class="pt-panel">
                <h3 class="pt-panel-title">Request Details</h3>
                <dl class="pt-detail-list">
                    <div><dt>Ticket ID</dt><dd>{{ $ticket->ulid }}</dd></div>
                    <div><dt>Status</dt><dd>{{ $ticket->status->label() }}</dd></div>
                    <div><dt>Office</dt><dd>{{ $ticket->office->name }}</dd></div>
                    <div><dt>Service</dt><dd>{{ $ticket->serviceType->name }}</dd></div>
                    <div><dt>Submitted</dt><dd>{{ $ticket->created_at->format('M j, Y g:ia') }}</dd></div>
                </dl>
            </section>

            <section class="pt-panel">
                <h3 class="pt-panel-title">Status Timeline</h3>
                @forelse ($ticket->history as $event)
                    <div class="pt-timeline-item">
                        <span class="pt-timeline-dot"></span>
                        <div>
                            <strong>{{ \Illuminate\Support\Str::headline($event->event_type->value) }}</strong>
                            <span>{{ $event->actor?->name ?? 'System' }} · {{ $event->created_at->format('M j, g:ia') }}</span>
                        </div>
                    </div>
                @empty
                    <p class="pt-helper">No events yet.</p>
                @endforelse
            </section>

            <section class="pt-panel">
                <h3 class="pt-panel-title">Knowledge base</h3>
                <div class="pt-kb-item">
                    <div class="pt-kb-icon"><x-heroicon-o-document-text /></div>
                    <div>
                        <strong>Keep details complete</strong>
                        <span>Add reference numbers, dates, and context in your replies.</span>
                    </div>
                </div>
                <div class="pt-kb-item">
                    <div class="pt-kb-icon"><x-heroicon-o-chat-bubble-left-right /></div>
                    <div>
                        <strong>Watch for staff replies</strong>
                        <span>Updates from the office will appear in this conversation thread.</span>
                    </div>
                </div>
                <div class="pt-kb-item">
                    <div class="pt-kb-icon"><x-heroicon-o-ticket /></div>
                    <div>
                        <strong>Use one ticket per concern</strong>
                        <span>Create a separate request when the issue belongs to another service.</span>
                    </div>
                </div>
            </section>
        </aside>
    </div>
</div>
