@extends('layouts.app')
@section('title', 'Farm Fields — Map View')

@push('styles')
{{-- Leaflet.js CSS --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.css" />
<style>
    #farmMap { height: 520px; width: 100%; border-radius: var(--radius-xl); }
    .field-card { cursor: pointer; transition: transform 0.2s, box-shadow 0.2s; }
    .field-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.12); }
    .field-stat { display: flex; align-items: center; gap: 6px; font-size: 0.8rem; color: var(--text-secondary); }
    .map-legend { background: var(--card-bg); padding: 10px 14px; border-radius: 8px; border: 1px solid var(--border-light); font-size: 0.78rem; line-height: 1.8; }
    .form-side { background: var(--card-bg); border: 1px solid var(--border-light); border-radius: var(--radius-xl); padding: 1.5rem; }
</style>
@endpush

@section('content')
<div class="page-header-section">
    <div class="page-header-row">
        <div>
            <h1 class="page-title"><i class="bi bi-map me-2 text-success"></i>Farm Fields</h1>
            <p class="page-subtitle">Draw and save your farm boundaries on the map. View all your fields in one place.</p>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- ── Map ─────────────────────────────────────────────────────────────── --}}
    <div class="col-lg-8">
        <div class="card-panel" style="padding: 0; overflow: hidden;">
            <div id="farmMap"></div>
        </div>
        <div class="d-flex align-items-center justify-content-between mt-2 px-1">
            <div class="map-legend">
                <span class="me-3"><span style="color: #22c55e;">●</span> Your Fields</span>
                <span class="me-3"><span style="color: #3b82f6;">●</span> Drawing Tool</span>
                <span class="text-muted">Zoom & click fields for details</span>
            </div>
            <span class="badge bg-success" id="fieldCountBadge">{{ $fields->count() }} Fields Saved</span>
        </div>
    </div>

    {{-- ── Add Field Form ──────────────────────────────────────────────────── --}}
    <div class="col-lg-4">
        <div class="form-side">
            <h5 class="fw-700 mb-3" id="drawInstructionTitle">
                <i class="bi bi-pencil-square me-2 text-success"></i>Save New Field
            </h5>
            <div class="alert-toast alert-toast-info mb-3" id="drawInstruction" style="position: static; opacity: 1; font-size: 0.82rem;">
                <i class="bi bi-info-circle-fill me-2"></i>Use the <strong>polygon tool</strong> on the map to draw your farm boundary, then fill in the details below.
            </div>
            <form id="farmFieldForm" method="POST" action="{{ route('farm-fields.store') }}">
                @csrf
                <input type="hidden" name="coordinates" id="fieldCoordinates" required>
                <input type="hidden" name="area_hectares" id="fieldArea">
                <input type="hidden" name="center[lat]" id="fieldCenterLat">
                <input type="hidden" name="center[lng]" id="fieldCenterLng">

                <div class="mb-3">
                    <label class="form-label fw-600" for="fieldName">Field Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="fieldName" name="name" placeholder="e.g. North Block — Wheat Field" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-600" for="fieldCropType">Crop Type</label>
                    <select class="form-select" id="fieldCropType" name="crop_type">
                        <option value="">— Select Crop —</option>
                        <option>Wheat</option><option>Rice</option><option>Maize</option>
                        <option>Cotton</option><option>Sugarcane</option><option>Soybean</option>
                        <option>Barley</option><option>Mustard</option><option>Sunflower</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-600" for="fieldSoilType">Soil Type</label>
                    <select class="form-select" id="fieldSoilType" name="soil_type">
                        <option value="">— Select Soil —</option>
                        <option value="loamy">Loamy (Best)</option>
                        <option value="silt">Silt</option>
                        <option value="black">Black (Regur)</option>
                        <option value="clay">Clay</option>
                        <option value="red">Red Laterite</option>
                        <option value="sandy">Sandy</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-600" for="fieldNotes">Notes (optional)</label>
                    <textarea class="form-control" id="fieldNotes" name="notes" rows="2" placeholder="Irrigation source, drainage info, etc."></textarea>
                </div>

                <div class="mb-3">
                    <div class="p-3 rounded" style="background: var(--bg-subtle); border: 1px solid var(--border-light);">
                        <p class="fw-600 mb-1 small">Drawn Area</p>
                        <span id="areaDisplay" class="text-muted small">Draw a polygon on the map to calculate area</span>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" id="saveFieldBtn" class="btn btn-success flex-grow-1" disabled>
                        <i class="bi bi-floppy me-2"></i>Save Field
                    </button>
                    <button type="button" id="clearDrawingBtn" class="btn btn-outline-danger" style="display:none;" onclick="clearDrawing()">
                        <i class="bi bi-eraser"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── Existing Fields List ──────────────────────────────────────────────── --}}
