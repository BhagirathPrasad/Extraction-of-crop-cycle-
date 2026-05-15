@extends('layouts.app')
@section('title', 'Edit User')
@section('page-title', '✏️ Edit User: ' . $user->name)

@section('content')
<div class="page-header mb-4">
    <div class="page-header-left">
        <h2>Edit User</h2>
        <p class="text-muted">Update profile information and privileges for {{ $user->name }}.</p>
    </div>
    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back to Users</a>
</div>

<div class="card shadow-sm" style="max-width: 800px;">
    <div class="card-body">
        <form action="{{ route('users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                    @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email Address <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                    @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">New Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current">
                    @error('password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" name="password_confirmation" class="form-control">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Role <span class="text-danger">*</span></label>
                    <select name="role" class="form-select" required>
                        <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="researcher" {{ old('role', $user->role) === 'researcher' ? 'selected' : '' }}>Researcher</option>
                        <option value="farmer" {{ old('role', $user->role) === 'farmer' ? 'selected' : '' }}>Farmer</option>
                    </select>
                    @error('role') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Organization</label>
                    <input type="text" name="organization" class="form-control" value="{{ old('organization', $user->organization) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Region</label>
                    <input type="text" name="region" class="form-control" value="{{ old('region', $user->region) }}">
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
                    @error('phone') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 d-flex align-items-end pb-2">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" id="isActive" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="isActive">Account is Active</label>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save Changes</button>
        </form>
    </div>
</div>
@endsection
