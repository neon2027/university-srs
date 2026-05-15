<div class="mx-auto max-w-3xl px-4 py-12">
    @if (! $isVerified)
        <div class="mb-8 text-center">
            <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-[#0089CB]/10 text-lg font-bold text-[#0089CB]">
                ?
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Track your Ticket</h1>
            <p class="mt-2 text-gray-500">Enter your ticket number and last name to view your request status.</p>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-8 shadow-sm">
            @if ($lookupError)
                <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                    {{ $lookupError }}
                </div>
            @endif

            <form wire:submit.prevent="lookup" class="space-y-5">
                <div>
                    <label for="ticket-number" class="mb-1.5 block text-sm font-semibold text-gray-700">Ticket Number</label>
                    <input
                        id="ticket-number"
                        type="text"
                        wire:model="ticketNumber"
                        placeholder="e.g. OSS-T-26-0001"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 font-mono text-sm focus:border-[#0089CB] focus:outline-none focus:ring-2 focus:ring-[#0089CB]/20"
                    >
                    @error('ticketNumber')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="last-name" class="mb-1.5 block text-sm font-semibold text-gray-700">Last Name</label>
                    <input
                        id="last-name"
                        type="text"
                        wire:model="lastName"
                        placeholder="Your last name"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-[#0089CB] focus:outline-none focus:ring-2 focus:ring-[#0089CB]/20"
                    >
                    @error('lastName')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="w-full rounded-lg bg-[#0089CB] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-[#0077b3] disabled:opacity-60"
                >
                    <span wire:loading.remove>Find my ticket</span>
                    <span wire:loading>Searching...</span>
                </button>
            </form>
        </div>
    @else
        <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="mb-1 font-mono text-xs font-bold text-[#0089CB]">{{ $ticket->ulid }}</p>
                <h1 class="text-2xl font-bold text-gray-900">{{ $ticket->subject }}</h1>
                <p class="mt-1 text-sm text-gray-500">{{ $ticket->office->name }} · {{ $ticket->serviceType->name }}</p>
            </div>

            <div class="flex items-center gap-3">
                @php
                    $statusColors = [
                        'warning' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                        'info' => 'bg-blue-50 text-blue-700 border-blue-200',
                        'primary' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                        'success' => 'bg-green-50 text-green-700 border-green-200',
                        'gray' => 'bg-gray-100 text-gray-600 border-gray-200',
                        'danger' => 'bg-red-50 text-red-700 border-red-200',
                    ];
                    $colorClass = $statusColors[$ticket->status->color()] ?? 'bg-gray-100 text-gray-600 border-gray-200';
                @endphp
                <span class="rounded-full border px-3 py-1 text-xs font-bold {{ $colorClass }}">
                    {{ $ticket->status->label() }}
                </span>
                <button wire:click="clearSession" class="text-sm text-gray-400 underline transition-colors hover:text-gray-600">
                    Search another ticket
                </button>
            </div>
        </div>

        <div class="space-y-4">
            @forelse ($messages as $message)
                @php
                    $isGuest = $message->sender_id === null;
                    $senderName = $isGuest ? ($message->guest_name ?? 'Requester') : $message->sender->name;
                    $initials = \Illuminate\Support\Str::of($senderName)
                        ->explode(' ')
                        ->take(2)
                        ->map(fn ($word) => \Illuminate\Support\Str::substr($word, 0, 1))
                        ->implode('');
                @endphp

                <div class="rounded-lg border {{ $isGuest ? 'border-gray-200 bg-white' : 'border-blue-100 bg-blue-50' }} p-5" wire:key="pub-msg-{{ $message->id }}">
                    <div class="mb-3 flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg {{ $isGuest ? 'bg-violet-100 text-violet-700' : 'bg-[#0089CB]/20 text-[#0089CB]' }} text-xs font-bold">
                            {{ $initials }}
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-900">{{ $senderName }}</p>
                            <p class="text-xs text-gray-400">{{ $message->created_at->diffForHumans() }}</p>
                        </div>
                        @unless ($isGuest)
                            <span class="ml-auto rounded-full bg-[#0089CB]/10 px-2.5 py-0.5 text-xs font-semibold text-[#0089CB]">Staff</span>
                        @endunless
                    </div>

                    <p class="whitespace-pre-wrap text-sm text-gray-700">{{ $message->body }}</p>

                    @if ($message->requests_attachment)
                        <div class="mt-4 rounded-lg border border-dashed border-[#0089CB]/40 bg-[#0089CB]/5 p-4">
                            <p class="mb-3 text-xs font-semibold text-[#0089CB]">Attachment requested</p>
                            <form wire:submit.prevent="uploadAttachment({{ $message->id }})" class="flex flex-wrap items-center gap-3">
                                <input
                                    type="file"
                                    wire:model="attachmentFiles.{{ $message->id }}"
                                    class="text-xs text-gray-600"
                                    accept=".pdf,.jpg,.jpeg,.png,.gif,.webp,.doc,.docx,.xlsx,.csv"
                                >
                                <button
                                    type="submit"
                                    wire:loading.attr="disabled"
                                    class="shrink-0 rounded-lg bg-[#0089CB] px-3 py-1.5 text-xs font-semibold text-white transition-colors hover:bg-[#0077b3] disabled:opacity-60"
                                >
                                    <span wire:loading.remove wire:target="uploadAttachment({{ $message->id }})">Upload</span>
                                    <span wire:loading wire:target="uploadAttachment({{ $message->id }})">Uploading...</span>
                                </button>
                            </form>
                            @error("attachmentFiles.{$message->id}")
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif
                </div>
            @empty
                <div class="rounded-lg border border-dashed border-gray-200 py-12 text-center text-sm text-gray-400">
                    No messages yet. Your ticket is being reviewed.
                </div>
            @endforelse
        </div>

        <div class="mt-6 rounded-lg border border-gray-200 bg-white p-6">
            <h3 class="mb-4 text-sm font-bold text-gray-900">Reply to this ticket</h3>
            <form wire:submit.prevent="sendReply" class="space-y-4">
                <textarea
                    wire:model="replyBody"
                    rows="4"
                    placeholder="Type your message..."
                    class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-[#0089CB] focus:outline-none focus:ring-2 focus:ring-[#0089CB]/20"
                ></textarea>
                @error('replyBody')
                    <p class="text-xs text-red-600">{{ $message }}</p>
                @enderror
                <div class="flex justify-end">
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="rounded-lg bg-[#0089CB] px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-[#0077b3] disabled:opacity-60"
                    >
                        <span wire:loading.remove wire:target="sendReply">Send Reply</span>
                        <span wire:loading wire:target="sendReply">Sending...</span>
                    </button>
                </div>
            </form>
        </div>
    @endif
</div>
