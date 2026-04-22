@extends('layouts.app')
@section('title', ucfirst($cropCycle->crop_type) . ' Cycle — Details')
@section('page-title', '🌾 Crop Cycle Details')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h2>{{ ucfirst($cropCycle->crop_type) }} — {{ $cropCycle->region }}</h2>
        <p>{{ $cropCycle->season }} {{ $cropCycle->season_year }} · {{ $cropCycle->variety ?? 'Standard variety' }}</p>
    </div>
    <div class="page-header-actions">
        <a href="{{ route('crop-cycles.edit', $cropCycle) }}" class="btn-outline"><i class="bi bi-pencil"></i> Edit</a>
        <a href="{{ route('crop-cycles.index') }}" class="btn-outline"><i class="bi bi-arrow-left"></i> Back</a>
    </div>
</div>

{{-- Parameter Cards --}}
<div class="grid-cols-4" style="margin-bottom:24px;">
    <div class="stat-card stat-card-green">
        <div class="stat-icon stat-icon-green"><i class="bi bi-calendar-plus"></i></div>
        <div class="stat-info">
            <div class="stat-value" style="font-size:16px;">{{ $cropCycle->sowing_date?->format('d M Y') ?? 'N/A' }}</div>
            <div class="stat-label">Sowing Date</div>
        </div>
    </div>
    <div class="stat-card stat-card-teal">
        <div class="stat-icon stat-icon-teal"><i class="bi bi-flower1"></i></div>
        <div class="stat-info">
            <div class="stat-value" style="font-size:16px;">{{ $cropCycle->peak_growth_date?->format('d M Y') ?? 'N/A' }}</div>
            <div class="stat-label">Peak Growth</div>
        </div>
    </div>
    <div class="stat-card stat-card-amber">
        <div class="stat-icon stat-icon-amber"><i class="bi bi-calendar-check"></i></div>
        <div class="stat-info">
            <div class="stat-value" style="font-size:16px;">{{ $cropCycle->harvest_date?->format('d M Y') ?? 'N/A' }}</div>
            <div class="stat-label">Harvest Date</div>
        </div>
    </div>
    <div class="stat-card {{ $cropCycle->yield_badge_class === 'badge-success' ? 'stat-card-green' : ($cropCycle->yield_badge_class === 'badge-warning' ? 'stat-card-amber' : 'stat-card-rose') }}">
        <div class="stat-icon {{$cropCycle->yield_badge_class === 'badge-success' ? 'stat-icon-green' : 'stat-icon-amber'}}"><i class="bi bi-bar-chart-fill"></i></div>
        <div class="stat-info">
            <div class="stat-value">{{ number_format($cropCycle->yield_prediction ?? 0, 0) }}</div>
            <div class="stat-label">Yield Prediction (kg/ha)</div>
            @if($cropCycle->yield_category)
                <div class="stat-change {{ $cropCycle->yield_category === 'high' ? 'up' : '' }}">
                    <span class="badge-pill {{ $cropCycle->yield_badge_class }}">{{ ucfirst($cropCycle->yield_category) }}</span>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="grid-cols-2" style="margin-bottom:24px;">

    {{-- NDVI Chart --}}
    <div class="card" style="grid-column: span 2;">
        <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
            <h3 class="card-title"><i class="bi bi-activity me-2 text-success"></i>NDVI Time Series</h3>
            <div style="display:flex; gap:10px; align-items:center;">
                <span class="badge-pill badge-success">NDVI Max: {{ $cropCycle->ndvi_max }}</span>
                <span class="badge-pill badge-secondary">NDVI Mean: {{ $cropCycle->ndvi_mean }}</span>
                <span>{{ $cropCycle->ndviRecords->count() }} observations</span>
            </div>
        </div>
        <div class="card-body">
            <canvas id="ndviChart" height="280"></canvas>
        </div>
    </div>

</div>

