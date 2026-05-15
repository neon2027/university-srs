<div x-data="{ viewerOpen: false, viewerUrl: '', viewerType: '', viewerTitle: '' }">

    {{-- File viewer modal --}}
    <div x-show="viewerOpen"
         x-cloak
         @keydown.escape.window="viewerOpen = false"
         @click.self="viewerOpen = false"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
        <div @click.stop
             class="flex max-h-[90vh] w-full max-w-5xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl">
            <div class="flex shrink-0 items-center justify-between border-b border-gray-100 px-6 py-4">
                <h3 class="text-base font-semibold text-gray-900" x-text="viewerTitle"></h3>
                <button @click="viewerOpen = false"
                        class="rounded-lg p-1.5 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600">
                    <x-heroicon-o-x-mark class="h-5 w-5" />
                </button>
            </div>
            <div class="flex-1 overflow-auto p-2">
                <template x-if="viewerType === 'pdf'">
                    <iframe :src="viewerUrl" class="h-[75vh] w-full rounded border border-gray-100"></iframe>
                </template>
                <template x-if="viewerType === 'image'">
                    <img :src="viewerUrl" class="mx-auto max-h-[75vh] max-w-full rounded object-contain">
                </template>
            </div>
        </div>
    </div>

    {{-- Office header --}}
    <div class="border-b border-gray-100 bg-gradient-to-br from-[#0089CB]/5 to-white">
        <div class="mx-auto max-w-6xl px-4 py-12">
            <a href="{{ route('offices.index') }}"
               class="mb-5 inline-flex items-center gap-1.5 text-sm font-medium text-gray-400 transition-colors hover:text-gray-600">
                <x-heroicon-o-arrow-left class="h-4 w-4" />
                All offices
            </a>

            <div class="flex flex-wrap items-start justify-between gap-6">
                <div>
                    <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-[#0089CB]/10">
                        <x-heroicon-o-building-office-2 class="h-7 w-7 text-[#0089CB]" />
                    </div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $office->name }}</h1>
                    @if ($office->description)
                        <p class="mt-2 max-w-2xl text-gray-500">{{ $office->description }}</p>
                    @endif
                    @if ($office->email)
                        <a href="mailto:{{ $office->email }}"
                           class="mt-3 inline-flex items-center gap-1.5 text-sm text-gray-400 transition-colors hover:text-[#0089CB]">
                            <x-heroicon-o-envelope class="h-4 w-4" />
                            {{ $office->email }}
                        </a>
                    @endif
                </div>

                @if ($office->citizen_charter)
                    @php
                        $ext = strtolower(pathinfo($office->citizen_charter, PATHINFO_EXTENSION));
                        $charterType = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']) ? 'image' : 'pdf';
                        $charterUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($office->citizen_charter);
                    @endphp
                    <button @click="viewerUrl='{{ $charterUrl }}'; viewerType='{{ $charterType }}'; viewerTitle='Citizen Charter'; viewerOpen=true"
                            class="inline-flex shrink-0 items-center gap-2 rounded-xl border border-[#0089CB]/30 bg-[#0089CB]/5 px-4 py-2.5 text-sm font-semibold text-[#0089CB] transition-all hover:bg-[#0089CB]/10">
                        <x-heroicon-o-document-text class="h-4 w-4" />
                        View Citizen Charter
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- Services --}}
    <div class="mx-auto max-w-6xl px-4 py-12">
        @if ($office->serviceCategories->isEmpty())
            <div class="rounded-2xl border border-dashed border-gray-200 py-20 text-center text-gray-400">
                <x-heroicon-o-squares-2x2 class="mx-auto mb-3 h-10 w-10 text-gray-300" />
                No services listed yet.
            </div>
        @else
            <div class="space-y-10">
                @foreach ($office->serviceCategories as $category)
                    @if ($category->serviceTypes->isNotEmpty())
                        <section>
                            <h2 class="mb-1 text-xl font-bold text-gray-900">{{ $category->name }}</h2>
                            @if ($category->description)
                                <p class="mb-5 text-sm text-gray-500">{{ $category->description }}</p>
                            @else
                                <div class="mb-5"></div>
                            @endif

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                @foreach ($category->serviceTypes as $service)
                                    <div class="flex flex-col rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                                        <div class="mb-3 flex items-start justify-between gap-3">
                                            <h3 class="text-base font-semibold leading-snug text-gray-900">{{ $service->name }}</h3>
                                            @if ($service->sla_days)
                                                <span class="shrink-0 rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-500">
                                                    {{ $service->sla_days }}d SLA
                                                </span>
                                            @endif
                                        </div>

                                        @if ($service->description)
                                            <p class="mb-4 flex-1 text-sm text-gray-500">{{ $service->description }}</p>
                                        @else
                                            <div class="flex-1"></div>
                                        @endif

                                        <div class="flex flex-wrap items-center gap-2 border-t border-gray-100 pt-3">
                                            @if ($service->work_instruction)
                                                @php
                                                    $ext = strtolower(pathinfo($service->work_instruction, PATHINFO_EXTENSION));
                                                    $wiType = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']) ? 'image' : 'pdf';
                                                    $wiUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($service->work_instruction);
                                                @endphp
                                                <button @click="viewerUrl='{{ $wiUrl }}'; viewerType='{{ $wiType }}'; viewerTitle='Work Instruction — {{ addslashes($service->name) }}'; viewerOpen=true"
                                                        class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-600 transition-colors hover:border-gray-300 hover:bg-gray-50">
                                                    <x-heroicon-o-document-text class="h-3.5 w-3.5" />
                                                    View Work Instruction
                                                </button>
                                            @endif

                                            @if ($canRequest)
                                                <a href="{{ route('portal.tickets.create', ['prefillServiceTypeId' => $service->id]) }}"
                                                   class="ml-auto inline-flex items-center gap-1.5 rounded-lg bg-[#0089CB] px-3 py-1.5 text-xs font-semibold text-white transition-colors hover:bg-[#0077b3]">
                                                    <x-heroicon-o-paper-airplane class="h-3.5 w-3.5" />
                                                    Request this service
                                                </a>
                                            @else
                                                <a href="{{ route('auth.google') }}"
                                                   class="ml-auto inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-500 transition-colors hover:bg-gray-50">
                                                    Sign in to request
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endif
                @endforeach
            </div>
        @endif
    </div>
</div>
