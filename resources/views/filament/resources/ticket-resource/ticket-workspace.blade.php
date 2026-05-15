@php
    use Illuminate\Support\Str;

    $requesterName = $record->requester?->name ?? 'Unknown requester';
    $requesterEmail = $record->requester?->email ?? 'No email available';

    $initials = Str::of($requesterName)
        ->explode(' ')
        ->take(2)
        ->map(fn ($word) => Str::substr($word, 0, 1))
        ->implode('');

    $customFieldAnswers = collect($record->custom_fields ?? [])
        ->mapWithKeys(fn ($value, $fieldId) => [(string) $fieldId => $value]);
@endphp

<style>
    .busrs-ticket-workspace,
    .busrs-ticket-workspace * {
        box-sizing: border-box;
    }

    .busrs-ticket-workspace {
        overflow: hidden;
        border: 1px solid #d8dee8;
        border-radius: 14px;
        background: #eef2f7;
        color: #132033;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
    }

    .btw-topbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 14px 18px;
        border-bottom: 1px solid #d8dee8;
        background: #ffffff;
    }

    .btw-breadcrumb {
        display: flex;
        gap: 8px;
        align-items: center;
        color: #64748b;
        font-size: 13px;
    }

    .btw-ticket-id {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        color: #1d4ed8;
        font-weight: 700;
    }

    .btw-title {
        margin: 4px 0 0;
        color: #0f172a;
        font-size: 22px;
        font-weight: 700;
        line-height: 1.2;
    }

    .btw-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .btw-badge {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 5px 10px;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: 12px;
        font-weight: 700;
        border: 1px solid #bfdbfe;
    }

    .btw-badge-priority {
        background: #fff7ed;
        color: #c2410c;
        border-color: #fed7aa;
    }

    .btw-grid {
        display: grid;
        grid-template-columns: minmax(230px, .72fr) minmax(420px, 1.45fr) minmax(280px, .86fr);
        min-height: 720px;
        gap: 1px;
        background: #d8dee8;
    }

    .btw-main,
    .btw-conversation,
    .btw-details {
        min-width: 0;
        background: #f8fafc;
        padding: 18px;
    }

    .btw-details {
        background: #ffffff;
    }

    .btw-card {
        overflow: hidden;
        border: 1px solid #dbe3ee;
        border-radius: 10px;
        background: #ffffff;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
    }

    .btw-card-head {
        display: grid;
        gap: 16px;
        padding: 20px;
        border-bottom: 1px solid #dbe3ee;
    }

    .btw-subject-row {
        display: flex;
        gap: 14px;
        align-items: flex-start;
    }

    .btw-icon {
        display: grid;
        width: 42px;
        height: 42px;
        place-items: center;
        border-radius: 10px;
        background: #e0f2fe;
        color: #0369a1;
        font-size: 19px;
    }

    .btw-icon svg {
        width: 21px;
        height: 21px;
        stroke-width: 2;
    }

    .btw-subject {
        margin: 0;
        color: #0f172a;
        font-size: 18px;
        font-weight: 700;
        line-height: 1.25;
    }

    .btw-muted {
        margin: 5px 0 0;
        color: #64748b;
        font-size: 13px;
    }

    .btw-service-box {
        min-width: 0;
        border: 1px solid #dbe3ee;
        border-radius: 8px;
        background: #f8fafc;
        padding: 12px;
        text-align: left;
    }

    .btw-service-label,
    .btw-panel-title {
        color: #475569;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .btw-service-id {
        margin-top: 5px;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 13px;
        font-weight: 800;
    }

    .btw-request {
        padding: 20px;
        background: #e8f3ff;
    }

    .btw-author {
        display: flex;
        gap: 12px;
        align-items: center;
        margin-bottom: 14px;
    }

    .btw-avatar {
        display: grid;
        width: 42px;
        height: 42px;
        place-items: center;
        border-radius: 8px;
        background: #ddd6fe;
        color: #5b21b6;
        font-size: 13px;
        font-weight: 800;
    }

    .btw-author-name {
        margin: 0;
        color: #0f172a;
        font-size: 14px;
        font-weight: 700;
    }

    .btw-description {
        white-space: pre-wrap;
        color: #1e293b;
        font-size: 14px;
        line-height: 1.65;
    }

    .btw-messaging-wrap {
        height: 100%;
    }

    .btw-conversation-card {
        height: 100%;
        min-height: 640px;
        overflow: hidden;
        border: 1px solid #dbe3ee;
        border-radius: 12px;
        background: #ffffff;
        padding: 18px;
        box-shadow: 0 8px 22px rgba(15, 23, 42, 0.06);
    }

    .btw-panel {
        padding: 18px 0;
        border-bottom: 1px solid #dbe3ee;
    }

    .btw-panel:first-child {
        padding-top: 0;
    }

    .btw-panel:last-child {
        border-bottom: 0;
    }

    .btw-dl {
        display: grid;
        gap: 14px;
        margin: 14px 0 0;
    }

    .btw-dl dt {
        color: #64748b;
        font-size: 12px;
        font-weight: 700;
    }

    .btw-dl dd {
        margin: 4px 0 0;
        color: #0f172a;
        font-size: 14px;
        font-weight: 650;
        overflow-wrap: anywhere;
    }

    .btw-contact-card {
        display: flex;
        gap: 12px;
        margin-top: 14px;
    }

    .btw-linkish {
        color: #2563eb;
        font-weight: 800;
    }

    .btw-timeline {
        display: grid;
        gap: 12px;
        margin-top: 14px;
    }

    .btw-timeline-item {
        display: flex;
        gap: 10px;
    }

    .btw-dot {
        width: 8px;
        height: 8px;
        margin-top: 6px;
        border-radius: 999px;
        background: #2563eb;
        flex: 0 0 auto;
    }

    .btw-timeline-title {
        margin: 0;
        color: #0f172a;
        font-size: 14px;
        font-weight: 700;
    }

    .btw-recent-message {
        padding: 10px;
        border: 1px solid #dbe3ee;
        border-radius: 8px;
        background: #ffffff;
        color: #334155;
        font-size: 13px;
        line-height: 1.45;
    }

    @media (max-width: 1100px) {
        .btw-grid {
            grid-template-columns: minmax(230px, .75fr) minmax(0, 1.25fr);
        }

        .btw-details {
            grid-column: 1 / -1;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
        }

        .btw-details .btw-panel {
            border-bottom: 0;
            padding: 0;
        }
    }

    @media (max-width: 900px) {
        .btw-topbar,
        .btw-card-head {
            display: block;
        }

        .btw-badges,
        .btw-service-box {
            margin-top: 12px;
        }

        .btw-grid {
            grid-template-columns: 1fr;
        }

        .btw-details {
            display: block;
        }

        .btw-details .btw-panel {
            padding: 18px 0;
            border-bottom: 1px solid #dbe3ee;
        }

        .btw-details .btw-panel:last-child {
            border-bottom: 0;
        }

        .btw-conversation-card {
            min-height: auto;
        }
    }

    .dark .busrs-ticket-workspace {
        border-color: #27272a;
        background: #18181b;
        color: #e4e4e7;
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.32);
    }

    .dark .btw-topbar,
    .dark .btw-card,
    .dark .btw-details,
    .dark .btw-conversation-card,
    .dark .btw-form-surface {
        border-color: #27272a;
        background: #09090b;
    }

    .dark .btw-grid {
        background: #27272a;
    }

    .dark .btw-main {
        background: #0f0f12;
    }

    .dark .btw-conversation {
        background: #101014;
    }

    .dark .btw-title,
    .dark .btw-subject,
    .dark .btw-author-name,
    .dark .btw-dl dd,
    .dark .btw-timeline-title,
    .dark .btw-description,
    .dark .btw-service-id {
        color: #f4f4f5;
    }

    .dark .btw-muted,
    .dark .btw-breadcrumb,
    .dark .btw-dl dt,
    .dark .btw-service-label,
    .dark .btw-panel-title {
        color: #a1a1aa;
    }

    .dark .btw-card-head,
    .dark .btw-request,
    .dark .btw-panel,
    .dark .btw-messaging-wrap {
        border-color: #27272a;
    }

    .dark .btw-request {
        background: #111827;
    }

    .dark .btw-service-box,
    .dark .btw-recent-message {
        border-color: #3f3f46;
        background: #18181b;
    }

    .dark .btw-badge {
        border-color: #1d4ed8;
        background: rgba(37, 99, 235, .14);
        color: #93c5fd;
    }

    .dark .btw-badge-priority {
        border-color: #c2410c;
        background: rgba(234, 88, 12, .14);
        color: #fdba74;
    }

    .dark .btw-icon {
        background: rgba(14, 165, 233, .14);
        color: #7dd3fc;
    }

    .dark .btw-avatar {
        background: rgba(124, 58, 237, .22);
        color: #c4b5fd;
    }

    .dark .btw-linkish,
    .dark .btw-ticket-id {
        color: #60a5fa;
    }
