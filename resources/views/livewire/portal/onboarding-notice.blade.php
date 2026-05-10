@php $status = auth()->user()?->onboarding_status; @endphp

@if ($status === \App\Enums\OnboardingStatus::PendingEmployee)
    <div class="border-b border-yellow-500/30 bg-yellow-500/10 px-4 py-3 text-sm text-yellow-300">
        <div class="mx-auto flex max-w-4xl items-center gap-2">
            <span>⏳</span>
            <span>
                Your request to join <strong>{{ auth()->user()->pendingOffice?->name }}</strong>
                is pending verification. You can still submit requests while you wait.
            </span>
        </div>
    </div>
@elseif ($status === \App\Enums\OnboardingStatus::Rejected)
    <div class="border-b border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300">
        <div class="mx-auto max-w-4xl">
            <div class="flex items-center gap-2 mb-2">
                <span>✗</span>
                <span>Your employee verification request was not approved.</span>
            </div>

            @if ($showOfficeSelector)
                <div class="mt-3 flex items-end gap-3">
                    <div class="flex-1">
                        <select wire:model="selectedOfficeId"
                            class="w-full rounded-lg border border-zinc-600 bg-zinc-800 px-3 py-1.5 text-sm text-white focus:border-[#0089CB] focus:outline-none">
                            <option value="">Select an office…</option>
                            @foreach ($this->offices as $office)
                                <option value="{{ $office->id }}">{{ $office->name }}</option>
                            @endforeach
                        </select>
                        @error('selectedOfficeId') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>
                    <button wire:click="reapply"
                        class="rounded-lg bg-[#0089CB] px-4 py-1.5 text-sm font-semibold text-white hover:bg-[#0077b3] transition-colors">
                        Submit
                    </button>
                    <button wire:click="$set('showOfficeSelector', false)"
                        class="text-sm text-zinc-400 hover:text-zinc-200 transition-colors">
                        Cancel
                    </button>
                </div>
            @else
                <div class="flex items-center gap-4 mt-1">
                    <button wire:click="showReapplyForm"
                        class="text-sm font-medium text-red-200 underline hover:text-white transition-colors">
                        Apply to a Different Office
                    </button>
                    <button wire:click="continueAsStudent"
                        class="text-sm text-zinc-400 hover:text-zinc-200 transition-colors">
                        Continue as Student
                    </button>
                </div>
            @endif
        </div>
    </div>
@endif
