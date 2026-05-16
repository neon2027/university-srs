<x-filament-panels::page>
    @php
        $offices = $this->accessibleOffices();
        $data = $this->getReportData();

        $starDisplay = function(float|null $val): string {
            if ($val === null) return '—';
            $full = floor($val);
            $half = ($val - $full) >= 0.5 ? 1 : 0;
            $empty = 5 - $full - $half;
            return str_repeat('★', $full) . str_repeat('½', $half) . str_repeat('☆', $empty) . " ({$val})";
        };
    @endphp

    {{-- Filters --}}
    <div class="bg-white border border-gray-200 rounded-xl p-6 dark:bg-gray-900 dark:border-gray-700">
        <h2 class="text-base font-bold text-gray-900 dark:text-white mb-4">Generate Report</h2>
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Office</label>
                <select wire:model="officeId"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                    <option value="">— Select office —</option>
                    @foreach ($offices as $office)
                        <option value="{{ $office->id }}">{{ $office->name }}</option>
                    @endforeach
                </select>
                @error('officeId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">From</label>
                <input type="date" wire:model="dateFrom"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white" />
                @error('dateFrom') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">To</label>
                <input type="date" wire:model="dateTo"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white" />
                @error('dateTo') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="flex gap-2">
                <button wire:click="generate"
                        class="flex-1 rounded-lg bg-primary-600 text-white px-4 py-2 text-sm font-bold hover:bg-primary-700 transition-colors">
                    Generate
                </button>
                @if ($generated && $officeId)
                    <a href="{{ $this->getPrintUrl() }}" target="_blank"
                       class="flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white text-gray-700 px-4 py-2 text-sm font-bold hover:bg-gray-50 transition-colors dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 no-underline">
                        <x-heroicon-o-printer class="w-4 h-4" /> Print
                    </a>
                @endif
            </div>
        </div>
    </div>

    @if ($generated && count($data))
        @php
            $office = $data['office'];
            $totalTickets = $data['totalTickets'];
            $resolvedTickets = $data['resolvedTickets'];
            $pendingTickets = $data['pendingTickets'];
            $inProgressTickets = $data['inProgressTickets'];
            $cancelledTickets = $data['cancelledTickets'];
            $avgResolutionHours = $data['avgResolutionHours'];
            $totalRatings = $data['totalRatings'];
            $avgOverall = $data['avgOverall'];
            $avgService = $data['avgService'];
            $avgStaff = $data['avgStaff'];
            $ratingDistribution = $data['ratingDistribution'];
            $byService = $data['byService'];
            $byStaff = $data['byStaff'];

            $resolutionRate = $totalTickets > 0 ? round(($resolvedTickets / $totalTickets) * 100, 1) : 0;
            $responseRate = $totalTickets > 0 ? round(($totalRatings / $totalTickets) * 100, 1) : 0;
            $avgHoursLabel = $avgResolutionHours !== null
                ? ($avgResolutionHours >= 24
                    ? round($avgResolutionHours / 24, 1).' days'
                    : round($avgResolutionHours, 1).' hrs')
                : '—';
        @endphp

        {{-- Report Header --}}
        <div class="bg-white border border-gray-200 rounded-xl p-6 dark:bg-gray-900 dark:border-gray-700">
            <div class="flex items-start justify-between gap-4 flex-wrap">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $office->name }}</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                        Performance Report &mdash;
                        {{ \Carbon\Carbon::parse($data['from'])->format('M j, Y') }}
                        to
                        {{ \Carbon\Carbon::parse($data['to'])->format('M j, Y') }}
                    </p>
                </div>
                <span class="text-xs text-gray-400 dark:text-gray-500">Generated {{ now()->format('M j, Y g:i A') }}</span>
            </div>
        </div>

        {{-- Summary Stats --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            @foreach ([
                ['Total Tickets', $totalTickets, 'text-gray-900', 'bg-gray-50 border-gray-200'],
                ['Resolved', $resolvedTickets, 'text-green-700', 'bg-green-50 border-green-200'],
                ['Pending', $pendingTickets, 'text-yellow-700', 'bg-yellow-50 border-yellow-200'],
                ['In Progress', $inProgressTickets, 'text-blue-700', 'bg-blue-50 border-blue-200'],
            ] as [$label, $value, $textColor, $bgBorder])
                <div class="rounded-xl border {{ $bgBorder }} p-5 dark:bg-gray-900 dark:border-gray-700">
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">{{ $label }}</p>
                    <p class="text-3xl font-black {{ $textColor }} dark:text-white">{{ $value }}</p>
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="rounded-xl border border-gray-200 bg-gray-50 p-5 dark:bg-gray-900 dark:border-gray-700">
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Resolution Rate</p>
                <p class="text-3xl font-black text-gray-900 dark:text-white">{{ $resolutionRate }}%</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-gray-50 p-5 dark:bg-gray-900 dark:border-gray-700">
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Avg. Resolution Time</p>
                <p class="text-3xl font-black text-gray-900 dark:text-white">{{ $avgHoursLabel }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-gray-50 p-5 dark:bg-gray-900 dark:border-gray-700">
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Total Ratings</p>
                <p class="text-3xl font-black text-gray-900 dark:text-white">{{ $totalRatings }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ $responseRate }}% response rate</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-gray-50 p-5 dark:bg-gray-900 dark:border-gray-700">
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Avg. Overall Rating</p>
                <p class="text-3xl font-black text-yellow-500">{{ $avgOverall ?? '—' }}</p>
                @if ($avgOverall)
                    <p class="text-xs text-gray-400 mt-0.5">out of 5.00</p>
                @endif
            </div>
        </div>

        {{-- Rating Summary --}}
        @if ($totalRatings > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Rating Breakdown --}}
                <div class="bg-white border border-gray-200 rounded-xl p-6 dark:bg-gray-900 dark:border-gray-700">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4">Rating Breakdown</h3>
                    <div class="space-y-3">
                        @foreach ([
                            ['Overall Experience', $avgOverall],
                            ['Service Quality', $avgService],
                            ['Staff Helpfulness', $avgStaff],
                        ] as [$label, $avg])
                            <div class="flex items-center gap-3">
                                <span class="text-xs font-semibold text-gray-600 dark:text-gray-400 w-36 shrink-0">{{ $label }}</span>
                                @if ($avg !== null)
                                    <div class="flex-1 bg-gray-100 dark:bg-gray-800 rounded-full h-2">
                                        <div class="bg-yellow-400 h-2 rounded-full" style="width: {{ ($avg / 5) * 100 }}%"></div>
                                    </div>
                                    <span class="text-xs font-bold text-gray-700 dark:text-gray-300 w-8 text-right">{{ $avg }}</span>
                                @else
                                    <span class="text-xs text-gray-400">No data</span>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-5 pt-4 border-t border-gray-100 dark:border-gray-800">
                        <p class="text-xs font-bold text-gray-500 dark:text-gray-400 mb-2.5">Overall Distribution</p>
                        @foreach (range(5, 1) as $star)
                            @php $count = $ratingDistribution[$star]; $pct = $totalRatings > 0 ? ($count / $totalRatings) * 100 : 0; @endphp
                            <div class="flex items-center gap-2 mb-1.5">
                                <span class="text-xs text-yellow-500 w-4">{{ $star }}★</span>
                                <div class="flex-1 bg-gray-100 dark:bg-gray-800 rounded-full h-1.5">
                                    <div class="bg-yellow-400 h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
                                </div>
                                <span class="text-xs text-gray-500 w-6 text-right">{{ $count }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Cancelled / Status Summary --}}
                <div class="bg-white border border-gray-200 rounded-xl p-6 dark:bg-gray-900 dark:border-gray-700">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4">Ticket Status Summary</h3>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <th class="text-left text-xs font-semibold text-gray-500 dark:text-gray-400 pb-2">Status</th>
                                <th class="text-right text-xs font-semibold text-gray-500 dark:text-gray-400 pb-2">Count</th>
                                <th class="text-right text-xs font-semibold text-gray-500 dark:text-gray-400 pb-2">Share</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                            @foreach ([
                                ['Resolved / Closed', $resolvedTickets, 'text-green-600'],
                                ['Pending', $pendingTickets, 'text-yellow-600'],
                                ['In Progress', $inProgressTickets, 'text-blue-600'],
                                ['Cancelled', $cancelledTickets, 'text-red-500'],
                            ] as [$label, $count, $color])
                                <tr>
                                    <td class="py-2 text-xs font-medium {{ $color }}">{{ $label }}</td>
                                    <td class="py-2 text-xs text-right font-bold text-gray-700 dark:text-gray-300">{{ $count }}</td>
                                    <td class="py-2 text-xs text-right text-gray-500">
                                        {{ $totalTickets > 0 ? round(($count / $totalTickets) * 100, 1) : 0 }}%
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- By Service --}}
        @if ($byService->isNotEmpty())
            <div class="bg-white border border-gray-200 rounded-xl overflow-hidden dark:bg-gray-900 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Performance by Service</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="text-left text-xs font-semibold text-gray-500 dark:text-gray-400 px-6 py-3">Service</th>
                                <th class="text-right text-xs font-semibold text-gray-500 dark:text-gray-400 px-4 py-3">Total</th>
                                <th class="text-right text-xs font-semibold text-gray-500 dark:text-gray-400 px-4 py-3">Resolved</th>
                                <th class="text-right text-xs font-semibold text-gray-500 dark:text-gray-400 px-4 py-3">Rated</th>
                                <th class="text-right text-xs font-semibold text-gray-500 dark:text-gray-400 px-4 py-3">Avg Overall</th>
                                <th class="text-right text-xs font-semibold text-gray-500 dark:text-gray-400 px-4 py-3">Avg Service</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach ($byService as $row)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                    <td class="px-6 py-3 font-medium text-gray-900 dark:text-white">{{ $row['name'] }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-gray-700 dark:text-gray-300">{{ $row['total'] }}</td>
                                    <td class="px-4 py-3 text-right text-green-600 font-semibold">{{ $row['resolved'] }}</td>
                                    <td class="px-4 py-3 text-right text-gray-500">{{ $row['rated_count'] }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-yellow-500">
                                        {{ $row['avg_overall'] !== null ? $row['avg_overall'] : '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold text-yellow-500">
                                        {{ $row['avg_service'] !== null ? $row['avg_service'] : '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- By Staff --}}
        @if ($byStaff->isNotEmpty())
            <div class="bg-white border border-gray-200 rounded-xl overflow-hidden dark:bg-gray-900 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Performance by Administrator / Staff</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="text-left text-xs font-semibold text-gray-500 dark:text-gray-400 px-6 py-3">Name</th>
                                <th class="text-right text-xs font-semibold text-gray-500 dark:text-gray-400 px-4 py-3">Handled</th>
                                <th class="text-right text-xs font-semibold text-gray-500 dark:text-gray-400 px-4 py-3">Resolved</th>
                                <th class="text-right text-xs font-semibold text-gray-500 dark:text-gray-400 px-4 py-3">Rated</th>
                                <th class="text-right text-xs font-semibold text-gray-500 dark:text-gray-400 px-4 py-3">Avg Overall</th>
                                <th class="text-right text-xs font-semibold text-gray-500 dark:text-gray-400 px-4 py-3">Avg Staff Rating</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach ($byStaff as $row)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                    <td class="px-6 py-3 font-medium text-gray-900 dark:text-white">{{ $row['name'] }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-gray-700 dark:text-gray-300">{{ $row['handled'] }}</td>
                                    <td class="px-4 py-3 text-right text-green-600 font-semibold">{{ $row['resolved'] }}</td>
                                    <td class="px-4 py-3 text-right text-gray-500">{{ $row['rated_count'] }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-yellow-500">
                                        {{ $row['avg_overall'] !== null ? $row['avg_overall'] : '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold text-yellow-500">
                                        {{ $row['avg_staff'] !== null ? $row['avg_staff'] : '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

    @elseif ($generated)
        <div class="bg-white border border-gray-200 rounded-xl p-12 text-center dark:bg-gray-900 dark:border-gray-700">
            <x-heroicon-o-document-magnifying-glass class="w-10 h-10 text-gray-300 mx-auto mb-3 dark:text-gray-600" />
            <p class="text-gray-500 dark:text-gray-400 font-medium">No tickets found for the selected period.</p>
        </div>
    @endif
</x-filament-panels::page>
