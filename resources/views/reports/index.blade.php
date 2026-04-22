@extends('layouts.app')
@section('title','Reports')
@section('page-title','📄 Reports')
@section('content')
<div class="page-header">
    <div class="page-header-left"><h2>Reports</h2><p>Generated PDF and Excel reports.</p></div>
    <div class="page-header-actions">
        <a href="{{ route('reports.export.excel') }}" class="btn-outline"><i class="bi bi-file-earmark-excel"></i> Quick Excel</a>
        <a href="{{ route('reports.export.pdf') }}" class="btn-outline"><i class="bi bi-file-earmark-pdf"></i> Quick PDF</a>
        <a href="{{ route('reports.create') }}" class="btn-primary-green"><i class="bi bi-plus-lg"></i> New Report</a>
    </div>
</div>
<div class="table-wrapper">
<table class="data-table">
    <thead><tr><th>#</th><th>Title</th><th>Type</th><th>Category</th><th>Status</th><th>Records</th><th>Generated</th><th>Actions</th></tr></thead>
    <tbody>
    @forelse($reports as $r)
    <tr>
        <td>{{ $r->id }}</td>
        <td><a href="{{ route('reports.show',$r) }}" style="font-weight:600; color:var(--text-primary); text-decoration:none;">{{ $r->title }}</a></td>
        <td><span class="badge-pill badge-info">{{ $r->type }}</span></td>
        <td><span class="badge-pill badge-secondary">{{ $r->report_category }}</span></td>
        <td><span class="status-dot status-dot-{{ $r->status === 'ready' ? 'processed' : ($r->status === 'generating' ? 'processing' : 'failed') }}">{{ ucfirst($r->status) }}</span></td>
        <td>{{ $r->record_count }}</td>
        <td>{{ $r->generated_at?->format('M d, Y') ?? '—' }}</td>
        <td style="display:flex; gap:5px;">
            @if($r->isReady())<a href="{{ route('reports.download',$r) }}" class="btn-outline btn-sm"><i class="bi bi-download"></i></a>@endif
            <form action="{{ route('reports.destroy',$r) }}" method="POST" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn-danger btn-sm"><i class="bi bi-trash"></i></button></form>
        </td>
    </tr>
    @empty
    <tr><td colspan="8"><div class="empty-state"><div class="empty-state-icon"><i class="bi bi-file-earmark-x"></i></div><h4>No reports yet</h4><p>Generate your first report.</p><a href="{{ route('reports.create') }}" class="btn-primary-green">Generate Report</a></div></td></tr>
    @endforelse
    </tbody>
</table>
</div>
<div class="pagination-wrapper">{{ $reports->links() }}</div>
@endsection
