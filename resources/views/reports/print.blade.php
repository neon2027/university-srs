<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Service Report – {{ $office->name }}</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 11pt;
            color: #1e293b;
            background: #fff;
            padding: 0;
        }

        /* ── Print page setup ── */
        @page { size: A4 portrait; margin: 18mm 16mm 18mm 16mm; }
        @media print {
            .no-print { display: none !important; }
            .page-break { page-break-before: always; }
            body { padding: 0; }
        }

        /* ── Screen wrapper ── */
        .page-wrap {
            max-width: 820px;
            margin: 0 auto;
            padding: 32px 24px 48px;
        }

        /* ── Print button ── */
        .print-bar {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-bottom: 28px;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 18px;
            border-radius: 7px;
            font-size: 10pt;
            font-weight: 700;
            cursor: pointer;
            border: none;
            text-decoration: none;
        }
        .btn-print { background: #0f172a; color: #fff; }
        .btn-back  { background: #f1f5f9; color: #334155; }
        .btn:hover { opacity: .88; }

        /* ── Report header ── */
        .report-header {
            border-bottom: 3px solid #0f172a;
            padding-bottom: 16px;
            margin-bottom: 24px;
        }
        .report-header .institution {
            font-size: 9pt;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #64748b;
            margin-bottom: 6px;
        }
        .report-header h1 {
            font-size: 20pt;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.15;
        }
        .report-header .subtitle {
            margin-top: 4px;
            font-size: 10pt;
            color: #475569;
        }
        .report-header .meta {
            margin-top: 10px;
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .report-header .meta span {
            font-size: 9pt;
            color: #64748b;
        }
        .report-header .meta strong { color: #0f172a; }

        /* ── Section titles ── */
        .section-title {
            font-size: 10pt;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #0f172a;
            margin: 28px 0 12px;
            padding-bottom: 6px;
            border-bottom: 1.5px solid #e2e8f0;
        }

        /* ── Summary grid ── */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 10px;
        }
        .stat-box {
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            padding: 14px 16px;
        }
        .stat-box .label {
            font-size: 8pt;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #64748b;
            margin-bottom: 5px;
        }
        .stat-box .value {
            font-size: 20pt;
            font-weight: 800;
            color: #0f172a;
            line-height: 1;
        }
        .stat-box .sub {
            font-size: 8pt;
            color: #94a3b8;
            margin-top: 3px;
        }
        .stat-box.green .value  { color: #16a34a; }
        .stat-box.yellow .value { color: #d97706; }
        .stat-box.blue .value   { color: #2563eb; }
        .stat-box.orange .value { color: #ea580c; }
        .stat-box.gold .value   { color: #b45309; }

        /* ── Tables ── */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5pt;
        }
        thead tr {
            background: #f8fafc;
            border-bottom: 2px solid #cbd5e1;
        }
        thead th {
            padding: 9px 10px;
            text-align: left;
            font-size: 8pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #64748b;
        }
        thead th.right { text-align: right; }
        tbody tr { border-bottom: 1px solid #f1f5f9; }
        tbody tr:last-child { border-bottom: 2px solid #cbd5e1; }
        tbody tr:nth-child(even) { background: #fafafa; }
        tbody td { padding: 8px 10px; vertical-align: middle; }
        tbody td.right { text-align: right; }
        tbody td.bold  { font-weight: 700; }
        tbody td.green { color: #16a34a; font-weight: 600; }
        tbody td.gold  { color: #b45309; font-weight: 700; }
        tbody td.muted { color: #94a3b8; }
        tfoot td {
            padding: 8px 10px;
            font-size: 8.5pt;
            color: #64748b;
            border-top: 1.5px solid #cbd5e1;
        }

        /* ── Rating bars ── */
        .rating-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        .rating-card {
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            padding: 16px;
        }
        .rating-card h4 {
            font-size: 9pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #64748b;
            margin-bottom: 12px;
        }
        .bar-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 7px;
        }
        .bar-row .bar-label { font-size: 8.5pt; color: #475569; width: 110px; flex-shrink: 0; }
        .bar-track { flex: 1; background: #f1f5f9; border-radius: 99px; height: 7px; }
        .bar-fill  { background: #f59e0b; border-radius: 99px; height: 7px; }
        .bar-row .bar-val { font-size: 8.5pt; font-weight: 700; color: #1e293b; width: 28px; text-align: right; }

        .dist-row { display: flex; align-items: center; gap: 6px; margin-bottom: 5px; }
        .dist-star { font-size: 8.5pt; color: #f59e0b; width: 22px; }
        .dist-track { flex: 1; background: #f1f5f9; border-radius: 99px; height: 6px; }
        .dist-fill  { background: #f59e0b; border-radius: 99px; height: 6px; }
        .dist-count { font-size: 8.5pt; color: #64748b; width: 22px; text-align: right; }

        /* ── Footer ── */
        .report-footer {
            margin-top: 40px;
            padding-top: 14px;
            border-top: 1.5px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            flex-wrap: wrap;
            gap: 10px;
        }
        .report-footer p { font-size: 8pt; color: #94a3b8; }
        .sig-block { text-align: right; }
        .sig-line {
            width: 180px;
            border-top: 1.5px solid #334155;
            margin-left: auto;
            margin-top: 36px;
            margin-bottom: 4px;
        }
        .sig-label { font-size: 8pt; color: #475569; font-weight: 600; }
    </style>
</head>
<body>
<div class="page-wrap">

    {{-- Print / Back buttons (screen only) --}}
    <div class="print-bar no-print">
        <a href="javascript:history.back()" class="btn btn-back">← Back</a>
        <button onclick="window.print()" class="btn btn-print">⎙ Print / Save as PDF</button>
    </div>

    {{-- Report Header --}}
    <header class="report-header">
        <p class="institution">Bicol University – IBUConnect Ticketing System</p>
        <h1>Service Performance Report</h1>
        <p class="subtitle">{{ $office->name }}</p>
        <div class="meta">
            <span><strong>Period:</strong> {{ \Carbon\Carbon::parse($from)->format('F j, Y') }} – {{ \Carbon\Carbon::parse($to)->format('F j, Y') }}</span>
            <span><strong>Generated:</strong> {{ now()->format('F j, Y \a\t g:i A') }}</span>
            <span><strong>Prepared by:</strong> {{ auth()->user()->name }}</span>
        </div>
    </header>

    {{-- I. Ticket Volume Summary --}}
    <h2 class="section-title">I. Ticket Volume Summary</h2>

    <div class="stat-grid">
        <div class="stat-box">
            <p class="label">Total Tickets</p>
            <p class="value">{{ $totalTickets }}</p>
        </div>
        <div class="stat-box green">
            <p class="label">Resolved / Closed</p>
            <p class="value">{{ $resolvedTickets }}</p>
            <p class="sub">{{ $resolutionRate }}% resolution rate</p>
        </div>
        <div class="stat-box yellow">
            <p class="label">Pending</p>
            <p class="value">{{ $pendingTickets }}</p>
        </div>
        <div class="stat-box blue">
            <p class="label">In Progress</p>
            <p class="value">{{ $inProgressTickets }}</p>
        </div>
    </div>

    <div class="stat-grid" style="grid-template-columns: repeat(3,1fr); margin-top:0;">
        <div class="stat-box orange">
            <p class="label">Cancelled</p>
            <p class="value">{{ $cancelledTickets }}</p>
        </div>
        <div class="stat-box">
            <p class="label">Avg. Resolution Time</p>
            <p class="value" style="font-size:16pt;">{{ $avgHoursLabel }}</p>
        </div>
        <div class="stat-box gold">
            <p class="label">Client Response Rate</p>
            <p class="value" style="font-size:16pt;">{{ $responseRate }}%</p>
            <p class="sub">{{ $totalRatings }} of {{ $totalTickets }} rated</p>
        </div>
    </div>

    <table style="margin-top:14px;">
        <thead>
            <tr>
                <th>Status</th>
                <th class="right">Count</th>
                <th class="right">% of Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ([
                ['Resolved / Closed', $resolvedTickets, 'green'],
                ['In Progress (Assigned / In Progress)', $inProgressTickets, ''],
                ['Pending', $pendingTickets, ''],
                ['Cancelled', $cancelledTickets, 'muted'],
            ] as [$label, $count, $cls])
                <tr>
                    <td class="{{ $cls }}">{{ $label }}</td>
                    <td class="right bold">{{ $count }}</td>
                    <td class="right muted">{{ $totalTickets > 0 ? number_format(($count / $totalTickets) * 100, 1) : '0.0' }}%</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr><td colspan="3">Tickets created between {{ \Carbon\Carbon::parse($from)->format('M j, Y') }} and {{ \Carbon\Carbon::parse($to)->format('M j, Y') }}.</td></tr>
        </tfoot>
    </table>

    {{-- II. Client Satisfaction Ratings --}}
    <h2 class="section-title">II. Client Satisfaction Ratings</h2>

    @if ($totalRatings === 0)
        <p style="color:#94a3b8;font-size:9.5pt;padding:12px 0;">No ratings were submitted for this period.</p>
    @else
        <div class="stat-grid" style="grid-template-columns:repeat(3,1fr);">
            <div class="stat-box gold">
                <p class="label">Avg. Overall Experience</p>
                <p class="value" style="font-size:16pt;">{{ $avgOverall ?? '—' }} <span style="font-size:10pt;color:#f59e0b;">/ 5.00</span></p>
            </div>
            <div class="stat-box gold">
                <p class="label">Avg. Service Quality</p>
                <p class="value" style="font-size:16pt;">{{ $avgService ?? '—' }} <span style="font-size:10pt;color:#f59e0b;">/ 5.00</span></p>
            </div>
            <div class="stat-box gold">
                <p class="label">Avg. Staff Helpfulness</p>
                <p class="value" style="font-size:16pt;">{{ $avgStaff ?? '—' }} <span style="font-size:10pt;color:#f59e0b;">/ 5.00</span></p>
                @if (!$avgStaff)<p class="sub">No staff ratings</p>@endif
            </div>
        </div>

        <div class="rating-section" style="margin-top:14px;">
            <div class="rating-card">
                <h4>Rating Averages</h4>
                @foreach ([
                    ['Overall Experience', $avgOverall],
                    ['Service Quality', $avgService],
                    ['Staff Helpfulness', $avgStaff],
                ] as [$label, $avg])
                    <div class="bar-row">
                        <span class="bar-label">{{ $label }}</span>
                        <div class="bar-track">
                            <div class="bar-fill" style="width:{{ $avg !== null ? ($avg/5)*100 : 0 }}%"></div>
                        </div>
                        <span class="bar-val">{{ $avg ?? '—' }}</span>
                    </div>
                @endforeach
            </div>
            <div class="rating-card">
                <h4>Overall Rating Distribution</h4>
                @foreach (range(5, 1) as $star)
                    @php $count = $ratingDistribution[$star]; $pct = $totalRatings > 0 ? ($count/$totalRatings)*100 : 0; @endphp
                    <div class="dist-row">
                        <span class="dist-star">{{ $star }}★</span>
                        <div class="dist-track"><div class="dist-fill" style="width:{{ $pct }}%"></div></div>
                        <span class="dist-count">{{ $count }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- III. Performance by Service --}}
    @if ($byService->isNotEmpty())
        <h2 class="section-title">III. Performance by Service Type</h2>
        <table>
            <thead>
                <tr>
                    <th>Service</th>
                    <th class="right">Total</th>
                    <th class="right">Resolved</th>
                    <th class="right">Res. Rate</th>
                    <th class="right">Rated</th>
                    <th class="right">Avg Overall</th>
                    <th class="right">Avg Service</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($byService as $row)
                    @php $resRate = $row['total'] > 0 ? round(($row['resolved']/$row['total'])*100,1) : 0; @endphp
                    <tr>
                        <td class="bold">{{ $row['name'] }}</td>
                        <td class="right">{{ $row['total'] }}</td>
                        <td class="right green">{{ $row['resolved'] }}</td>
                        <td class="right muted">{{ $resRate }}%</td>
                        <td class="right muted">{{ $row['rated_count'] }}</td>
                        <td class="right gold">{{ $row['avg_overall'] ?? '—' }}</td>
                        <td class="right gold">{{ $row['avg_service'] ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr><td colspan="7">Averages based on submitted client ratings only.</td></tr>
            </tfoot>
        </table>
    @endif

    {{-- IV. Performance by Administrator / Staff --}}
    @if ($byStaff->isNotEmpty())
        <h2 class="section-title">IV. Performance by Administrator / Staff</h2>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th class="right">Tickets Handled</th>
                    <th class="right">Resolved</th>
                    <th class="right">Res. Rate</th>
                    <th class="right">Rated</th>
                    <th class="right">Avg Overall</th>
                    <th class="right">Avg Staff Rating</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($byStaff as $row)
                    @php $resRate = $row['handled'] > 0 ? round(($row['resolved']/$row['handled'])*100,1) : 0; @endphp
                    <tr>
                        <td class="bold">{{ $row['name'] }}</td>
                        <td class="right">{{ $row['handled'] }}</td>
                        <td class="right green">{{ $row['resolved'] }}</td>
                        <td class="right muted">{{ $resRate }}%</td>
                        <td class="right muted">{{ $row['rated_count'] }}</td>
                        <td class="right gold">{{ $row['avg_overall'] ?? '—' }}</td>
                        <td class="right gold">{{ $row['avg_staff'] ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr><td colspan="7">Staff helpfulness ratings reflect only tickets where the staff was assigned and rated.</td></tr>
            </tfoot>
        </table>
    @endif

    {{-- Footer / Certification --}}
    <div class="report-footer">
        <div>
            <p>IBUConnect – Bicol University Service Ticketing System</p>
            <p>This report is system-generated and reflects data within the selected period only.</p>
        </div>
        <div class="sig-block">
            <div class="sig-line"></div>
            <p class="sig-label">Authorized Signatory</p>
        </div>
    </div>

</div>
</body>
</html>
