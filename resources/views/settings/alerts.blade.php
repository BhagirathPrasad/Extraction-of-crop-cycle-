@extends('layouts.app')
@section('title', 'Alert Preferences')

@section('content')
<div class="page-header-section">
    <div class="page-header-row">
        <div>
            <h1 class="page-title"><i class="bi bi-bell-fill me-2 text-warning"></i>Alert Preferences</h1>
            <p class="page-subtitle">Configure crop health monitoring alerts. Receive email notifications when NDVI drops below your threshold or weather events require attention.</p>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card-panel">
            <h5 class="fw-700 mb-3"><i class="bi bi-sliders me-2 text-success"></i>Alert Configuration</h5>

            <form action="{{ route('settings.alerts.update') }}" method="POST">
                @csrf

                {{-- NDVI Threshold --}}
                <div class="mb-4">
                    <label class="form-label fw-600" for="ndvi_alert_threshold">
                        NDVI Alert Threshold
                        <span class="text-muted fw-400 ms-1">(0.1 – 0.9)</span>
                    </label>
                    <p class="text-muted small mb-2">You'll be alerted when any crop cycle's latest NDVI drops below this value.</p>
                    <div class="d-flex align-items-center gap-3">
                        <input type="range" class="form-range" id="ndvi_alert_threshold" name="ndvi_alert_threshold"
                               min="0.1" max="0.9" step="0.05"
                               value="{{ old('ndvi_alert_threshold', $user->ndvi_alert_threshold ?? 0.30) }}"
                               oninput="document.getElementById('ndviThresholdDisplay').textContent = this.value">
                        <span class="badge bg-success fs-6 px-3 py-2" id="ndviThresholdDisplay" style="min-width: 52px;">
                            {{ number_format($user->ndvi_alert_threshold ?? 0.30, 2) }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between mt-1 small text-muted">
                        <span>0.10 (Very sensitive)</span>
                        <span>0.50 (Moderate)</span>
                        <span>0.90 (Rare alerts)</span>
                    </div>
                    @error('ndvi_alert_threshold')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <hr class="my-4">

                {{-- Email Alerts Toggle --}}
                <div class="mb-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="alert_email_enabled" name="alert_email_enabled" value="1"
                               {{ ($user->alert_email_enabled ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label fw-600" for="alert_email_enabled">
                            <i class="bi bi-envelope me-1 text-info"></i>Email Alerts
                        </label>
                    </div>
                    <p class="text-muted small ms-4">Receive crop health alerts at <strong>{{ $user->email }}</strong></p>
                </div>

                {{-- SMS Alerts Toggle --}}
                <div class="mb-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="alert_sms_enabled" name="alert_sms_enabled" value="1"
                               {{ ($user->alert_sms_enabled ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label fw-600" for="alert_sms_enabled">
                            <i class="bi bi-phone me-1 text-success"></i>SMS Alerts
                            <span class="badge bg-secondary-subtle text-secondary ms-1">Coming Soon</span>
                        </label>
                    </div>
                    <p class="text-muted small ms-4">SMS alerts require a verified phone number (Twilio/MSG91 integration).</p>
                </div>

                {{-- Phone Number --}}
                <div class="mb-4">
                    <label class="form-label fw-600" for="phone_number">Phone Number (for SMS)</label>
                    <input type="tel" class="form-control" id="phone_number" name="phone_number"
                           value="{{ old('phone_number', $user->phone ?? '') }}" placeholder="+91 9876543210">
                    @error('phone_number')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" class="btn btn-success">
                    <i class="bi bi-floppy me-2"></i>Save Alert Preferences
                </button>
            </form>
        </div>
    </div>

    {{-- Info Panel --}}
    <div class="col-lg-5">
        <div class="card-panel" style="background: linear-gradient(135deg, rgba(22,163,74,0.05), rgba(37,99,235,0.05));">
            <h6 class="fw-700 mb-3"><i class="bi bi-info-circle me-2 text-info"></i>How Alerts Work</h6>
            <div class="mb-3">
                <p class="fw-600 mb-1">🌿 Low NDVI Alert</p>
                <p class="text-muted small mb-0">Triggered when your crop's latest NDVI reading drops below your configured threshold. This may indicate drought stress, pest damage, or nutrient deficiency.</p>
            </div>
            <div class="mb-3">
                <p class="fw-600 mb-1">☀️ Drought Risk Alert</p>
                <p class="text-muted small mb-0">Automatically checks weather data for your farm fields. If rainfall is below 5mm in the last 7 days, you'll receive a drought risk warning.</p>
            </div>
            <div class="mb-3">
                <p class="fw-600 mb-1">🌾 Harvest Window Alert</p>
                <p class="text-muted small mb-0">When NDVI starts declining past peak and your harvest date is within 14 days, you'll be notified that your crop is ready for harvest.</p>
            </div>
            <div>
                <p class="fw-600 mb-1">📈 High Yield Prediction</p>
                <p class="text-muted small mb-0">When our ML model predicts above-average yield for your crop cycle, you'll receive a positive notification.</p>
            </div>
            <hr>
            <p class="text-muted small mb-0"><i class="bi bi-clock me-1"></i>Alerts are checked nightly. Duplicate alerts are suppressed for 3 days.</p>
        </div>
    </div>
</div>
@endsection
