@extends('layouts.app')
@section('title', 'Profile Settings')
@section('page-title', '⚙️ Settings')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h2>Profile Settings</h2>
        <p>Update your personal information and preferences.</p>
    </div>
</div>

<div style="max-width:700px;">
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="bi bi-person-fill me-2 text-success"></i>Personal Information</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('settings.profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')

            <div style="display:flex; align-items:center; gap:20px; margin-bottom:24px;">
                <img src="{{ $user->avatar_url }}" alt="Avatar" style="width:80px;height:80px;border-radius:50%;object-fit:cover;border:3px solid var(--brand-green);">
                <div>
                    <label class="form-label">Profile Avatar</label>
                    <input type="file" name="avatar" class="form-control" accept="image/*" style="padding:6px;">
                    <small style="color:var(--text-muted);">Max 2MB · JPG/PNG</small>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div class="form-group">
                    <label class="form-label">Full Name <span class="required">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                    @error('name') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" class="form-control" value="{{ $user->email }}" disabled style="opacity:.6;">
                </div>
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}" placeholder="+91 XXXXX XXXXX">
                </div>
                <div class="form-group">
                    <label class="form-label">Organization</label>
                    <input type="text" name="organization" class="form-control" value="{{ old('organization', $user->organization) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Region / State</label>
                    <input type="text" name="region" class="form-control" value="{{ old('region', $user->region) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Role</label>
                    <input type="text" class="form-control" value="{{ ucfirst($user->role) }}" disabled style="opacity:.6;">
                </div>
            </div>

            <button type="submit" class="btn-primary-green" style="margin-top:8px;">
                <i class="bi bi-save"></i> Save Profile
            </button>
        </form>
    </div>
</div>

{{-- Language preference --}}
<div class="card" style="margin-top:20px;">
    <div class="card-header"><h3 class="card-title"><i class="bi bi-translate me-2 text-info"></i>Language Preference</h3></div>
    <div class="card-body" style="display:flex; gap:12px; flex-wrap:wrap;">
        @foreach(['en' => '🇬🇧 English', 'hi' => '🇮🇳 हिंदी', 'fr' => '🇫🇷 Français'] as $code => $label)
        <form action="{{ route('settings.locale.switch') }}" method="POST">
            @csrf <input type="hidden" name="locale" value="{{ $code }}">
            <button class="btn-outline {{ $user->locale === $code ? 'btn-primary-green' : '' }}">{{ $label }}</button>
        </form>
        @endforeach
    </div>
</div>
</div>
@endsection
