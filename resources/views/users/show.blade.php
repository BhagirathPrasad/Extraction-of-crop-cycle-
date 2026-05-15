@extends('layouts.app')
@section('title', 'User Profile: ' . $user->name)
@section('page-title', '👤 User Profile')

@section('content')
<div class="page-header mb-4">
    <div class="page-header-left">
        <h2>{{ $user->name }}</h2>
        <p class="text-muted">Detailed view of user activity, role, and associated data.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back to Users</a>
        <a href="{{ route('users.edit', $user) }}" class="btn btn-primary btn-sm"><i class="bi bi-pencil"></i> Edit Profile</a>
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm h-100 text-center">
            <div class="card-body pt-5">
                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="rounded-circle mb-3 shadow" width="100" height="100">
                <h4 class="card-title">{{ $user->name }}</h4>
                <p class="text-muted mb-2">{{ $user->email }}</p>
                
                <span class="badge bg-{{ $user->role === 'admin' ? 'danger' : ($user->role === 'researcher' ? 'info' : 'success') }} px-3 py-2 mb-3">
                    {{ ucfirst($user->role) }}
                </span>

                <hr>

                <div class="text-start mt-4">
                    <p><strong><i class="bi bi-building me-2"></i> Organization:</strong> {{ $user->organization ?? 'Not specified' }}</p>
                    <p><strong><i class="bi bi-geo-alt me-2"></i> Region:</strong> {{ $user->region ?? 'Not specified' }}</p>
                    <p><strong><i class="bi bi-telephone me-2"></i> Phone:</strong> {{ $user->phone ?? 'Not specified' }}</p>
                    <p><strong><i class="bi bi-calendar-check me-2"></i> Joined:</strong> {{ $user->created_at->format('F d, Y') }}</p>
                    <p><strong><i class="bi bi-activity me-2"></i> Status:</strong> 
                        @if($user->is_active)
                            <span class="text-success fw-bold">Active</span>
                        @else
                            <span class="text-danger fw-bold">Inactive</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8 mb-4">
        <div class="row">
            <div class="col-sm-4 mb-4">
                <div class="card shadow-sm bg-primary text-white h-100">
                    <div class="card-body d-flex flex-column align-items-center justify-content-center py-4">
                        <i class="bi bi-cloud-arrow-up-fill fs-1 mb-2"></i>
                        <h2 class="display-5 fw-bold mb-0">{{ $user->datasets_count ?? 0 }}</h2>
                        <p class="mb-0">Datasets Uploaded</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-4 mb-4">
                <div class="card shadow-sm bg-success text-white h-100">
                    <div class="card-body d-flex flex-column align-items-center justify-content-center py-4">
                        <i class="bi bi-diagram-3-fill fs-1 mb-2"></i>
                        <h2 class="display-5 fw-bold mb-0">{{ $user->crop_cycles_count ?? 0 }}</h2>
                        <p class="mb-0">Crop Cycles Processed</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-4 mb-4">
                <div class="card shadow-sm bg-info text-white h-100">
                    <div class="card-body d-flex flex-column align-items-center justify-content-center py-4">
                        <i class="bi bi-file-earmark-pdf-fill fs-1 mb-2"></i>
                        <h2 class="display-5 fw-bold mb-0">{{ $user->reports_count ?? 0 }}</h2>
                        <p class="mb-0">Reports Generated</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h5 class="mb-0">Recent Activity</h5>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item text-center text-muted py-4">
                        Detailed activity logs for this user will appear here.
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
