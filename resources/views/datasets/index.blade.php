@extends('layouts.app')
@section('title', 'Datasets')
@section('page-title', '🗄️ Datasets')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h2>Satellite Datasets</h2>
        <p>Manage multi-temporal satellite data uploads for crop cycle extraction.</p>
    </div>
    <div class="page-header-actions">
        <a href="{{ route('datasets.create') }}" class="btn-primary-green">
            <i class="bi bi-cloud-upload-fill"></i> Upload Dataset
        </a>
    </div>
</div>

{{-- Filters --}}
<form method="GET" action="{{ route('datasets.index') }}">
<div class="filters-bar">
    <i class="bi bi-funnel text-muted"></i>
    <select name="status" class="filter-select" onchange="this.form.submit()">
        <option value="">All Status</option>
        @foreach(['pending','processing','processed','failed'] as $s)
            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
        @endforeach
    </select>
    <select name="crop_type" class="filter-select" onchange="this.form.submit()">
        <option value="">All Crops</option>
        @foreach($cropTypes as $ct)
            <option value="{{ $ct }}" {{ request('crop_type') === $ct ? 'selected' : '' }}>{{ ucfirst($ct) }}</option>
        @endforeach
    </select>
    <select name="region" class="filter-select" onchange="this.form.submit()">
        <option value="">All Regions</option>
        @foreach($regions as $r)
            <option value="{{ $r }}" {{ request('region') === $r ? 'selected' : '' }}>{{ $r }}</option>
        @endforeach
    </select>
    <select name="type" class="filter-select" onchange="this.form.submit()">
        <option value="">All Types</option>
        <option value="CSV"     {{ request('type') === 'CSV' ? 'selected' : '' }}>CSV</option>
        <option value="GeoTIFF" {{ request('type') === 'GeoTIFF' ? 'selected' : '' }}>GeoTIFF</option>
    </select>
    <input type="date" name="date_from" class="filter-select" value="{{ request('date_from') }}" placeholder="From">
    <input type="date" name="date_to"   class="filter-select" value="{{ request('date_to') }}" placeholder="To">
    @if(request()->hasAny(['status','crop_type','region','type','date_from','date_to']))
        <a href="{{ route('datasets.index') }}" class="btn-outline btn-sm"><i class="bi bi-x-circle"></i> Clear</a>
    @endif
</div>
</form>

{{-- Table --}}
<div class="table-wrapper">
    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Crop</th>
                <th>Region</th>
                <th>Type</th>
                <th>Records</th>
                <th>Status</th>
                <th>Uploaded</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($datasets as $ds)
            <tr>
                <td class="text-muted">{{ $ds->id }}</td>
                <td>
                    <a href="{{ route('datasets.show', $ds) }}" style="font-weight:600; text-decoration:none; color: var(--text-primary);">
                        {{ $ds->name }}
                    </a>
                    <div style="font-size:11px; color:var(--text-muted);">{{ $ds->original_filename }}</div>
                </td>
                <td><span class="badge-pill badge-secondary">{{ ucfirst($ds->crop_type ?? '—') }}</span></td>
                <td>{{ $ds->region ?? '—' }}</td>
                <td><span class="badge-pill badge-info">{{ $ds->type }}</span></td>
                <td>{{ number_format($ds->record_count) }}</td>
                <td>
                    <span class="status-dot status-dot-{{ $ds->status }}">{{ ucfirst($ds->status) }}</span>
                </td>
                <td>{{ $ds->created_at->format('M d, Y') }}</td>
                <td>
                    <div style="display:flex; gap:6px; align-items:center;">
                        <a href="{{ route('datasets.show', $ds) }}" class="btn-outline btn-sm"><i class="bi bi-eye"></i></a>
                        @if($ds->isFailed() || $ds->isPending())
                        <form action="{{ route('datasets.reprocess', $ds) }}" method="POST" style="display:inline;">
                            @csrf
                            <button class="btn-outline btn-sm" title="Reprocess"><i class="bi bi-arrow-clockwise"></i></button>
                        </form>
                        @endif
                        <form action="{{ route('datasets.destroy', $ds) }}" method="POST"
                              onsubmit="return confirm('Delete this dataset?')">
                            @csrf @method('DELETE')
                            <button class="btn-danger btn-sm"><i class="bi bi-trash"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9">
                    <div class="empty-state">
                        <div class="empty-state-icon"><i class="bi bi-database-x"></i></div>
                        <h4>No datasets found</h4>
                        <p>Upload your first satellite dataset to start extracting crop cycle parameters.</p>
                        <a href="{{ route('datasets.create') }}" class="btn-primary-green">
                            <i class="bi bi-cloud-upload-fill"></i> Upload Dataset
                        </a>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination --}}
<div class="pagination-wrapper">
    {{ $datasets->links() }}
</div>

@endsection
