@extends('layouts.app')
@section('title', 'Cache Management')

@section('content')
<div class="page-header-section">
    <div class="page-header-row">
        <div>
            <h1 class="page-title"><i class="bi bi-lightning-charge-fill me-2 text-warning"></i>Cache Management</h1>
            <p class="page-subtitle">Monitor and manage application cache. Clear specific data groups or flush the entire cache.</p>
        </div>
        <form action="{{ route('admin.cache.clear-all') }}" method="POST" onsubmit="return confirm('⚠️ This will flush ALL cached data. Continue?');">
            @csrf
            <button class="btn btn-danger">
                <i class="bi bi-trash me-2"></i>Flush All Cache
            </button>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="card-panel text-center">
            <i class="bi bi-server text-info" style="font-size: 2rem;"></i>
            <p class="fw-700 mt-2 mb-0">{{ ucfirst($stats['driver']) }}</p>
            <span class="text-muted small">Cache Driver</span>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card-panel text-center">
            <i class="bi bi-layers text-success" style="font-size: 2rem;"></i>
            <p class="fw-700 mt-2 mb-0">{{ $stats['total_groups'] }}</p>
            <span class="text-muted small">Cache Groups</span>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card-panel text-center">
            <i class="bi bi-database text-warning" style="font-size: 2rem;"></i>
            <p class="fw-700 mt-2 mb-0">{{ $stats['cache_store'] }}</p>
            <span class="text-muted small">Store Backend</span>
        </div>
    </div>
</div>

<div class="card-panel">
    <h5 class="fw-700 mb-3"><i class="bi bi-grid me-2 text-success"></i>Cache Groups</h5>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Cache Group</th>
                    <th>Key Prefix</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($groups as $group)
                <tr>
                    <td>
                        <span class="fw-600">{{ $group['label'] }}</span>
                    </td>
                    <td><code class="small">{{ $group['prefix'] }}_*</code></td>
                    <td class="text-end">
                        <form action="{{ route('admin.cache.clear-group') }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="prefix" value="{{ $group['prefix'] }}">
                            <button class="btn btn-sm btn-outline-warning">
                                <i class="bi bi-arrow-clockwise me-1"></i>Clear
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
