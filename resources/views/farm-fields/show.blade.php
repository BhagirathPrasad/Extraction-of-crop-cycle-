@extends('layouts.app')
@section('title', $farmField->name . ' — Farm Field')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #fieldDetailMap { height: 380px; width: 100%; border-radius: var(--radius-xl); }
    .detail-stat-card { background: var(--card-bg); border: 1px solid var(--border-light); border-radius: var(--radius-lg); padding: 1rem 1.25rem; }
    .detail-stat-card .stat-label { font-size: 0.75rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px; }
    .detail-stat-card .stat-value { font-size: 1.35rem; font-weight: 800; color: var(--text-primary); }
    .weather-card { background: linear-gradient(135deg, #0ea5e9 0%, #2563eb 100%); color: #fff; border-radius: var(--radius-xl); padding: 1.5rem; }
    .weather-card .weather-temp { font-size: 2.5rem; font-weight: 800; }
    .weather-daily { display: flex; gap: 8px; overflow-x: auto; padding: 8px 0; }
    .weather-day { background: rgba(255,255,255,0.12); border-radius: 10px; padding: 8px 12px; text-align: center; min-width: 72px; font-size: 0.78rem; flex-shrink: 0; }
    .weather-day .day-label { font-weight: 700; margin-bottom: 2px; }
</style>
@endpush

@section('content')
<div class="page-header-section">
    <div class="page-header-row">
        <div>
            <h1 class="page-title"><i class="bi bi-geo-alt-fill me-2 text-success"></i>{{ $farmField->name }}</h1>
            <p class="page-subtitle">
                <span class="badge bg-success-subtle text-success me-2">{{ $farmField->crop_type ?? 'Unknown Crop' }}</span>
                <span class="badge bg-info-subtle text-info me-2">{{ ucfirst($farmField->soil_type ?? 'N/A') }} Soil</span>
                @if($farmField->area_hectares)
                    <span class="text-muted">{{ number_format($farmField->area_hectares, 2) }} hectares</span>
                @endif
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('farm-fields.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Back to Fields
            </a>
            <form action="{{ route('farm-fields.destroy', $farmField) }}" method="POST" onsubmit="return confirm('Delete this field?');">
                @csrf @method('DELETE')
                <button class="btn btn-outline-danger"><i class="bi bi-trash me-1"></i>Delete</button>
            </form>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Map --}}
    <div class="col-lg-7">
        <div class="card-panel" style="padding: 0; overflow: hidden;">
            <div id="fieldDetailMap"></div>
        </div>
    </div>

    {{-- Weather Widget --}}
    <div class="col-lg-5">
        <div class="weather-card" id="weatherWidget">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <p class="mb-0 fw-600 opacity-75">Live Weather</p>
                    <h4 class="fw-800 mb-0">{{ $farmField->name }}</h4>
                </div>
                <i class="bi bi-cloud-sun-fill" style="font-size: 2.5rem; opacity: 0.7;"></i>
            </div>
            <div class="d-flex align-items-end gap-3 mb-3">
                <div class="weather-temp" id="weatherTemp">--°C</div>
                <div>
                    <div class="opacity-75 small" id="weatherLabel">Loading...</div>
                    <div class="opacity-75 small"><i class="bi bi-droplet me-1"></i><span id="weatherHumidity">--</span>% Humidity</div>
                    <div class="opacity-75 small"><i class="bi bi-wind me-1"></i><span id="weatherWind">--</span> km/h</div>
                </div>
            </div>
            <div class="weather-daily" id="weatherDailyForecast">
                <div class="weather-day"><div class="day-label">--</div><div>--°</div></div>
            </div>
            <div id="weatherAlerts" class="mt-3"></div>
        </div>
    </div>
</div>

{{-- Stats Row --}}
<div class="row g-3 mt-1">
    <div class="col-6 col-md-3">
        <div class="detail-stat-card">
            <div class="stat-label">Area</div>
            <div class="stat-value text-success">{{ number_format($farmField->area_hectares ?? 0, 2) }} <small class="fw-500 text-muted">ha</small></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="detail-stat-card">
            <div class="stat-label">Coordinates</div>
            <div class="stat-value text-info" style="font-size: 1rem;">{{ round($farmField->center_lat ?? 0, 4) }}, {{ round($farmField->center_lng ?? 0, 4) }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="detail-stat-card">
            <div class="stat-label">Crop Cycles</div>
            <div class="stat-value text-warning">{{ $farmField->cropCycles->count() }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="detail-stat-card">
            <div class="stat-label">Status</div>
            <div class="stat-value text-success">{{ $farmField->is_active ? 'Active' : 'Inactive' }}</div>
        </div>
    </div>
</div>

@if($farmField->notes)
<div class="card-panel mt-3">
    <h6 class="fw-700 mb-2"><i class="bi bi-sticky me-2 text-warning"></i>Notes</h6>
    <p class="text-muted mb-0">{{ $farmField->notes }}</p>
</div>
@endif

{{-- Crop Cycles for This Field --}}
@if($farmField->cropCycles->count() > 0)
<div class="section-title mt-4 mb-3">
    <h4 class="fw-700">Crop Cycles on This Field ({{ $farmField->cropCycles->count() }})</h4>
</div>
<div class="table-responsive">
    <table class="data-table">
        <thead>
            <tr>
                <th>Crop</th><th>Season</th><th>NDVI Peak</th><th>Yield (kg/ha)</th><th>SOS</th><th>EOS</th><th>GDD</th>
            </tr>
        </thead>
        <tbody>
            @foreach($farmField->cropCycles as $cycle)
            <tr onclick="window.location='{{ route('crop-cycles.show', $cycle) }}'" style="cursor: pointer;">
                <td class="fw-600">{{ $cycle->crop_type }}</td>
                <td>{{ $cycle->season }} {{ $cycle->season_year }}</td>
                <td><span class="badge bg-success-subtle text-success">{{ $cycle->ndvi_max }}</span></td>
                <td>{{ number_format($cycle->yield_prediction ?? 0) }}</td>
                <td>{{ $cycle->sos_date ?? '—' }}</td>
                <td>{{ $cycle->eos_date ?? '—' }}</td>
                <td>{{ $cycle->gdd_total ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const coords = @json($farmField->coordinates ?? []);
    const center = @json($farmField->center ?? ['lat' => 20.5937, 'lng' => 78.9629]);

    const map = L.map('fieldDetailMap').setView([center.lat, center.lng], 14);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap', maxZoom: 20,
    }).addTo(map);

    if (coords.length > 0) {
        const polygon = L.polygon(coords, {
            color: '#16a34a', weight: 3, fillOpacity: 0.18,
        }).addTo(map).bindPopup('<strong>{{ $farmField->name }}</strong>');
        map.fitBounds(polygon.getBounds().pad(0.15));
    }

    // Fetch weather for this field
    @if($farmField->center_lat && $farmField->center_lng)
    fetch('{{ route("weather.field", $farmField) }}', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
    })
    .then(r => r.json())
    .then(w => {
        if (w.error) {
            document.getElementById('weatherTemp').textContent = 'N/A';
            document.getElementById('weatherLabel').textContent = w.error;
            return;
        }
        const c = w.current || {};
        document.getElementById('weatherTemp').textContent = (c.temperature ?? '--') + '°C';
        document.getElementById('weatherLabel').textContent = c.weather_label ?? 'Unknown';
        document.getElementById('weatherHumidity').textContent = c.humidity ?? '--';
        document.getElementById('weatherWind').textContent = c.wind_speed ?? '--';

        // Daily forecast
        const daily = w.daily || [];
        const container = document.getElementById('weatherDailyForecast');
        container.innerHTML = '';
        const dayNames = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
        daily.forEach(d => {
            const dt = new Date(d.date);
            container.innerHTML += `
                <div class="weather-day">
                    <div class="day-label">${dayNames[dt.getDay()]}</div>
                    <div>${d.temp_max ?? '--'}°</div>
                    <div style="font-size:0.7rem;opacity:0.7;">${d.temp_min ?? '--'}°</div>
                    <div style="font-size:0.68rem;margin-top:2px;">💧${d.precipitation ?? 0}mm</div>
                </div>
            `;
        });

        // Alerts
        const alerts = w.alerts || [];
        const alertsEl = document.getElementById('weatherAlerts');
        alerts.forEach(a => {
            alertsEl.innerHTML += `
                <div class="mt-2 p-2 rounded" style="background:rgba(255,255,255,0.15);font-size:0.82rem;">
                    <i class="bi bi-${a.icon} me-1"></i><strong>${a.title}</strong><br>
                    <span style="opacity:0.85;">${a.message}</span>
                </div>
            `;
        });
    })
    .catch(err => {
        document.getElementById('weatherLabel').textContent = 'Offline';
    });
    @endif
});
</script>
@endpush
