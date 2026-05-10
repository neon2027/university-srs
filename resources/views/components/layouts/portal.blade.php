<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-zinc-900 text-zinc-100 antialiased">
    <nav class="sticky top-0 z-50 border-b border-zinc-700/60 bg-zinc-900/90 backdrop-blur-sm">
        <div class="mx-auto flex max-w-4xl items-center justify-between px-4 py-3">
            <a href="{{ route('portal.tickets.index') }}" wire:navigate
               class="text-base font-bold tracking-tight text-white">BUSRS</a>
            <div class="flex items-center gap-3">
                <a href="{{ route('portal.tickets.index') }}" wire:navigate
                   class="text-sm text-zinc-400 hover:text-zinc-200 transition-colors">My Requests</a>
                <a href="{{ route('portal.tickets.create') }}" wire:navigate
                   class="rounded-lg bg-[#0089CB] px-3.5 py-1.5 text-sm font-semibold text-white hover:bg-[#0077b3] transition-colors">
                    + New Request
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-zinc-400 hover:text-zinc-200 transition-colors">
                        Log out
                    </button>
                </form>
            </div>
        </div>
    </nav>

    @livewire('portal.onboarding-notice')

    <main class="mx-auto max-w-4xl px-4 py-8">
        {{ $slot }}
    </main>
</body>
</html>
