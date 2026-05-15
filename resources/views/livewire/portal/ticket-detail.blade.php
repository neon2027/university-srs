<div class="flex flex-col overflow-hidden h-[calc(100dvh-120px)] min-h-[640px] border border-slate-200 rounded-[18px] bg-slate-50 text-slate-900 shadow-[0_18px_40px_rgba(15,23,42,.16)] dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-100 dark:shadow-[0_18px_40px_rgba(0,0,0,.35)] max-[1180px]:h-auto max-[1180px]:min-h-0 max-[820px]:h-auto max-[820px]:min-h-0"
     wire:poll.5s>
    @php
        $initials = \Illuminate\Support\Str::of(auth()->user()->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => \Illuminate\Support\Str::substr($word, 0, 1))
            ->implode('');
    @endphp

    {{-- Topbar --}}
    <header class="flex items-center justify-between gap-4 px-4 py-3 border-b border-slate-200 bg-white shrink-0 dark:border-zinc-800 dark:bg-zinc-900 max-[820px]:flex-col max-[820px]:items-start">
        <div class="flex flex-wrap gap-2 items-center text-slate-500 text-sm">
            <a href="{{ route('portal.tickets.index') }}" wire:navigate class="hover:text-slate-900 dark:hover:text-zinc-100">All requests</a>
            <span>/</span>
            <span class="font-mono text-blue-700 font-extrabold dark:text-blue-400">{{ $ticket->ulid }}</span>
        </div>
        <a href="{{ route('portal.tickets.create') }}" wire:navigate
           class="inline-flex items-center rounded-lg bg-orange-500 text-white px-3 py-2 text-sm font-extrabold no-underline hover:bg-orange-600 transition-colors">
            New Request
        </a>
    </header>

    {{-- 3-column grid --}}
    <div class="flex-1 min-h-0 grid grid-cols-[310px_minmax(0,1fr)_310px] gap-px bg-slate-200 overflow-hidden dark:bg-zinc-800 max-[1180px]:grid-cols-[300px_minmax(0,1fr)] max-[1180px]:flex-none max-[1180px]:overflow-visible max-[820px]:grid-cols-1 max-[820px]:flex max-[820px]:flex-col max-[820px]:bg-transparent">

        {{-- Left Rail --}}
        <aside class="bg-white overflow-y-auto dark:bg-zinc-900 max-[820px]:overflow-visible">
            <div class="px-4 py-3.5 border-b border-slate-100 dark:border-zinc-800">
                <p class="m-0 text-sm font-extrabold text-slate-900 dark:text-zinc-100">Requests</p>
            </div>

            @foreach ($recentTickets as $recent)
                <a href="{{ route('portal.tickets.show', $recent->ulid) }}" wire:navigate
                   class="block px-4 py-3.5 border-b border-slate-100 text-inherit no-underline transition-colors dark:border-zinc-800 {{ $recent->id === $ticket->id ? 'bg-blue-50 dark:bg-zinc-800' : 'hover:bg-blue-50/60 dark:hover:bg-zinc-800/60' }}">
                    <p class="m-0 text-sm font-extrabold text-slate-900 leading-snug dark:text-zinc-100">{{ $recent->subject }}</p>
                    <p class="mt-1 m-0 text-slate-500 text-xs leading-snug dark:text-zinc-400">
                        <span class="inline-block w-[7px] h-[7px] mr-1 rounded-full bg-blue-600 align-middle"></span>{{ $recent->status->label() }}<br>
                        {{ $recent->ulid }} · {{ $recent->updated_at->diffForHumans() }}
                    </p>
                </a>
            @endforeach
        </aside>

        {{-- Center --}}
        <main class="flex flex-col bg-slate-50 p-[18px] overflow-hidden min-h-0 dark:bg-zinc-950 max-[1180px]:overflow-visible max-[820px]:block max-[820px]:overflow-visible">
            <section class="flex-1 flex flex-col overflow-hidden min-h-0 border border-slate-200 rounded-xl bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900 max-[820px]:block max-[820px]:overflow-visible">

                {{-- Card header --}}
                <div class="px-5 py-[18px] border-b border-slate-200 shrink-0 dark:border-zinc-800">
                    <div class="flex items-start justify-between gap-3.5 max-[820px]:flex-col">
                        <div>
                            <h1 class="m-0 text-[22px] font-extrabold text-slate-900 leading-tight dark:text-zinc-100">{{ $ticket->subject }}</h1>
                            <p class="m-0 mt-1.5 text-slate-500 text-sm dark:text-zinc-400">{{ $ticket->office->name }} · {{ $ticket->serviceType->name }}</p>
                        </div>
                        <span class="font-mono text-blue-700 font-extrabold text-sm shrink-0 dark:text-blue-400">{{ $ticket->ulid }}</span>
                    </div>
                    <div class="flex flex-wrap gap-2 mt-3.5">
                        <span class="rounded-full bg-blue-50 text-blue-700 border border-blue-200 px-2.5 py-1 text-xs font-extrabold dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800">
                            {{ $ticket->status->label() }}
                        </span>
                        <span class="rounded-full bg-orange-50 text-orange-700 border border-orange-200 px-2.5 py-1 text-xs font-extrabold dark:bg-orange-950/30 dark:text-orange-300 dark:border-orange-800">
                            {{ $ticket->priority->label() }} priority
                        </span>
                    </div>
                </div>

                {{-- Messages --}}
                <div class="flex-1 flex flex-col overflow-hidden min-h-0 p-5 max-[820px]:block max-[820px]:overflow-visible">
                    <div class="flex items-center justify-between mb-3.5 shrink-0">
                        <h2 class="m-0 text-[15px] font-extrabold text-slate-900 dark:text-zinc-100">Conversation</h2>
                        <span class="rounded-full bg-slate-100 text-slate-600 px-2.5 py-1 text-xs font-extrabold dark:bg-zinc-800 dark:text-zinc-300">
                            {{ $messages->count() }} {{ \Illuminate\Support\Str::plural('message', $messages->count()) }}
                        </span>
                    </div>

                    <div class="flex-1 min-h-0 space-y-3 overflow-y-auto pr-2 pb-3 [scrollbar-width:thin] [scrollbar-color:theme(colors.slate.300)_transparent] dark:[scrollbar-color:theme(colors.zinc.600)_transparent] max-[820px]:min-h-[18rem] max-[820px]:max-h-[24rem]"
                         x-data
                         x-init="$el.scrollTop = $el.scrollHeight"
                         x-on:message-sent.window="$nextTick(() => $el.scrollTop = $el.scrollHeight)">
                        @forelse ($messages as $message)
                            @php
                                $senderName = $message->sender?->name ?? $message->guest_name ?? 'Requester';
                                $senderInitials = \Illuminate\Support\Str::of($senderName)
                                    ->explode(' ')
                                    ->take(2)
                                    ->map(fn ($word) => \Illuminate\Support\Str::substr($word, 0, 1))
                                    ->implode('');
                                $isMine = $message->sender_id === auth()->id();
                            @endphp

                            <article class="flex w-full {{ $isMine ? 'justify-end' : 'justify-start' }}">
                                <div class="flex w-fit max-w-[82%] items-end gap-2 sm:max-w-[34rem] {{ $isMine ? 'flex-row-reverse' : '' }}">
                                    <div class="grid h-[34px] w-[34px] shrink-0 place-items-center rounded-full text-[11px] font-black {{ $isMine ? 'bg-blue-100 text-blue-600 dark:bg-blue-950/40 dark:text-blue-300' : 'bg-slate-200 text-slate-500 dark:bg-zinc-700 dark:text-zinc-300' }}">
                                        {{ $senderInitials }}
                                    </div>

                                    <div class="flex min-w-0 max-w-full flex-col {{ $isMine ? 'items-end' : 'items-start' }}">
                                        <div class="w-fit max-w-full min-w-10 px-3.5 py-2.5 whitespace-pre-wrap [overflow-wrap:anywhere] text-sm leading-relaxed shadow-sm {{ $isMine ? 'rounded-[18px_18px_6px_18px] bg-blue-600 text-white' : 'rounded-[18px_18px_18px_6px] bg-slate-100 text-slate-900 dark:bg-zinc-800 dark:text-zinc-100' }}">
                                            {{ $message->body }}
                                        </div>
                                        <div class="mt-1 max-w-full truncate text-slate-500 text-[11px] dark:text-zinc-400 {{ $isMine ? 'text-right' : '' }}">
                                            {{ $isMine ? 'You' : $senderName }} · {{ $message->created_at->format('M j, g:ia') }}
                                            @if ($isMine && $message->seen_at)
                                                · Seen
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="flex min-h-[14rem] items-center justify-center rounded-xl border border-dashed border-slate-200 bg-slate-50 text-center text-slate-500 text-sm dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-400">
                                No messages yet. Send a reply below to start the conversation.
                            </div>
                        @endforelse
                    </div>

                    {{-- Compose --}}
                    <form wire:submit.prevent="sendMessage"
                          class="mt-4 shrink-0 border border-slate-200 rounded-[10px] bg-white p-3.5 dark:border-zinc-700 dark:bg-zinc-950">
                        <textarea wire:model="messageBody"
                                  wire:keydown.ctrl.enter="sendMessage"
                                  placeholder="Type a reply..."
                                  class="block w-full min-h-[104px] resize-y border border-slate-300 rounded-[9px] bg-white text-slate-900 px-3 py-2.5 text-sm leading-relaxed outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100 dark:placeholder:text-zinc-500"></textarea>
                        @error('messageBody')
                            <p class="mt-2 text-red-700 text-sm font-bold dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <div class="flex items-center justify-between gap-3 mt-3 max-[820px]:flex-col max-[820px]:items-start">
                            <span class="text-slate-500 text-xs dark:text-zinc-400">Press Ctrl+Enter to send</span>
                            <button type="submit"
                                    class="border-0 rounded-[9px] bg-gray-900 text-white px-3.5 py-2.5 text-sm font-extrabold cursor-pointer hover:bg-gray-800 transition-colors dark:bg-blue-600 dark:hover:bg-blue-500">
                                Send Reply
                            </button>
                        </div>
                    </form>
                </div>
            </section>
        </main>

        {{-- Right Side --}}
        <aside class="bg-white overflow-y-auto p-[18px] dark:bg-zinc-900 max-[1180px]:col-span-2 max-[1180px]:overflow-visible max-[820px]:overflow-visible">

            {{-- Description --}}
            <section class="pb-[18px] border-b border-slate-200 dark:border-zinc-800"
                     x-data="{ expanded: false, truncated: false }"
                     x-init="truncated = $refs.desc.scrollHeight > $refs.desc.clientHeight">
                <div class="flex gap-3 items-center mb-2.5">
                    <div class="w-[42px] h-[42px] grid place-items-center rounded-[10px] bg-violet-100 text-violet-700 text-sm font-black shrink-0 dark:bg-violet-950/30 dark:text-violet-300">
                        {{ $initials }}
                    </div>
                    <div>
                        <p class="m-0 text-sm font-extrabold text-slate-900 dark:text-zinc-100">{{ auth()->user()->name }}</p>
                        <p class="m-0 text-slate-500 text-sm dark:text-zinc-400">{{ $ticket->created_at->format('M j, Y g:ia') }}</p>
                    </div>
                </div>
                <div x-ref="desc"
                     class="whitespace-pre-wrap text-slate-700 text-sm leading-relaxed dark:text-zinc-300"
                     :class="{ 'line-clamp-3': !expanded }">{{ $ticket->description }}</div>
                <button type="button"
                        x-show="truncated || expanded"
                        x-cloak
                        @click="expanded = !expanded"
                        class="mt-2 bg-transparent border-0 p-0 text-blue-600 text-sm font-bold cursor-pointer leading-relaxed dark:text-blue-400 hover:underline"
                        x-text="expanded ? 'See less' : 'See more'">
                </button>
            </section>

            {{-- Request Details --}}
            <section class="py-[18px] border-b border-slate-200 dark:border-zinc-800">
                <h3 class="m-0 mb-3.5 text-xs font-black tracking-[.04em] uppercase text-slate-500 dark:text-zinc-400">Request Details</h3>
                <dl class="grid gap-3.5">
                    <div>
                        <dt class="text-xs font-extrabold text-slate-500 dark:text-zinc-400">Ticket ID</dt>
                        <dd class="mt-1 m-0 text-sm font-extrabold text-slate-900 [overflow-wrap:anywhere] dark:text-zinc-100">{{ $ticket->ulid }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-extrabold text-slate-500 dark:text-zinc-400">Status</dt>
                        <dd class="mt-1 m-0 text-sm font-extrabold text-slate-900 [overflow-wrap:anywhere] dark:text-zinc-100">{{ $ticket->status->label() }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-extrabold text-slate-500 dark:text-zinc-400">Office</dt>
                        <dd class="mt-1 m-0 text-sm font-extrabold text-slate-900 [overflow-wrap:anywhere] dark:text-zinc-100">{{ $ticket->office->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-extrabold text-slate-500 dark:text-zinc-400">Service</dt>
                        <dd class="mt-1 m-0 text-sm font-extrabold text-slate-900 [overflow-wrap:anywhere] dark:text-zinc-100">{{ $ticket->serviceType->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-extrabold text-slate-500 dark:text-zinc-400">Submitted</dt>
                        <dd class="mt-1 m-0 text-sm font-extrabold text-slate-900 [overflow-wrap:anywhere] dark:text-zinc-100">{{ $ticket->created_at->format('M j, Y g:ia') }}</dd>
                    </div>
                </dl>
            </section>

            {{-- Status Timeline --}}
            <section class="py-[18px] border-b border-slate-200 dark:border-zinc-800">
                <h3 class="m-0 mb-3.5 text-xs font-black tracking-[.04em] uppercase text-slate-500 dark:text-zinc-400">Status Timeline</h3>
                @forelse ($ticket->history as $event)
                    <div class="flex gap-2.5 py-2.5 border-b border-slate-100 last:border-0 dark:border-zinc-800">
                        <span class="w-2.5 h-2.5 mt-[5px] rounded-full bg-blue-600 shrink-0"></span>
                        <div>
                            <strong class="block text-sm text-slate-900 dark:text-zinc-100">{{ \Illuminate\Support\Str::headline($event->event_type->value) }}</strong>
                            <span class="block mt-0.5 text-slate-500 text-xs dark:text-zinc-400">{{ $event->actor?->name ?? 'System' }} · {{ $event->created_at->format('M j, g:ia') }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-slate-500 text-sm dark:text-zinc-400">No events yet.</p>
                @endforelse
            </section>

            {{-- Knowledge Base --}}
            <section class="pt-[18px]">
                <h3 class="m-0 mb-3.5 text-xs font-black tracking-[.04em] uppercase text-slate-500 dark:text-zinc-400">Knowledge base</h3>
                <div class="flex gap-2.5 py-2.5 border-b border-slate-100 dark:border-zinc-800">
                    <div class="w-8 h-8 grid place-items-center rounded-lg bg-sky-100 text-sky-700 shrink-0 dark:bg-sky-950/30 dark:text-sky-400">
                        <x-heroicon-o-document-text class="w-[17px] h-[17px] stroke-2" />
                    </div>
                    <div>
                        <strong class="block text-sm text-slate-900 dark:text-zinc-100">Keep details complete</strong>
                        <span class="block mt-0.5 text-slate-500 text-xs dark:text-zinc-400">Add reference numbers, dates, and context in your replies.</span>
                    </div>
                </div>
                <div class="flex gap-2.5 py-2.5 border-b border-slate-100 dark:border-zinc-800">
                    <div class="w-8 h-8 grid place-items-center rounded-lg bg-sky-100 text-sky-700 shrink-0 dark:bg-sky-950/30 dark:text-sky-400">
                        <x-heroicon-o-chat-bubble-left-right class="w-[17px] h-[17px] stroke-2" />
                    </div>
                    <div>
                        <strong class="block text-sm text-slate-900 dark:text-zinc-100">Watch for staff replies</strong>
                        <span class="block mt-0.5 text-slate-500 text-xs dark:text-zinc-400">Updates from the office will appear in this conversation thread.</span>
                    </div>
                </div>
                <div class="flex gap-2.5 py-2.5">
                    <div class="w-8 h-8 grid place-items-center rounded-lg bg-sky-100 text-sky-700 shrink-0 dark:bg-sky-950/30 dark:text-sky-400">
                        <x-heroicon-o-ticket class="w-[17px] h-[17px] stroke-2" />
                    </div>
                    <div>
                        <strong class="block text-sm text-slate-900 dark:text-zinc-100">Use one ticket per concern</strong>
                        <span class="block mt-0.5 text-slate-500 text-xs dark:text-zinc-400">Create a separate request when the issue belongs to another service.</span>
                    </div>
                </div>
            </section>
        </aside>
    </div>
</div>
