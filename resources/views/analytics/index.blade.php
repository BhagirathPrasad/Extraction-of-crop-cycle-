@extends('layouts.app')
@section('title', 'Analytics & AI')
@section('page-title', '📊 Analytics & AI Insights')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h2>Crop Intelligence Analytics</h2>
        <p>AI-powered insights, yield predictions, and seasonal NDVI analysis.</p>
    </div>
</div>

{{-- AI Yield Predictor --}}
<div class="card" style="margin-bottom:24px; border:1px solid var(--brand-green); border-left:4px solid var(--brand-green);">
    <div class="card-header" style="background:linear-gradient(135deg, rgba(22,163,74,.08), transparent);">
        <h3 class="card-title"><i class="bi bi-robot me-2 text-success"></i>🤖 AI Yield Predictor</h3>
    </div>
    <div class="card-body">
        <div style="display:grid; grid-template-columns:1fr 1fr auto; gap:16px; align-items:end;">
            <div class="form-group" style="margin:0;">
                <label class="form-label">Crop Type</label>
                <select id="aiCropType" class="form-control">
                    @foreach(['wheat','rice','maize','cotton','soybean','barley','sugarcane'] as $c)
                        <option value="{{ $c }}">{{ ucfirst($c) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin:0;">
                <label class="form-label">NDVI Peak Value (0–1)</label>
                <input type="range" id="ndviSlider" min="0" max="1" step="0.01" value="0.75"
                       style="width:100%; accent-color:var(--brand-green);">
                <div style="display:flex; justify-content:space-between; font-size:11px; color:var(--text-muted);">
                    <span>0</span>
                    <span id="ndviSliderVal" style="font-weight:700; color:var(--brand-green);">0.75</span>
                    <span>1.0</span>
                </div>
            </div>
            <button onclick="runYieldPredictor()" class="btn-primary-green">
                <i class="bi bi-cpu-fill"></i> Predict Yield
            </button>
        </div>

        {{-- Result Card --}}
        <div id="aiResult" style="display:none; margin-top:20px;">
            <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:16px;">
                <div class="stat-card stat-card-green" style="background:var(--bg-hover); border:1px solid var(--border-color);">
                    <div class="stat-icon stat-icon-green"><i class="bi bi-graph-up"></i></div>
                    <div class="stat-info">
                        <div class="stat-value" id="resultYield">—</div>
                        <div class="stat-label">Predicted Yield (kg/ha)</div>
                    </div>
                </div>
                <div class="stat-card stat-card-teal">
                    <div class="stat-icon stat-icon-teal"><i class="bi bi-award-fill"></i></div>
                    <div class="stat-info">
                        <div class="stat-value" id="resultCategory" style="font-size:16px;">—</div>
                        <div class="stat-label">Yield Category</div>
                    </div>
                </div>
                <div class="stat-card stat-card-blue">
                    <div class="stat-icon stat-icon-blue"><i class="bi bi-droplet-fill"></i></div>
                    <div class="stat-info">
                        <div class="stat-value" style="font-size:13px; line-height:1.3;" id="resultIrrigation">—</div>
                        <div class="stat-label">Irrigation Advice</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="grid-cols-2" style="margin-bottom:24px;">

    {{-- Yield by Crop --}}
    <div class="card">
        <div class="card-header"><h3 class="card-title"><i class="bi bi-bar-chart-fill me-2 text-warning"></i>Average Yield by Crop Type</h3></div>
        <div class="card-body"><canvas id="yieldByCropChart" height="260"></canvas></div>
    </div>

    {{-- NDVI by Season --}}
    <div class="card">
        <div class="card-header"><h3 class="card-title"><i class="bi bi-activity me-2 text-success"></i>Peak NDVI by Season</h3></div>
        <div class="card-body"><canvas id="ndviSeasonChart" height="260"></canvas></div>
    </div>

</div>

<div class="grid-cols-2" style="margin-bottom:24px;">

    {{-- Growing Duration --}}
    <div class="card">
        <div class="card-header"><h3 class="card-title"><i class="bi bi-calendar-range me-2 text-info"></i>Average Growing Duration (Days)</h3></div>
        <div class="card-body">
            @forelse($growingDays as $gd)
            <div style="display:flex; align-items:center; gap:12px; margin-bottom:14px;">
                <span style="width:100px; font-weight:600; font-size:13px;">{{ ucfirst($gd['crop_type']) }}</span>
                <div class="ndvi-bar" style="flex:1; height:12px;">
                    <div class="ndvi-fill ndvi-fill-good"
                         style="width:{{ min(100, ($gd['avg_days'] ?? 0) / 2) }}%; height:100%;"></div>
                </div>
                <span style="width:60px; text-align:right; font-weight:600; font-size:13px;">{{ round($gd['avg_days'] ?? 0) }} d</span>
            </div>
            @empty
                <div class="empty-state" style="padding:30px 0;"><p>No data yet.</p></div>
            @endforelse
        </div>
    </div>

    {{-- Prediction Accuracy --}}
    <div class="card">
        <div class="card-header"><h3 class="card-title"><i class="bi bi-bullseye me-2 text-danger"></i>Yield Prediction vs Actual</h3></div>
        <div class="card-body">
            @if($accuracyData->count() > 0)
                <canvas id="accuracyChart" height="240"></canvas>
            @else
                <div class="empty-state" style="padding:30px 0;">
                    <div class="empty-state-icon"><i class="bi bi-bar-chart"></i></div>
                    <p>No actual yield data recorded yet.</p>
                </div>
            @endif
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
const isDark  = document.documentElement.getAttribute('data-theme') === 'dark';
const textClr = isDark ? '#94a3b8' : '#64748b';
const gridClr = isDark ? 'rgba(255,255,255,.06)' : 'rgba(0,0,0,.06)';

// NDVI Range slider
document.getElementById('ndviSlider').addEventListener('input', function() {
    document.getElementById('ndviSliderVal').textContent = parseFloat(this.value).toFixed(2);
});

// AI Predictor
function runYieldPredictor() {
    const cropType = document.getElementById('aiCropType').value;
    const ndviPeak = document.getElementById('ndviSlider').value;

    fetch('{{ route('analytics.predict-yield') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ crop_type: cropType, ndvi_peak: ndviPeak })
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('resultYield').textContent    = data.yield_prediction.toLocaleString();
        document.getElementById('resultCategory').textContent = data.yield_category.charAt(0).toUpperCase() + data.yield_category.slice(1);
        document.getElementById('resultIrrigation').textContent = data.irrigation_advice;
        document.getElementById('aiResult').style.display = 'block';
    });
}

// Yield by crop bar chart
const yieldByCropLabels = @json($yieldByCrop->pluck('crop_type')->map(fn($v) => ucfirst($v)));
const yieldByCropData   = @json($yieldByCrop->pluck('avg_yield')->map(fn($v) => round($v, 0)));

new Chart(document.getElementById('yieldByCropChart'), {
    type: 'bar',
    data: {
        labels: yieldByCropLabels,
        datasets: [{
            label: 'Avg Yield (kg/ha)',
            data: yieldByCropData,
            backgroundColor: ['#22c55e','#3b82f6','#f59e0b','#8b5cf6','#e11d48','#0d9488','#ea580c'],
            borderRadius: 8,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { 
            x: { grid: { color: gridClr }, ticks: { color: textClr } }, 
            y: { grid: { color: gridClr }, ticks: { color: textClr }, beginAtZero: true } 
        }
    }
});

// NDVI by Season bar chart
const ndviSeasonLabels = @json($ndviBySeaon->pluck('season'));
const ndviSeasonData   = @json($ndviBySeaon->pluck('avg_peak_ndvi')->map(fn($v) => round($v, 3)));

new Chart(document.getElementById('ndviSeasonChart'), {
    type: 'bar',
    data: {
        labels: ndviSeasonLabels.length ? ndviSeasonLabels : ['Kharif','Rabi','Zaid'],
        datasets: [{
            label: 'Peak NDVI',
            data: ndviSeasonData.length ? ndviSeasonData : [0.72, 0.68, 0.55],
            backgroundColor: ['#22c55e', '#3b82f6', '#f59e0b'],
            borderRadius: 8,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { 
            x: { grid: { color: gridClr }, ticks: { color: textClr } }, 
            y: { grid: { color: gridClr }, ticks: { color: textClr }, beginAtZero: true, min: 0, max: 1 } 
        }
    }
});

// Accuracy scatter (if data)
@if($accuracyData->count() > 0)
const accData = @json($accuracyData->map(fn($r) => ['x' => $r->yield_prediction, 'y' => $r->actual_yield, 'crop' => $r->crop_type]));

new Chart(document.getElementById('accuracyChart'), {
    type: 'scatter',
    data: {
        datasets: [{
            label: 'Predicted vs Actual',
            data: accData,
            backgroundColor: 'rgba(22,163,74,.6)',
            borderColor: '#16a34a',
            pointRadius: 7,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false }, tooltip: {
            callbacks: { label: ctx => `${ctx.raw.crop}: P=${ctx.raw.x} A=${ctx.raw.y}` }
        }},
        scales: {
            x: { grid: { color: gridClr }, ticks: { color: textClr }, title: { display: true, text: 'Predicted (kg/ha)', color: textClr } },
            y: { grid: { color: gridClr }, ticks: { color: textClr }, title: { display: true, text: 'Actual (kg/ha)', color: textClr } }
        }
    }
});
@endif
</script>
@endpush
