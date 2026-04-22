@extends('layouts.app')
@section('title', 'Crop Cycles')
@section('page-title', '🌱 Crop Cycles')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h2>Crop Cycle Records</h2>
        <p>Extracted sowing, growth, and harvest parameters from satellite data.</p>
    </div>
    <div class="page-header-actions">
        <a href="{{ route('reports.export.excel') }}" class="btn-outline">
            <i class="bi bi-file-earmark-excel"></i> Export Excel
        </a>
        <a href="{{ route('reports.export.pdf') }}" class="btn-outline">
            <i class="bi bi-file-earmark-pdf"></i> Export PDF
        </a>
        <a href="{{ route('crop-cycles.create') }}" class="btn-primary-green">
            <i class="bi bi-plus-lg"></i> New Cycle
        </a>
    </div>
</div>

{{-- Filters --}}
<form method="GET" action="{{ route('crop-cycles.index') }}">
<div class="filters-bar">
    <i class="bi bi-funnel text-muted"></i>
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
    <select name="season" class="filter-select" onchange="this.form.submit()">
        <option value="">All Seasons</option>
        @foreach(['Kharif','Rabi','Zaid','Summer','Winter'] as $s)
            <option value="{{ $s }}" {{ request('season') === $s ? 'selected' : '' }}>{{ $s }}</option>
        @endforeach
    </select>
    <select name="season_year" class="filter-select" onchange="this.form.submit()">
        <option value="">All Years</option>
        @foreach($seasonYears as $yr)
            <option value="{{ $yr }}" {{ request('season_year') == $yr ? 'selected' : '' }}>{{ $yr }}</option>
        @endforeach
    </select>
    <select name="yield_cat" class="filter-select" onchange="this.form.submit()">
        <option value="">All Yield</option>
        <option value="high"   {{ request('yield_cat') === 'high' ? 'selected' : '' }}>High</option>
        <option value="medium" {{ request('yield_cat') === 'medium' ? 'selected' : '' }}>Medium</option>
        <option value="low"    {{ request('yield_cat') === 'low' ? 'selected' : '' }}>Low</option>
    </select>
    @if(request()->hasAny(['crop_type','region','season','season_year','yield_cat']))
        <a href="{{ route('crop-cycles.index') }}" class="btn-outline btn-sm"><i class="bi bi-x-circle"></i> Clear</a>
    @endif
</div>
</form>

<div class="table-wrapper">
    <table class="data-table">
        <thead>
            <tr>
                <th>#</th><th>Crop</th><th>Region</th><th>Season</th>
                <th>Sowing Date</th><th>Harvest Date</th><th>Growing Days</th>
                <th>NDVI Peak</th><th>Yield (kg/ha)</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($cropCycles as $cycle)
            <tr>
                <td class="text-muted">{{ $cycle->id }}</td>
                <td>
                    <span style="font-weight:600;">{{ ucfirst($cycle->crop_type) }}</span>
                    <div style="font-size:11px;color:var(--text-muted);">{{ $cycle->variety ?? '' }}</div>
                </td>
                <td>{{ $cycle->region }}</td>
                <td><span class="badge-pill badge-secondary">{{ $cycle->season }} {{ $cycle->season_year }}</span></td>
                <td>{{ $cycle->sowing_date?->format('M d, Y') ?? '—' }}</td>
                <td>{{ $cycle->harvest_date?->format('M d, Y') ?? '—' }}</td>
                <td>
                    @if($cycle->growing_days)
                        <span style="font-weight:600;">{{ $cycle->growing_days }}</span> <small class="text-muted">days</small>
                    @else —
                    @endif
                </td>
                <td>
                    @if($cycle->ndvi_max)
                        <div style="display:flex; align-items:center; gap:8px;">
                            <div class="ndvi-bar" style="width:60px;">
                                <div class="ndvi-fill {{ $cycle->ndvi_max > 0.6 ? 'ndvi-fill-good' : ($cycle->ndvi_max > 0.35 ? 'ndvi-fill-medium' : 'ndvi-fill-low') }}"
                                     style="width:{{ round($cycle->ndvi_max * 100) }}%;"></div>
                            </div>
                            <span style="font-size:12px; font-weight:600;">{{ $cycle->ndvi_max }}</span>
                        </div>
                    @else —
                    @endif
                </td>
                <td>
                    @if($cycle->yield_prediction)
                        <span class="badge-pill {{ $cycle->yield_badge_class }}">
                            {{ number_format($cycle->yield_prediction, 0) }}
                        </span>
                    @else —
                    @endif
                </td>
                <td>
                    <div style="display:flex; gap:5px;">
                        <a href="{{ route('crop-cycles.show', $cycle) }}" class="btn-outline btn-sm"><i class="bi bi-eye"></i></a>
                        <a href="{{ route('crop-cycles.edit', $cycle) }}" class="btn-outline btn-sm"><i class="bi bi-pencil"></i></a>
                        <form action="{{ route('crop-cycles.destroy', $cycle) }}" method="POST"
                              onsubmit="return confirm('Delete this cycle?')">
                            @csrf @method('DELETE')
                            <button class="btn-danger btn-sm"><i class="bi bi-trash"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10">
                    <div class="empty-state">
                        <div class="empty-state-icon"><i class="bi bi-arrows-collapse"></i></div>
                        <h4>No crop cycles found</h4>
                        <p>Upload a dataset to automatically extract crop cycle parameters.</p>
                        <a href="{{ route('datasets.create') }}" class="btn-primary-green">Upload Dataset</a>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="pagination-wrapper">{{ $cropCycles->links() }}</div>
@endsection
