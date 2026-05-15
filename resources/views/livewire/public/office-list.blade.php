<div>
    {{-- Page header --}}
    <div class="border-b border-gray-100 bg-gradient-to-br from-[#0089CB]/5 to-white">
        <div class="mx-auto max-w-6xl px-4 py-14">
            <p class="mb-3 text-sm font-semibold tracking-widest text-[#0089CB] uppercase">Bicol University</p>
            <h1 class="mb-3 text-4xl font-bold text-gray-900">University Offices</h1>
            <p class="max-w-xl text-lg text-gray-500">
                Browse offices, explore the services they provide, and submit requests directly.
            </p>
        </div>
    </div>

    {{-- Office grid --}}
    <div class="mx-auto max-w-6xl px-4 py-12">
        @if ($offices->isEmpty())
            <p class="py-16 text-center text-gray-500">No offices available at this time.</p>
        @else
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($offices as $office)
                    <a href="{{ route('offices.show', $office->slug) }}"
                       class="group flex flex-col rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition-all hover:border-[#0089CB]/50 hover:shadow-md">
                        <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-xl bg-[#0089CB]/10">
                            <x-heroicon-o-building-office-2 class="h-6 w-6 text-[#0089CB]" />
                        </div>
                        <h2 class="text-base font-semibold text-gray-900 transition-colors group-hover:text-[#0089CB]">
                            {{ $office->name }}
                        </h2>
                        @if ($office->description)
                            <p class="mt-2 flex-1 line-clamp-2 text-sm text-gray-500">{{ $office->description }}</p>
                        @endif
                        @if ($office->email)
                            <p class="mt-3 text-xs text-gray-400">{{ $office->email }}</p>
                        @endif
                        <div class="mt-5 flex items-center gap-1 text-sm font-semibold text-[#0089CB]">
                            View services
                            <x-heroicon-o-arrow-right class="h-4 w-4 transition-transform group-hover:translate-x-0.5" />
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>
