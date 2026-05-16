<div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
    <div class="mb-3 flex items-center gap-2">
        <span class="inline-flex items-center gap-1.5 rounded-md border border-amber-300 bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700">
            <flux:icon.bug-ant class="size-3" />
            DEV
        </span>
        <span class="text-sm font-medium text-amber-800">Quick Access</span>
    </div>

    @if ($this->users->isEmpty())
        <p class="text-center text-xs text-amber-600">
            No users found. Run
            <code class="rounded bg-amber-100 px-1 font-mono">php artisan db:seed --class=SystemDemoSeeder</code>
            to create test users.
        </p>
    @else
        <div class="flex flex-col gap-3">
            <flux:select
                wire:model.live="userId"
                searchable
                placeholder="Search and select a user..."
                class="bg-white"
            >
                @foreach ($this->users as $user)
                    <flux:select.option value="{{ $user->id }}">
                        {{ $user->name }}
                        @if ($user->roles->isNotEmpty())
                            — {{ $user->roles->pluck('name')->join(', ') }}
                        @endif
                    </flux:select.option>
                @endforeach
            </flux:select>

            <flux:button
                wire:click="login"
                wire:loading.attr="disabled"
                variant="filled"
                class="w-full bg-amber-600! hover:bg-amber-700! border-amber-700! text-white! disabled:opacity-50"
            >
                <span wire:loading.remove wire:target="login">
                    {{ $userId ? 'Login as selected user' : 'Select a user above' }}
                </span>
                <span wire:loading wire:target="login">Signing in...</span>
            </flux:button>
        </div>
    @endif
</div>