<div class="grid-cols-2" style="margin-bottom:24px;">

    {{-- Growth Stages --}}
    <div class="card">
        <div class="card-header"><h3 class="card-title"><i class="bi bi-diagram-3 me-2 text-primary"></i>Growth Stage Timeline</h3></div>
        <div class="card-body">
            @php
                $stages = [
                    ['label' => 'Sowing',         'date' => $cropCycle->sowing_date,       'icon' => 'bi-seed',           'color' => '#94a3b8'],
                    ['label' => 'Emergence',       'date' => $cropCycle->emergence_date,    'icon' => 'bi-arrow-up-circle','color' => '#22c55e'],
                    ['label' => 'Tillering',       'date' => $cropCycle->tillering_date,    'icon' => 'bi-tree',           'color' => '#16a34a'],
                    ['label' => 'Heading / Jointing','date'=> $cropCycle->heading_date,     'icon' => 'bi-flower1',        'color' => '#0d9488'],
                    ['label' => 'Peak Growth',     'date' => $cropCycle->peak_growth_date,  'icon' => 'bi-star-fill',      'color' => '#f59e0b'],
                    ['label' => 'Maturity',        'date' => $cropCycle->maturity_date,     'icon' => 'bi-check-circle',   'color' => '#d97706'],
                    ['label' => 'Harvest',         'date' => $cropCycle->harvest_date,      'icon' => 'bi-scissors',       'color' => '#e11d48'],
                ];
            @endphp
            <div style="position:relative; padding-left:28px;">
                @foreach($stages as $stage)
                <div style="display:flex; gap:12px; align-items:flex-start; margin-bottom:14px; position:relative;">
                    <div style="position:absolute; left:-28px; width:22px; height:22px; background:{{ $stage['color'] }}; border-radius:50%; display:flex; align-items:center; justify-content:center; color:white; font-size:10px; flex-shrink:0;">
                        <i class="bi {{ $stage['icon'] }}"></i>
                    </div>
                    <div>
                        <p style="font-weight:600; font-size:13px; color:var(--text-primary); margin:0;">{{ $stage['label'] }}</p>
                        <p style="font-size:12px; color:var(--text-muted); margin:0;">
                            {{ $stage['date'] ? $stage['date']->format('M d, Y') : 'Not recorded' }}
                        </p>
                    </div>
                </div>
                @if(!$loop->last)
                <div style="position:absolute; left:-18px; top:22px; width:2px; height:14px; background:var(--border-color);"></div>
                @endif
                @endforeach
            </div>
            @if($cropCycle->growing_days)
            <div style="background:var(--bg-hover); border-radius:10px; padding:12px; margin-top:10px; text-align:center;">
                <span style="font-size:20px; font-weight:700; color:var(--brand-green);">{{ $cropCycle->growing_days }}</span>
                <span style="font-size:12px; color:var(--text-muted); margin-left:6px;">Total Growing Days</span>
            </div>
            @endif
        </div>
    </div>

    {{-- NDVI Stats + Irrigation --}}
    <div class="card">
        <div class="card-header"><h3 class="card-title"><i class="bi bi-bar-chart me-2 text-info"></i>NDVI Statistics & Irrigation</h3></div>
        <div class="card-body">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:20px;">
                @foreach([
                    ['label' => 'NDVI Max',      'value' => $cropCycle->ndvi_max,        'color' => 'text-success'],
                    ['label' => 'NDVI Min',      'value' => $cropCycle->ndvi_min,        'color' => 'text-danger'],
                    ['label' => 'NDVI Mean',     'value' => $cropCycle->ndvi_mean,       'color' => 'text-info'],
                    ['label' => 'NDVI Range',    'value' => $cropCycle->ndvi_range,      'color' => 'text-warning'],
                    ['label' => 'At Sowing',     'value' => $cropCycle->ndvi_at_sowing,  'color' => ''],
                    ['label' => 'At Harvest',    'value' => $cropCycle->ndvi_at_harvest, 'color' => ''],
                ] as $stat)
                <div style="background:var(--bg-hover); border-radius:10px; padding:12px; text-align:center;">
                    <div style="font-size:18px; font-weight:700; color:var(--text-primary);">
                        {{ $stat['value'] ?? '—' }}
                    </div>
                    <div style="font-size:11px; color:var(--text-muted); text-transform:uppercase; letter-spacing:.04em;">{{ $stat['label'] }}</div>
                </div>
                @endforeach
            </div>

            @if($cropCycle->irrigation_suggestions)
            <h4 style="font-size:13px; font-weight:600; margin-bottom:10px;"><i class="bi bi-droplet-fill text-info"></i> Irrigation Suggestions</h4>
            @foreach($cropCycle->irrigation_suggestions as $irr)
            <div style="background:rgba(13,148,136,.08); border:1px solid rgba(13,148,136,.2); border-radius:8px; padding:10px 12px; margin-bottom:8px;">
                <div style="font-weight:600; font-size:12px; color:var(--accent-teal);">{{ $irr['date'] ?? '' }} — {{ ucfirst($irr['stage'] ?? '') }}</div>
                <div style="font-size:12px; color:var(--text-secondary);">{{ $irr['action'] ?? '' }}</div>
                @if(isset($irr['amount'])) <span class="badge-pill badge-info">{{ $irr['amount'] }}</span> @endif
            </div>
            @endforeach
            @endif
        </div>
    </div>

