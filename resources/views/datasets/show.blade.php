@extends('layouts.app')
@section('title', 'Dataset Details')
@section('page-title', '📄 Dataset Details')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h2>{{ $dataset->name }}</h2>
        <p>View dataset information and associated crop cycles.</p>
    </div>
    <div class="page-header-actions">
        <a href="{{ route('datasets.index') }}" class="btn-outline"><i class="bi bi-arrow-left"></i> Back to List</a>
        <a href="{{ route('datasets.edit', $dataset) }}" class="btn-primary-green"><i class="bi bi-pencil"></i> Edit Dataset</a>
    </div>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom: 20px;">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Metadata</h3>
        </div>
        <div class="card-body">
            <table class="table">
                <tbody>
                    <tr><th>Type</th><td><span class="badge-pill badge-info">{{ $dataset->type }}</span></td></tr>
                    <tr><th>Crop Type</th><td>{{ ucfirst($dataset->crop_type ?? 'N/A') }}</td></tr>
                    <tr><th>Region</th><td>{{ $dataset->region ?? 'N/A' }}</td></tr>
                    <tr><th>Status</th><td><span class="status-dot status-dot-{{ $dataset->status }}">{{ ucfirst($dataset->status) }}</span></td></tr>
                    <tr><th>Uploaded By</th><td>{{ optional($dataset->user)->name }}</td></tr>
                    <tr><th>Uploaded At</th><td>{{ $dataset->created_at->format('M d, Y h:i A') }}</td></tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Processing Information</h3>
        </div>
        <div class="card-body">
            <table class="table">
                <tbody>
                    <tr><th>Original File</th><td>{{ $dataset->original_filename }}</td></tr>
                    <tr><th>File Size</th><td>{{ number_format($dataset->file_size / 1024, 2) }} KB</td></tr>
                    <tr><th>Total Records</th><td>{{ number_format($dataset->record_count) }}</td></tr>
                    <tr><th>Processed At</th><td>{{ $dataset->processed_at ? $dataset->processed_at->format('M d, Y h:i A') : 'Pending' }}</td></tr>
                    @if($dataset->processing_notes)
                    <tr><th>Notes</th><td class="text-danger">{{ $dataset->processing_notes }}</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Generated Crop Cycles</h3>
    </div>
    <div class="card-body">
        @if($dataset->cropCycles->count() > 0)
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Season</th>
                            <th>Sowing Date</th>
                            <th>Harvest Date</th>
                            <th>Yield Prediction</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($dataset->cropCycles as $cycle)
                        <tr>
                            <td>{{ $cycle->id }}</td>
                            <td>{{ $cycle->season }} {{ $cycle->season_year }}</td>
                            <td>{{ optional($cycle->sowing_date)->format('M d, Y') ?? 'N/A' }}</td>
                            <td>{{ optional($cycle->harvest_date)->format('M d, Y') ?? 'N/A' }}</td>
                            <td>
                                @if($cycle->yield_prediction)
                                    {{ number_format($cycle->yield_prediction) }} kg/ha
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('crop-cycles.show', $cycle) }}" class="btn-outline btn-sm"><i class="bi bi-eye"></i> View</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-muted">No crop cycles generated for this dataset yet.</p>
        @endif
    </div>
</div>
@endsection
