<div class="space-y-4">
    {{-- Messages list --}}
    <div class="space-y-3 max-h-96 overflow-y-auto pr-2">
        @forelse ($messages as $message)
            <div @class([
                'rounded-lg p-3 text-sm',
                'bg-amber-50 border border-amber-200' => $message->is_internal_note,
                'bg-gray-50 border border-gray-200' => ! $message->is_internal_note,
            ])>
                <div class="flex items-center justify-between mb-1">
                    <span class="font-medium text-gray-900">{{ $message->sender->name }}</span>
                    <div class="flex items-center gap-2">
                        @if ($message->is_internal_note)
                            <span class="text-[10px] font-semibold uppercase tracking-wide text-amber-600 bg-amber-100 px-1.5 py-0.5 rounded">Internal</span>
                        @endif
                        <span class="text-xs text-gray-400">{{ $message->created_at->diffForHumans() }}</span>
                    </div>
                </div>
                <p class="text-gray-700 whitespace-pre-wrap">{{ $message->body }}</p>
            </div>
        @empty
            <p class="text-sm text-gray-400 text-center py-4">No messages yet.</p>
        @endforelse
    </div>

    {{-- Canned responses --}}
    @if ($cannedResponses->isNotEmpty())
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Canned Responses</label>
            <select wire:change="applyCannedResponse($event.target.value)"
                    class="w-full rounded-md border border-gray-300 text-sm px-3 py-1.5">
                <option value="">— Select a template —</option>
                @foreach ($cannedResponses as $canned)
                    <option value="{{ $canned->body }}">{{ $canned->title }}</option>
                @endforeach
            </select>
        </div>
    @endif

    {{-- Compose --}}
    <div class="space-y-2">
        <textarea wire:model="body"
                  rows="3"
                  placeholder="Write a message..."
                  class="w-full rounded-md border border-gray-300 text-sm px-3 py-2 resize-none"></textarea>

        @error('body')
            <p class="text-xs text-red-500">{{ $message }}</p>
        @enderror

        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer select-none">
                <input type="checkbox" wire:model="isInternalNote"
                       class="rounded border-gray-300" />
                <span>Internal note <span class="text-xs text-gray-400">(staff only)</span></span>
            </label>

            <button wire:click="send"
                    wire:loading.attr="disabled"
                    class="px-4 py-1.5 rounded-md text-sm font-semibold text-white bg-sky-600 hover:bg-sky-700">
                <span wire:loading.remove>{{ $isInternalNote ? 'Add Note' : 'Send' }}</span>
                <span wire:loading>Sending…</span>
            </button>
        </div>
    </div>
</div>
