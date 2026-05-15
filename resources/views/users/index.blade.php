@extends('layouts.app')
@section('title', 'Manage Users')
@section('page-title', '👥 Manage Users')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div class="page-header-left">
        <h2>Users Management</h2>
        <p class="text-muted">View, add, and manage system users and their roles.</p>
    </div>
    <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-person-plus-fill"></i> Add User</a>
</div>

<div class="card mb-4 shadow-sm">
    <div class="card-body">
        <form method="GET" action="{{ route('users.index') }}" class="d-flex gap-2">
            <input type="text" name="search" class="form-control" placeholder="Search users by name or email..." value="{{ request('search') }}">
            <select name="role" class="form-select">
                <option value="">All Roles</option>
                <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="researcher" {{ request('role') == 'researcher' ? 'selected' : '' }}>Researcher</option>
                <option value="farmer" {{ request('role') == 'farmer' ? 'selected' : '' }}>Farmer</option>
            </select>
            <button type="submit" class="btn btn-secondary">Filter</button>
            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Reset</a>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover table-striped mb-0">
            <thead class="table-light">
                <tr>
                    <th>User</th>
                    <th>Role</th>
                    <th>Organization</th>
                    <th>Status</th>
                    <th>Registered</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="rounded-circle me-3" width="40" height="40">
                                <div>
                                    <div class="fw-bold">{{ $user->name }}</div>
                                    <div class="text-muted small">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-{{ $user->role === 'admin' ? 'danger' : ($user->role === 'researcher' ? 'info' : 'success') }}">
                                {{ ucfirst($user->role) }}
                            </span>
                        </td>
                        <td>{{ $user->organization ?? '-' }}</td>
                        <td>
                            @if($user->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td>{{ $user->created_at->format('M d, Y') }}</td>
                        <td class="text-end">
                            <form action="{{ route('users.toggle-status', $user) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-{{ $user->is_active ? 'warning' : 'success' }}" title="Toggle Status">
                                    <i class="bi bi-power"></i>
                                </button>
                            </form>
                            <a href="{{ route('users.show', $user) }}" class="btn btn-sm btn-outline-info" title="View"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No users found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white border-top-0 pt-3">
        {{ $users->links() }}
    </div>
</div>
@endsection
