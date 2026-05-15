<div class="busrs-admin-messages" wire:poll.10s>
    <style>
        .busrs-admin-messages,
        .busrs-admin-messages * {
            box-sizing: border-box;
        }

    .busrs-admin-messages {
        display: grid;
        gap: 16px;
    }

    .bam-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .bam-title {
        margin: 0;
        color: #0f172a;
        font-size: 14px;
        font-weight: 800;
    }

    .bam-count {
        border-radius: 999px;
        background: #f1f5f9;
        color: #475569;
        padding: 5px 10px;
        font-size: 12px;
        font-weight: 700;
    }

    .bam-list {
        display: grid;
        gap: 12px;
    }

    .bam-message {
        overflow: hidden;
        border: 1px solid #bfdbfe;
        border-radius: 9px;
        background: #eff6ff;
        box-shadow: 0 3px 12px rgba(15, 23, 42, 0.04);
    }

    .bam-message-note {
        border-color: #fde68a;
        background: #fffbeb;
    }

    .bam-message-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 14px;
        border-bottom: 1px solid rgba(15, 23, 42, 0.06);
    }

    .bam-author {
        display: flex;
        gap: 10px;
        align-items: center;
        min-width: 0;
    }

    .bam-avatar {
        display: grid;
        width: 36px;
        height: 36px;
        place-items: center;
        border-radius: 7px;
        background: #ddd6fe;
        color: #5b21b6;
        font-size: 12px;
        font-weight: 800;
        flex: 0 0 auto;
    }

    .bam-message-note .bam-avatar {
        background: #fef3c7;
        color: #92400e;
    }

    .bam-name {
        margin: 0;
        color: #0f172a;
        font-size: 13px;
        font-weight: 800;
    }

    .bam-meta {
        margin: 2px 0 0;
        color: #64748b;
        font-size: 12px;
    }

    .bam-chip {
        border-radius: 999px;
        background: #fef3c7;
        color: #92400e;
        padding: 5px 9px;
        font-size: 11px;
        font-weight: 800;
        white-space: nowrap;
    }

    .bam-body {
        padding: 14px;
        white-space: pre-wrap;
        overflow-wrap: anywhere;
        color: #1e293b;
        font-size: 14px;
        line-height: 1.6;
    }

    .bam-empty {
        border: 1px dashed #cbd5e1;
        border-radius: 9px;
        background: #f8fafc;
        padding: 32px 16px;
        color: #64748b;
        text-align: center;
        font-size: 14px;
    }

    .bam-form {
        border: 1px solid #dbe3ee;
        border-radius: 9px;
        background: #ffffff;
        padding: 16px;
        box-shadow: 0 3px 12px rgba(15, 23, 42, 0.04);
    }

    .bam-form-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 12px;
    }

    .bam-form-title {
        margin: 0;
        color: #0f172a;
        font-size: 14px;
        font-weight: 800;
    }

    .bam-checkbox {
        display: inline-flex;
        gap: 8px;
        align-items: center;
        border: 1px solid #dbe3ee;
        border-radius: 7px;
        background: #f8fafc;
        padding: 8px 10px;
        color: #334155;
        font-size: 13px;
        font-weight: 650;
        cursor: pointer;
    }

    .bam-field {
        margin-bottom: 12px;
    }

    .bam-label {
        display: block;
        margin-bottom: 6px;
        color: #475569;
        font-size: 12px;
        font-weight: 800;
    }

    .bam-select,
    .bam-textarea {
        display: block;
        width: 100%;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        background: #ffffff;
        color: #0f172a;
        font-size: 14px;
        outline: 0;
    }

    .bam-select {
        padding: 9px 10px;
    }

    .bam-textarea {
        min-height: 128px;
        resize: vertical;
        padding: 10px 12px;
        line-height: 1.6;
    }

    .bam-select:focus,
    .bam-textarea:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }

    .bam-error {
        margin: 0 0 12px;
        border: 1px solid #fecaca;
        border-radius: 8px;
        background: #fef2f2;
        color: #b91c1c;
        padding: 9px 10px;
        font-size: 13px;
        font-weight: 650;
    }

    .bam-actions {
        display: flex;
        justify-content: flex-end;
        margin-top: 12px;
    }

    .bam-submit {
        border: 0;
        border-radius: 8px;
        background: #2563eb;
        color: #ffffff;
        padding: 10px 16px;
        font-size: 14px;
        font-weight: 800;
        cursor: pointer;
    }

    .bam-submit:hover {
        background: #1d4ed8;
    }

        .bam-submit:disabled {
            cursor: not-allowed;
            opacity: .6;
        }

        .dark .busrs-admin-messages {
            color: #e4e4e7;
        }

        .dark .bam-title,
        .dark .bam-form-title,
        .dark .bam-name {
            color: #f4f4f5;
        }

        .dark .bam-count,
        .dark .bam-meta,
        .dark .bam-label {
            color: #a1a1aa;
        }

        .dark .bam-count,
        .dark .bam-checkbox {
            background: #18181b;
            border-color: #3f3f46;
        }

        .dark .bam-message {
            border-color: #1d4ed8;
            background: rgba(30, 64, 175, .22);
        }

        .dark .bam-message-note {
            border-color: #92400e;
            background: rgba(146, 64, 14, .18);
        }

        .dark .bam-message-head {
            border-color: rgba(255, 255, 255, .08);
        }

        .dark .bam-body {
            color: #e4e4e7;
        }

        .dark .bam-empty,
        .dark .bam-form {
            border-color: #3f3f46;
            background: #09090b;
        }

        .dark .bam-empty {
            color: #a1a1aa;
        }

        .dark .bam-avatar {
            background: rgba(124, 58, 237, .22);
            color: #c4b5fd;
        }

        .dark .bam-message-note .bam-avatar,
        .dark .bam-chip {
            background: rgba(146, 64, 14, .28);
            color: #fcd34d;
        }

        .dark .bam-checkbox {
            color: #d4d4d8;
        }

        .dark .bam-select,
        .dark .bam-textarea {
            border-color: #3f3f46;
            background: #18181b;
            color: #f4f4f5;
        }

        .dark .bam-select:focus,
        .dark .bam-textarea:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, .18);
        }

        .dark .bam-error {
            border-color: #7f1d1d;
            background: rgba(127, 29, 29, .22);
            color: #fecaca;
        }

        @media (max-width: 700px) {
            .bam-head,
            .bam-form-top,
        .bam-message-head {
            align-items: flex-start;
            flex-direction: column;
        }
    }
    </style>

    <div class="bam-head">
        <h3 class="bam-title">Messages and Notes</h3>
        <span class="bam-count">{{ $messages->count() }} {{ \Illuminate\Support\Str::plural('message', $messages->count()) }}</span>
    </div>

    <div class="bam-list">
        @forelse ($messages as $message)
            @php
                $senderName = $message->sender?->name ?? $message->guest_name ?? 'Unknown';
                $initials = \Illuminate\Support\Str::of($senderName)
                    ->explode(' ')
                    ->take(2)
                    ->map(fn ($word) => \Illuminate\Support\Str::substr($word, 0, 1))
                    ->implode('');
            @endphp

            <article class="bam-message {{ $message->is_internal_note ? 'bam-message-note' : '' }}" wire:key="admin-ticket-message-{{ $message->id }}">
                <header class="bam-message-head">
                    <div class="bam-author">
                        <div class="bam-avatar">{{ $initials }}</div>
                        <div>
                            <p class="bam-name">{{ $senderName }}</p>
                            <p class="bam-meta">
                                {{ $message->is_internal_note ? 'added an internal note' : 'added a public reply' }}
                                @if ($message->sender === null)
                                    <span class="bam-chip" style="font-size:10px;padding:2px 7px;">Via public tracker</span>
                                @endif
                                {{ $message->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>

                    @if ($message->is_internal_note)
                        <span class="bam-chip">Staff only</span>
                    @endif
                </header>

                <div class="bam-body">{{ $message->body }}</div>
            </article>
        @empty
            <div class="bam-empty">Start the thread with a public reply or internal staff note.</div>
        @endforelse
    </div>

    <form wire:submit.prevent="send" class="bam-form">
        <div class="bam-form-top">
            <h4 class="bam-form-title">{{ $isInternalNote ? 'Add Internal Note' : 'Reply to Requester' }}</h4>

            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <label class="bam-checkbox">
                    <input type="checkbox" wire:model.live="isInternalNote">
                    <span>Internal note</span>
                </label>
                @unless ($isInternalNote)
                    <label class="bam-checkbox">
                        <input type="checkbox" wire:model.live="requestsAttachment">
                        <span>Request attachment</span>
                    </label>
                @endunless
            </div>
        </div>

        @if ($cannedResponses->isNotEmpty())
            <div class="bam-field">
                <label for="canned-response" class="bam-label">Saved response</label>
                <select id="canned-response" wire:change="applyCannedResponse($event.target.value)" class="bam-select">
                    <option value="">Select a template</option>
                    @foreach ($cannedResponses as $canned)
                        <option value="{{ $canned->body }}">{{ $canned->title }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        @if ($errorMessage)
            <div class="bam-error">{{ $errorMessage }}</div>
        @endif

        <textarea wire:model="body" rows="5" class="bam-textarea"
            placeholder="{{ $isInternalNote ? 'Add a staff-only note...' : 'Type your reply...' }}"></textarea>

        @error('body')
            <p class="bam-error">{{ $message }}</p>
        @enderror

        <div class="bam-actions">
            <button type="submit" class="bam-submit" wire:loading.attr="disabled" wire:target="send">
                <span wire:loading.remove wire:target="send">{{ $isInternalNote ? 'Add Note' : 'Send Reply' }}</span>
                <span wire:loading wire:target="send">Sending...</span>
            </button>
        </div>
    </form>
</div>
