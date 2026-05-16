<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-white text-gray-900 antialiased">

    {{-- Nav --}}
    <header
        x-data="{ scrolled: false, mobileOpen: false, init() { this.scrolled = window.scrollY > 10; window.addEventListener('scroll', () => { this.scrolled = window.scrollY > 10; }, { passive: true }); } }"
        x-effect="mobileOpen ? document.body.style.overflow = 'hidden' : document.body.style.overflow = ''"
        :class="scrolled ? 'border-gray-200 bg-white/95 shadow-sm backdrop-blur-lg' : 'border-transparent'"
        class="sticky top-0 z-50 w-full border-b transition-all duration-300"
    >
        <nav class="mx-auto flex min-h-14 w-full max-w-5xl flex-wrap items-center justify-between gap-2 px-4 py-2">
            <a href="{{ route('home') }}" class="flex flex-col rounded-md px-2 py-1 leading-none transition-colors hover:bg-black/5">
                <span class="text-[10px] font-extrabold tracking-widest text-[#0089CB]">BICOL UNIVERSITY</span>
                <span class="text-[9px] font-medium tracking-widest text-gray-500">iBUConnect</span>
            </a>
            <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                <a href="{{ route('project.overview') }}"
                   class="inline-flex items-center gap-1.5 rounded-md px-3 py-1.5 text-sm font-medium text-gray-600 transition-colors hover:bg-black/5 hover:text-gray-900">
                    Project Overview
                </a>
                <a href="{{ route('track.ticket') }}"
                   class="inline-flex items-center gap-1.5 rounded-md px-3 py-1.5 text-sm font-medium text-gray-600 transition-colors hover:bg-black/5 hover:text-gray-900">
                    Track Ticket
                </a>
                <a href="{{ route('auth.google') }}"
                   class="inline-flex items-center gap-2 rounded-md bg-[#0089CB] px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-[#0077b3]">
                    Sign In
                </a>
            </div>
        </nav>
    </header>

    {{-- Page content --}}
    <main>
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="border-t bg-gray-50/60">
        <div class="mx-auto max-w-6xl px-4 lg:px-6">
            <div class="grid grid-cols-2 gap-8 py-8 md:grid-cols-4">
                <div>
                    <h3 class="mb-4 text-xs font-semibold text-gray-900">University</h3>
                    <ul class="space-y-2 text-sm text-gray-500">
                        <li><a href="#" class="transition-colors hover:text-gray-900">About Bicol University</a></li>
                        <li><a href="#" class="transition-colors hover:text-gray-900">University Website</a></li>
                        <li><a href="#" class="transition-colors hover:text-gray-900">BU Portal</a></li>
                        <li><a href="#" class="transition-colors hover:text-gray-900">Announcements</a></li>
                        <li><a href="#" class="transition-colors hover:text-gray-900">Academic Calendar</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="mb-4 text-xs font-semibold text-gray-900">Support</h3>
                    <ul class="space-y-2 text-sm text-gray-500">
                        <li><a href="mailto:itsupport@bicol-u.edu.ph" class="transition-colors hover:text-gray-900">Technical Support</a></li>
                        <li><a href="#" class="transition-colors hover:text-gray-900">Help Center</a></li>
                        <li><a href="#" class="transition-colors hover:text-gray-900">FAQs</a></li>
                        <li><a href="{{ route('project.overview') }}" class="transition-colors hover:text-gray-900">Project overview</a></li>
                        <li><a href="{{ route('track.ticket') }}" class="transition-colors hover:text-gray-900">Track your ticket</a></li>
                    </ul>
                    <div class="mt-3 space-y-0.5">
                        <p class="text-[11px] text-gray-400">itsupport@bicol-u.edu.ph</p>
                        <p class="text-[11px] text-gray-400">Mon–Fri, 8:00 AM – 5:00 PM</p>
                    </div>
                </div>
                <div>
                    <h3 class="mb-4 text-xs font-semibold text-gray-900">Legal</h3>
                    <ul class="space-y-2 text-sm text-gray-500">
                        <li><a href="{{ route('legal.terms') }}" class="transition-colors hover:text-gray-900">Terms of Use</a></li>
                        <li><a href="{{ route('legal.privacy') }}" class="transition-colors hover:text-gray-900">Privacy Policy</a></li>
                        <li><a href="{{ route('legal.cookies') }}" class="transition-colors hover:text-gray-900">Cookie Policy</a></li>
                        <li><a href="{{ route('legal.data-protection') }}" class="transition-colors hover:text-gray-900">Data Protection</a></li>
                        <li><a href="{{ route('legal.transparency') }}" class="transition-colors hover:text-gray-900">Transparency Report</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="mb-4 text-xs font-semibold text-gray-900">Portal</h3>
                    <ul class="space-y-2 text-sm text-gray-500">
                        <li><a href="{{ route('auth.google') }}" class="transition-colors hover:text-gray-900">Sign In</a></li>
                        <li><a href="{{ route('home') }}" class="transition-colors hover:text-gray-900">Home</a></li>
                    </ul>
                </div>
            </div>

            <div class="h-px bg-gray-200"></div>

            <div class="py-4 text-center text-xs text-gray-400">
                <p>© {{ date('Y') }} Bicol University — iBUConnect. All rights reserved.</p>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
