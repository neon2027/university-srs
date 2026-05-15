<div class="flex min-h-[70vh] items-center justify-center">
    <div class="w-full max-w-md">
        @if ($step === 1)
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-white">Welcome to BUSRS</h1>
                <p class="mt-2 text-sm text-zinc-400">How are you affiliated with Bicol University?</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <button wire:click="chooseStudent"
                    class="flex flex-col items-center gap-3 rounded-xl border border-zinc-700 bg-zinc-800 p-8 text-center hover:border-zinc-500 hover:bg-zinc-700 transition-colors">
                    <x-heroicon-o-academic-cap class="h-10 w-10 text-[#0089CB]" />
                    <div>
                        <div class="font-semibold text-white">Student</div>
                        <div class="text-xs text-zinc-400 mt-1">Enrolled at BU</div>
                    </div>
                </button>

                <button wire:click="showEmployeePicker"
                    class="flex flex-col items-center gap-3 rounded-xl border border-zinc-700 bg-zinc-800 p-8 text-center hover:border-zinc-500 hover:bg-zinc-700 transition-colors">
                    <x-heroicon-o-building-office class="h-10 w-10 text-[#FE8926]" />
                    <div>
                        <div class="font-semibold text-white">Employee</div>
                        <div class="text-xs text-zinc-400 mt-1">Faculty or Staff</div>
                    </div>
                </button>
            </div>
        @else
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-white">Select Your Office</h1>
                <p class="mt-2 text-sm text-zinc-400">Your office admin will verify your affiliation before granting staff access.</p>
            </div>

            <div class="rounded-xl border border-zinc-700 bg-zinc-800 p-6">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-zinc-300 mb-2">Office</label>
                    <select wire:model="selectedOfficeId"
                        class="w-full rounded-lg border border-zinc-600 bg-zinc-700 px-3 py-2 text-white focus:border-[#0089CB] focus:outline-none">
                        <option value="">Select an office…</option>
                        @foreach ($this->offices as $office)
                            <option value="{{ $office->id }}">{{ $office->name }}</option>
                        @endforeach
                    </select>
                    @error('selectedOfficeId') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>

                <button wire:click="submitEmployeeRequest"
                    class="w-full rounded-lg bg-[#0089CB] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#0077b3] transition-colors">
                    Submit Verification Request
                </button>

                <button wire:click="$set('step', 1)"
                    class="mt-3 inline-flex w-full items-center justify-center gap-2 text-center text-sm text-zinc-400 hover:text-zinc-200 transition-colors">
                    <x-heroicon-o-arrow-left class="h-4 w-4" />
                    <span>Back</span>
                </button>
            </div>
        @endif
    </div>
</div>
