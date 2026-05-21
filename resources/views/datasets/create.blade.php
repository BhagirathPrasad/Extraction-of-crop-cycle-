@extends('layouts.app')
@section('title', 'Upload Dataset')
@section('page-title', '📤 Upload Dataset')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h2>Upload New Dataset</h2>
        <p>Upload CSV or GeoTIFF satellite data files for NDVI processing and crop cycle extraction.</p>
    </div>
    <a href="{{ route('datasets.index') }}" class="btn-outline"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<div style="max-width: 800px;">
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="bi bi-cloud-upload me-2 text-success"></i>Dataset Information</h3>
    </div>
    <div class="card-body">

        <form action="{{ route('datasets.store') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
            @csrf

            {{-- File Upload Zone --}}
            <div class="form-group">
                <label class="form-label">Data File <span class="required">*</span></label>
                <div class="upload-zone" id="uploadZone" onclick="document.getElementById('fileInput').click()">
                    <div class="upload-zone-icon"><i class="bi bi-cloud-upload-fill"></i></div>
                    <p id="uploadText">Drag & drop your file here, or click to browse</p>
                    <small>Supported: CSV, GeoTIFF (.tif/.tiff), JSON, PDF, Image · Max: 100 MB</small>
                    <input type="file" id="fileInput" name="file" accept=".csv,.tif,.tiff,.json,.txt,.pdf,.jpg,.jpeg,.png"
                           style="display:none;" required>
                </div>
                @error('file') <div class="form-error">{{ $message }}</div> @enderror
                <div id="filePreview" style="display:none; margin-top:10px;"
                     class="badge-pill badge-success" style="font-size:13px;"></div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">

                <div class="form-group">
                    <label class="form-label">Dataset Name <span class="required">*</span></label>
                    <input type="text" name="name" class="form-control"
                           value="{{ old('name') }}" placeholder="e.g. Wheat Punjab Kharif 2024" required>
                    @error('name') <div class="form-error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">File Type <span class="required">*</span></label>
                    <select name="type" class="form-control" required>
                        <option value="CSV"     {{ old('type') === 'CSV' ? 'selected' : '' }}>CSV</option>
                        <option value="GeoTIFF" {{ old('type') === 'GeoTIFF' ? 'selected' : '' }}>GeoTIFF</option>
                        <option value="JSON"    {{ old('type') === 'JSON' ? 'selected' : '' }}>JSON</option>
                        <option value="PDF"     {{ old('type') === 'PDF' ? 'selected' : '' }}>PDF</option>
                        <option value="Image"   {{ old('type') === 'Image' ? 'selected' : '' }}>Image</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Crop Type</label>
                    <select name="crop_type" class="form-control">
                        <option value="">Select crop...</option>
                        @foreach(['wheat','rice','maize','cotton','soybean','barley','millet','sugarcane','sunflower','mustard'] as $crop)
                            <option value="{{ $crop }}" {{ old('crop_type') === $crop ? 'selected' : '' }}>{{ ucfirst($crop) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Region / State</label>
                    <select name="region" class="form-control">
                        <option value="">Select region...</option>
                        @foreach(['Punjab','Haryana','Uttar Pradesh','Madhya Pradesh','Maharashtra','Gujarat','Rajasthan','Bihar','Andhra Pradesh','Karnataka','Tamil Nadu','Telangana','West Bengal','Odisha','Assam'] as $r)
                            <option value="{{ $r }}" {{ old('region') === $r ? 'selected' : '' }}>{{ $r }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Data Start Date</label>
                    <input type="date" name="data_start_date" class="form-control" value="{{ old('data_start_date') }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Data End Date</label>
                    <input type="date" name="data_end_date" class="form-control" value="{{ old('data_end_date') }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Latitude</label>
                    <input type="number" step="0.0001" name="latitude" class="form-control"
                           placeholder="e.g. 30.7333" value="{{ old('latitude') }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Longitude</label>
                    <input type="number" step="0.0001" name="longitude" class="form-control"
                           placeholder="e.g. 76.7794" value="{{ old('longitude') }}">
                </div>

            </div>

            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3"
                          placeholder="Brief description of the dataset...">{{ old('description') }}</textarea>
            </div>

            {{-- CSV Format Guide --}}
            <div class="card" style="background: var(--bg-hover); margin-bottom: 20px;">
                <div class="card-body" style="padding: 14px 18px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <p style="font-size:12px; color:var(--text-secondary); margin:0;">
                            <strong><i class="bi bi-info-circle text-info"></i> CSV Format Guide:</strong>
                            Your CSV should have headers: <code>date, ndvi</code> (required).
                            Optional: <code>evi, savi, lai, growth_stage, temperature, rainfall, humidity, soil_moisture, satellite_source</code>
                        </p>
                        <a href="{{ asset('sample_dataset.csv') }}" download class="btn-outline" style="font-size: 11px; padding: 4px 8px; flex-shrink: 0; margin-left: 12px; text-decoration: none;">
                            <i class="bi bi-download"></i> Download Sample CSV
                        </a>
                    </div>
                    <div style="margin-top:8px; font-family:monospace; font-size:11px; color:var(--text-muted); padding: 8px; background: rgba(0,0,0,0.2); border-radius: 4px; overflow-x: auto; white-space: nowrap;">
                        date,ndvi,evi,savi,lai,growth_stage,temperature,rainfall,humidity,soil_moisture,satellite_source<br>
                        2024-06-15,0.12,0.10,0.11,0.5,pre_sowing,32,0,45,30,Sentinel-2<br>
                        2024-06-25,0.18,0.15,0.16,0.8,germination,31,12,60,45,Sentinel-2
                    </div>
                </div>
            </div>

            {{-- Upload progress bar --}}
            <div id="uploadProgress" style="display:none; margin-bottom:16px;">
                <div style="background:var(--bg-hover); border-radius:999px; height:8px;">
                    <div id="progressBar" class="ndvi-fill ndvi-fill-good" style="width:0%; height:100%; border-radius:999px; transition: width .3s;"></div>
                </div>
                <p style="font-size:12px; color:var(--text-muted); margin-top:6px;">
                    <i class="bi bi-gear-fill spin"></i> Uploading & queuing processing...
                </p>
            </div>

            <button type="submit" class="btn-primary-green" id="submitBtn" style="width:100%; justify-content:center;">
                <i class="bi bi-cloud-upload-fill"></i> Upload & Process Dataset
            </button>
        </form>

    </div>
</div>
</div>
@endsection

@push('styles')
<style>
.spin { animation: spin 1s linear infinite; display: inline-block; }
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>
@endpush

@push('scripts')
<script>
const zone  = document.getElementById('uploadZone');
const input = document.getElementById('fileInput');
const text  = document.getElementById('uploadText');
const preview = document.getElementById('filePreview');

// Drag & Drop
zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('drag-over'); });
zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
zone.addEventListener('drop', e => {
    e.preventDefault(); zone.classList.remove('drag-over');
    input.files = e.dataTransfer.files;
    updatePreview();
});

input.addEventListener('change', updatePreview);

function updatePreview() {
    if (input.files[0]) {
        const f = input.files[0];
        const mb = (f.size / 1048576).toFixed(2);
        text.textContent = '✓ ' + f.name;
        preview.style.display = 'inline-flex';
        preview.textContent = f.name + ' (' + mb + ' MB)';
        // Auto-fill name if empty
        const nameInput = document.querySelector('[name="name"]');
        if (!nameInput.value) nameInput.value = f.name.replace(/\.[^/.]+$/, '').replace(/_/g, ' ');
    }
}

// Upload progress simulation
document.getElementById('uploadForm').addEventListener('submit', function(e) {
    document.getElementById('uploadProgress').style.display = 'block';
    
    // Defer disabling the button so the form can submit
    setTimeout(() => {
        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Uploading...';
    }, 10);

    let w = 0;
    const iv = setInterval(() => {
        w = Math.min(w + Math.random() * 15, 90);
        document.getElementById('progressBar').style.width = w + '%';
        if (w >= 90) clearInterval(iv);
    }, 200);
});
</script>
@endpush
