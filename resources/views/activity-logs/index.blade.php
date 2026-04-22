@extends('layouts.app')
@section('title', 'Activity Logs')
@section('page-title', '📋 Activity Logs')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h2>Activity Logs</h2>
        <p>Comprehensive audit trail of all user actions.</p>
    </div>
</div>

<div class="table-wrapper">
    <table class="data-table">
        <thead>
            <tr><th>Time</th><th>User</th><th>Action</th><th>Description</th><th>IP</th><th>Method</th></tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
            <tr>
                <td style="white-space:nowrap; font-size:11px;">{{ $log->created_at->format('M d, H:i') }}</td>
                <td>{{ $log->user?->name ?? 'System' }}</td>
                <td><span class="badge-pill badge-secondary">{{ $log->action }}</span></td>
                <td>{{ Str::limit($log->description, 80) }}</td>
                <td style="font-size:11px; color:var(--text-muted);">{{ $log->ip_address }}</td>
                <td><span class="badge-pill {{ match($log->method) { 'POST'=>'badge-success', 'DELETE'=>'badge-danger', default=>'badge-secondary' } }}">{{ $log->method }}</span></td>
            </tr>
            @empty
            <tr><td colspan="6">
                <div class="empty-state"><div class="empty-state-icon"><i class="bi bi-clock-history"></i></div><p>No activity logged yet.</p></div>
            </td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="pagination-wrapper">{{ $logs->links() }}</div>
@endsection
