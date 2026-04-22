@extends('layouts.app')
@section('title', 'Security Settings')
@section('page-title', '🔐 Security')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h2>Security Settings</h2>
        <p>Change your password and manage security preferences.</p>
    </div>
</div>
<div style="max-width:600px;">
<div class="card">
    <div class="card-header"><h3 class="card-title"><i class="bi bi-key-fill me-2 text-warning"></i>Change Password</h3></div>
    <div class="card-body">
        <form action="{{ route('settings.password.update') }}" method="POST">
            @csrf @method('PUT')
            <div class="form-group">
                <label class="form-label">Current Password <span class="required">*</span></label>
                <input type="password" name="current_password" class="form-control" required>
                @error('current_password') <div class="form-error">{{ $message }}</div> @enderror
            </div>
            <div class="form-group">
                <label class="form-label">New Password <span class="required">*</span></label>
                <input type="password" name="password" class="form-control" required minlength="8">
                @error('password') <div class="form-error">{{ $message }}</div> @enderror
            </div>
            <div class="form-group">
                <label class="form-label">Confirm New Password <span class="required">*</span></label>
                <input type="password" name="password_confirmation" class="form-control" required>
            </div>
            <button type="submit" class="btn-primary-green"><i class="bi bi-shield-check"></i> Update Password</button>
        </form>
    </div>
</div>

<div class="card" style="margin-top:20px;">
    <div class="card-header"><h3 class="card-title"><i class="bi bi-key-fill me-2 text-info"></i>API Access Tokens</h3></div>
    <div class="card-body">
        @php $tokens = auth()->user()->tokens; @endphp
        @if($tokens->count())
            @foreach($tokens as $token)
            <div style="display:flex; align-items:center; justify-content:space-between; padding:10px 0; border-bottom:1px solid var(--border-light);">
                <div>
                    <p style="font-weight:600; font-size:13px; margin:0;">{{ $token->name }}</p>
                    <p style="font-size:11px; color:var(--text-muted); margin:0;">Created: {{ $token->created_at->format('M d, Y') }}</p>
                </div>
                <form action="{{ route('settings.security') }}" method="POST">
                    @csrf @method('DELETE')
                    <input type="hidden" name="token_id" value="{{ $token->id }}">
                    <button class="btn-danger btn-sm"><i class="bi bi-trash"></i> Revoke</button>
                </form>
            </div>
            @endforeach
        @else
            <p style="color:var(--text-muted); font-size:13px;">No API tokens generated yet.</p>
        @endif
    </div>
</div>
</div>
@endsection