</style>

<div class="busrs-ticket-workspace">
    <div class="btw-topbar">
        <div>
            <div class="btw-breadcrumb">
                <span>All tickets</span>
                <span>/</span>
                <span class="btw-ticket-id">{{ $record->ulid }}</span>
            </div>
            <h2 class="btw-title">{{ $record->subject }}</h2>
        </div>

        <div class="btw-badges">
            <span class="btw-badge">{{ $record->status->label() }}</span>
            <span class="btw-badge btw-badge-priority">{{ $record->priority->label() }} priority</span>
        </div>
    </div>

    <div class="btw-grid">
        <aside class="btw-main">
            <div class="btw-card">
                <div class="btw-card-head">
                    <div class="btw-subject-row">
                        <div class="btw-icon"><x-heroicon-o-phone /></div>
                        <div>
                            <h3 class="btw-subject">{{ $record->subject }}</h3>
                            <p class="btw-muted">{{ $requesterName }} created a request {{ $record->created_at->diffForHumans() }}</p>
                        </div>
                    </div>

                    <div class="btw-service-box">
                        <div class="btw-service-label">Service Task</div>
                        <div class="btw-service-id">{{ $record->ulid }}</div>
                    </div>
                </div>

                <section class="btw-request">
                    <div class="btw-author">
                        <div class="btw-avatar">{{ $initials }}</div>
                        <div>
                            <p class="btw-author-name">{{ $requesterName }} submitted this request</p>
                            <p class="btw-muted">{{ $record->created_at->format('M j, Y g:ia') }}</p>
                        </div>
                    </div>

                    <div class="btw-description">{{ $record->description }}</div>
                </section>
            </div>
        </aside>

        <main class="btw-conversation" aria-label="Ticket conversation">
            <div class="btw-conversation-card">
                <section class="btw-messaging-wrap">
                    @livewire('admin.ticket-messaging', ['ticket' => $record], key('admin-ticket-messaging-' . $record->id))
                </section>
            </div>
        </main>

        <aside class="btw-details">
            <section class="btw-panel">
                <h3 class="btw-panel-title">Contact Details</h3>
                <div class="btw-contact-card">
                    <div class="btw-avatar">{{ $initials }}</div>
                    <div>
                        <div class="btw-linkish">{{ $requesterName }}</div>
                        <p class="btw-muted">Requester</p>
                    </div>
                </div>
                <dl class="btw-dl">
                    <div><dt>Email</dt><dd>{{ $requesterEmail }}</dd></div>
                </dl>
            </section>

            <section class="btw-panel">
                <h3 class="btw-panel-title">Properties</h3>
                <dl class="btw-dl">
                    <div><dt>Ticket ID</dt><dd>{{ $record->ulid }}</dd></div>
                    <div><dt>Status</dt><dd>{{ $record->status->label() }}</dd></div>
                    <div><dt>Priority</dt><dd>{{ $record->priority->label() }}</dd></div>
                    <div><dt>Assigned to</dt><dd>{{ $record->assignee?->name ?? 'Unassigned' }}</dd></div>
                    <div><dt>Submitted</dt><dd>{{ $record->created_at->format('M j, Y g:ia') }}</dd></div>
                    <div><dt>Resolved</dt><dd>{{ $record->resolved_at?->format('M j, Y g:ia') ?? 'Not yet resolved' }}</dd></div>
                </dl>
            </section>

            <section class="btw-panel">
                <h3 class="btw-panel-title">Service Task</h3>
                <dl class="btw-dl">
                    <div><dt>Office</dt><dd>{{ $record->office->name }}</dd></div>
                    <div><dt>Service</dt><dd>{{ $record->serviceType->name }}</dd></div>
                    <div><dt>Agent</dt><dd>{{ $record->assignee?->name ?? 'Unassigned' }}</dd></div>
                    <div><dt>Created</dt><dd>{{ $record->created_at->diffForHumans() }}</dd></div>
                </dl>
            </section>

            <section class="btw-panel">
                <h3 class="btw-panel-title">Request Details</h3>
                <dl class="btw-dl">
                    @forelse ($record->serviceType->fields as $field)
                        @php $answer = $customFieldAnswers->get((string) $field->id); @endphp

                        @if ($answer !== null && $answer !== '')
                            <div>
                                <dt>{{ $field->label }}</dt>
                                <dd>{{ is_array($answer) ? implode(', ', $answer) : $answer }}</dd>
                            </div>
                        @endif
                    @empty
                        <div><dd>No custom fields for this service.</dd></div>
                    @endforelse
                </dl>
            </section>

            <section class="btw-panel">
                <h3 class="btw-panel-title">Timeline</h3>
                <div class="btw-timeline">
                    @forelse ($record->history->take(5) as $event)
                        <div class="btw-timeline-item">
                            <span class="btw-dot"></span>
                            <div>
                                <p class="btw-timeline-title">{{ Str::headline($event->event_type->value) }}</p>
                                <p class="btw-muted">{{ $event->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="btw-muted">No activity yet.</p>
                    @endforelse
                </div>
            </section>
        </aside>
    </div>
</div>