</div>

{{-- NDVI Records Table --}}
@if($cropCycle->ndviRecords->count() > 0)
<div class="card">
    <div class="card-header"><h3 class="card-title"><i class="bi bi-table me-2"></i>NDVI Observation Records ({{ $cropCycle->ndviRecords->count() }})</h3></div>
    <div class="table-wrapper" style="border:none; border-radius:0 0 14px 14px;">
        <table class="data-table">
            <thead><tr>
                <th>Date</th><th>NDVI</th><th>EVI</th><th>Growth Stage</th>
                <th>Temperature</th><th>Rainfall</th><th>Humidity</th><th>Satellite</th>
            </tr></thead>
            <tbody>
                @foreach($cropCycle->ndviRecords->take(50) as $r)
                <tr>
                    <td>{{ $r->observation_date->format('M d, Y') }}</td>
                    <td>
                        <div style="display:flex; align-items:center; gap:6px;">
                            <div class="ndvi-bar" style="width:50px;">
                                <div class="ndvi-fill {{ $r->ndvi_value > 0.6 ? 'ndvi-fill-good' : ($r->ndvi_value > 0.3 ? 'ndvi-fill-medium' : 'ndvi-fill-low') }}"
                                     style="width:{{ round($r->ndvi_value * 100) }}%;"></div>
                            </div>
                            <span style="font-size:12px; font-weight:600;">{{ $r->ndvi_value }}</span>
                        </div>
                    </td>
                    <td>{{ $r->evi_value ?? '—' }}</td>
                    <td><span class="badge-pill badge-secondary">{{ $r->growth_stage_label }}</span></td>
                    <td>{{ $r->temperature ? $r->temperature . '°C' : '—' }}</td>
                    <td>{{ $r->rainfall ? $r->rainfall . ' mm' : '—' }}</td>
                    <td>{{ $r->humidity ? $r->humidity . '%' : '—' }}</td>
                    <td style="font-size:11px; color:var(--text-muted);">{{ $r->satellite_source ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
const isDark  = document.documentElement.getAttribute('data-theme') === 'dark';
const textClr = isDark ? '#94a3b8' : '#64748b';
const gridClr = isDark ? 'rgba(255,255,255,.06)' : 'rgba(0,0,0,.06)';

const dates  = @json($ndviDates);
const ndvi   = @json($ndviValues);
const evi    = @json($eviValues);

// Key stage annotation lines
const stages = {
    sowing:   '{{ $cropCycle->sowing_date?->toDateString() }}',
    peak:     '{{ $cropCycle->peak_growth_date?->toDateString() }}',
    harvest:  '{{ $cropCycle->harvest_date?->toDateString() }}',
};

new Chart(document.getElementById('ndviChart'), {
    type: 'line',
    data: {
        labels: dates,
        datasets: [
            {
                label: 'NDVI',
                data:  ndvi,
                borderColor: '#22c55e',
                backgroundColor: 'rgba(34,197,94,.1)',
                fill: true, tension: 0.4,
                pointRadius: dates.length < 30 ? 4 : 2,
                borderWidth: 2,
            },
            {
                label: 'EVI',
                data:  evi.filter(v => v !== null),
                borderColor: '#3b82f6',
                backgroundColor: 'transparent',
                fill: false, tension: 0.4,
                pointRadius: 2, borderWidth: 1.5,
                borderDash: [5, 3],
            }
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: {
            legend: { display: true, labels: { color: textClr, usePointStyle: true } },
            tooltip: { mode: 'index', intersect: false }
        },
        scales: {
            x: { grid: { color: gridClr }, ticks: { color: textClr, maxTicksLimit: 10 } },
            y: { grid: { color: gridClr }, ticks: { color: textClr }, min: 0, max: 1,
                 title: { display: true, text: 'NDVI / EVI Value', color: textClr } }
        },
        interaction: { intersect: false, mode: 'index' }
    }
});
</script>
@endpush
