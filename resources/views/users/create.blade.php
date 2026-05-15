@extends('layouts.app')
@section('title', 'Add User')
@section('page-title', '➕ Add New User')

@section('content')
<div class="page-header mb-4">
    <div class="page-header-left">
        <h2>Add User</h2>
        <p class="text-muted">Create a new user profile with specific roles and permissions.</p>
    </div>
    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back to Users</a>
</div>

<div class="card shadow-sm" style="max-width: 800px;">
    <div class="card-body">
        <form action="{{ route('users.store') }}" method="POST">
            @csrf

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email Address <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                    @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Password <span class="text-danger">*</span></label>
                    <input type="password" name="password" class="form-control" required>
                    @error('password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Role <span class="text-danger">*</span></label>
                    <select name="role" class="form-select" required>
                        <option value="">Select a role...</option>
                        <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="researcher" {{ old('role') === 'researcher' ? 'selected' : '' }}>Researcher</option>
                        <option value="farmer" {{ old('role') === 'farmer' ? 'selected' : '' }}>Farmer</option>
                    </select>
                    @error('role') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Organization</label>
                    <input type="text" name="organization" class="form-control" value="{{ old('organization') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Region</label>
                    <input type="text" name="region" class="form-control" value="{{ old('region') }}">
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Phone Number</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                @error('phone') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Create User</button>
        </form>
    </div>
</div>
@endsection
