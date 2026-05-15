<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')
</head>
<body class="bg-white antialiased">

    <header class="sticky top-0 z-50 w-full border-b border-gray-200 bg-white/95 backdrop-blur-lg">
        <nav class="mx-auto flex h-14 w-full max-w-6xl items-center justify-between px-4">
            <a href="{{ route('home') }}"
               class="flex flex-col rounded-md px-2 py-1 leading-none transition-colors hover:bg-black/5">
                <span class="text-[10px] font-extrabold tracking-widest text-[#0089CB]">BICOL UNIVERSITY</span>
                <span class="text-[9px] font-medium tracking-widest text-gray-500">SERVICE REQUEST SYSTEM</span>
            </a>

            <div class="flex items-center gap-4">
                <a href="{{ route('offices.index') }}"
                   class="text-sm font-medium transition-colors {{ request()->routeIs('offices.*') ? 'text-gray-900' : 'text-gray-500 hover:text-gray-900' }}">
                    Offices
                </a>

                @auth
                    <a href="{{ route('portal.tickets.index') }}"
                       class="rounded-md bg-[#0089CB] px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-[#0077b3]">
                        My Portal
                    </a>
                @else
                    <a href="{{ route('auth.google') }}"
                       class="rounded-md bg-[#0089CB] px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-[#0077b3]">
                        Sign In
                    </a>
                @endauth
            </div>
        </nav>
    </header>

    <main>
        {{ $slot }}
    </main>

    <footer class="mt-20 border-t border-gray-100 bg-gray-50 py-10">
        <div class="mx-auto max-w-6xl px-4 text-center text-sm text-gray-400">
            &copy; {{ date('Y') }} {{ config('app.name') }} &middot; Bicol University
        </div>
    </footer>

</body>
</html>
