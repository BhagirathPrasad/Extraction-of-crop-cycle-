@extends('layouts.app')
@section('title', 'Notifications')
@section('page-title', '🔔 Notifications')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h2>Notifications</h2>
        <p>System alerts and activity updates.</p>
    </div>
    <form action="{{ route('notifications.mark-all-read') }}" method="POST">
        @csrf
        <button class="btn-outline"><i class="bi bi-check2-all"></i> Mark All Read</button>
    </form>
</div>

<div class="card">
    <div class="card-body" style="padding:0;">
        @forelse($notifications as $notif)
        @php
            $data = $notif->data;
            $isRead = $notif->read_at !== null;
        @endphp
        <div style="display:flex; align-items:flex-start; gap:14px; padding:16px 20px; border-bottom:1px solid var(--border-light); {{ !$isRead ? 'background: rgba(22,163,74,.04);' : '' }}">
            <div style="width:10px; height:10px; border-radius:50%; background:#22c55e; flex-shrink:0; margin-top:5px; {{ $isRead ? 'opacity:0;' : '' }}"></div>
            <div style="width:42px;height:42px; border-radius:10px; background:rgba(22,163,74,.1); display:flex;align-items:center;justify-content:center; font-size:18px; color:var(--brand-green); flex-shrink:0;">
                <i class="bi bi-{{ $data['icon'] ?? 'bell' }}-fill"></i>
            </div>
            <div style="flex:1;">
                <p style="font-weight:600; font-size:13px; margin:0;">{{ $data['title'] ?? 'Notification' }}</p>
                <p style="font-size:12px; color:var(--text-muted); margin:3px 0;">{{ $data['message'] ?? '' }}</p>
                <p style="font-size:11px; color:var(--text-muted); margin:0;">{{ $notif->created_at->diffForHumans() }}</p>
                @if(!empty($data['url']))
                    <a href="{{ $data['url'] }}" style="font-size:12px; font-weight:600; color:var(--brand-green);">View details →</a>
                @endif
            </div>
            <div style="display:flex; gap:6px;">
                @if(!$isRead)
                <form action="{{ route('notifications.mark-read', $notif->id) }}" method="POST">
                    @csrf
                    <button class="btn-outline btn-sm"><i class="bi bi-check"></i></button>
                </form>
                @endif
                <form action="{{ route('notifications.destroy', $notif->id) }}" method="POST">
                    @csrf @method('DELETE')
                    <button class="btn-danger btn-sm"><i class="bi bi-trash"></i></button>
                </form>
            </div>
        </div>
        @empty
        <div class="empty-state">
            <div class="empty-state-icon"><i class="bi bi-bell-slash"></i></div>
            <h4>No notifications</h4>
            <p>You're all caught up! Notifications will appear here when datasets are processed.</p>
        </div>
        @endforelse
    </div>
</div>

@if(method_exists($notifications, 'links'))
<div class="pagination-wrapper">{{ $notifications->links() }}</div>
@endif
@endsection