@if($fields->count() > 0)
<div class="section-title mt-4 mb-3">
    <h4 class="fw-700">My Farm Fields ({{ $fields->count() }})</h4>
</div>
<div class="row g-3">
    @foreach($fields as $field)
    <div class="col-sm-6 col-lg-4">
        <div class="card-panel field-card" onclick="window.location='{{ route('farm-fields.show', $field) }}'">
            <div class="d-flex align-items-start justify-content-between mb-2">
                <div>
                    <p class="fw-700 mb-0">{{ $field->name }}</p>
                    <span class="badge bg-success-subtle text-success">{{ $field->crop_type ?? 'Unknown Crop' }}</span>
                </div>
                <span class="badge bg-info-subtle text-info">{{ $field->soil_type ?? 'N/A' }}</span>
            </div>
            <div class="d-flex gap-3 mt-2">
                <div class="field-stat"><i class="bi bi-rulers text-success"></i>{{ number_format($field->area_hectares ?? 0, 2) }} ha</div>
                @if($field->center_lat)
                <div class="field-stat"><i class="bi bi-geo-alt text-info"></i>{{ round($field->center_lat, 3) }}, {{ round($field->center_lng, 3) }}</div>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>
@else
<div class="empty-state mt-4">
    <i class="bi bi-map"></i>
    <h3>No Fields Saved Yet</h3>
    <p>Use the polygon tool on the map above to draw your farm boundary and save your first field.</p>
</div>
@endif
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // ── Initialize Leaflet Map ──────────────────────────────────────────────
    const map = L.map('farmMap').setView([20.5937, 78.9629], 5); // India center

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        maxZoom: 20,
    }).addTo(map);

    // Try to center on user's GPS location
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(pos => {
            map.setView([pos.coords.latitude, pos.coords.longitude], 13);
        });
    }

    // ── Drawing Controls ───────────────────────────────────────────────────
    const drawnItems = new L.FeatureGroup();
    map.addLayer(drawnItems);

    const drawControl = new L.Control.Draw({
        draw: {
            polygon: {
                allowIntersection: false,
                shapeOptions: { color: '#22c55e', fillOpacity: 0.2 },
            },
            polyline: false, rectangle: false, circle: false,
            circlemarker: false, marker: false,
        },
        edit: { featureGroup: drawnItems }
    });
    map.addControl(drawControl);

    // ── Existing Fields GeoJSON Layer ──────────────────────────────────────
    fetch('{{ route('farm-fields.geojson') }}')
        .then(r => r.json())
        .then(data => {
            if (!data.features || data.features.length === 0) return;

            const layer = L.geoJSON(data, {
                style: { color: '#16a34a', weight: 2, fillOpacity: 0.15 },
                onEachFeature: (feature, layer) => {
                    const p = feature.properties;
                    layer.bindPopup(`
                        <strong>${p.name}</strong><br>
                        Crop: ${p.crop_type || 'N/A'}<br>
                        Soil: ${p.soil_type || 'N/A'}<br>
                        Area: ${parseFloat(p.area_hectares || 0).toFixed(2)} ha<br>
                        <a href="${p.show_url}" class="text-success fw-600">View Details →</a>
                    `);
                },
            }).addTo(map);

            map.fitBounds(layer.getBounds().pad(0.1));
        });

    // ── Handle Draw Events ─────────────────────────────────────────────────
    map.on(L.Draw.Event.CREATED, function (e) {
        drawnItems.clearLayers();
        const layer = e.layer;
        drawnItems.addLayer(layer);

        const latlngs = layer.getLatLngs()[0];
        const coords   = latlngs.map(p => [p.lat, p.lng]);
        const area_m2  = L.GeometryUtil ? L.GeometryUtil.geodesicArea(latlngs) : 0;
        const area_ha  = (area_m2 / 10000).toFixed(4);
        const center   = layer.getBounds().getCenter();

        // Fill hidden form fields
        document.getElementById('fieldCoordinates').value = JSON.stringify(coords);
        document.getElementById('fieldArea').value        = area_ha;
        document.getElementById('fieldCenterLat').value   = center.lat.toFixed(6);
        document.getElementById('fieldCenterLng').value   = center.lng.toFixed(6);

        // Update display
        document.getElementById('areaDisplay').innerHTML = `
            <span class="fw-700 text-success">${area_ha} ha</span>
            &nbsp;(${coords.length} vertices)&nbsp;&nbsp;
            Center: ${center.lat.toFixed(4)}, ${center.lng.toFixed(4)}
        `;

        document.getElementById('saveFieldBtn').disabled = false;
        document.getElementById('clearDrawingBtn').style.display = 'block';
        document.getElementById('drawInstruction').style.display = 'none';
    });

    map.on(L.Draw.Event.DELETED, function () {
        clearFormState();
    });
    
    // Custom function to clear the map drawing from the helper button
    window.clearDrawing = function() {
        drawnItems.clearLayers();
        clearFormState();
    };

    function clearFormState() {
        document.getElementById('fieldCoordinates').value = '';
        document.getElementById('saveFieldBtn').disabled  = true;
        document.getElementById('clearDrawingBtn').style.display = 'none';
        document.getElementById('areaDisplay').textContent = 'Draw a polygon on the map to calculate area';
        document.getElementById('drawInstruction').style.display = '';
    }

    // ── Form Submission via AJAX ───────────────────────────────────────────
    document.getElementById('farmFieldForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        const btn = document.getElementById('saveFieldBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Saving...';

        try {
            const coordsVal = document.getElementById('fieldCoordinates').value;
            const payload = {
                _token: document.querySelector('input[name="_token"]').value,
                name: document.getElementById('fieldName').value,
                crop_type: document.getElementById('fieldCropType').value,
                soil_type: document.getElementById('fieldSoilType').value,
                notes: document.getElementById('fieldNotes').value,
                area_hectares: document.getElementById('fieldArea').value,
                center: {
                    lat: document.getElementById('fieldCenterLat').value,
                    lng: document.getElementById('fieldCenterLng').value
                },
                coordinates: coordsVal ? JSON.parse(coordsVal) : []
            };

            const res  = await fetch(this.action, {
                method: 'POST',
                body: JSON.stringify(payload),
                headers: { 
                    'X-Requested-With': 'XMLHttpRequest', 
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
            });
            const data = await res.json();
            if (res.ok && data.success) {
                showToast('Field Saved', data.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                let errorMsg = data.message || 'Failed to save field.';
                if (data.errors) {
                    errorMsg = Object.values(data.errors).flat().join('<br>');
                }
                showToast('Error', errorMsg, 'error');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-floppy me-2"></i>Save Farm Field';
            }
        } catch (err) {
            showToast('Error', 'Network error. Please try again.', 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-floppy me-2"></i>Save Farm Field';
        }
    });
});
</script>
@endpush
