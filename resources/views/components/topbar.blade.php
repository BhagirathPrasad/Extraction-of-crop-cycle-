@php
    $user = auth()->user();
    $recentNotifications = $recentNotifications ?? collect();
    $localeOptions = [
        'en' => ['label' => 'English', 'flag' => '🇬🇧'],
        'hi' => ['label' => 'हिंदी', 'flag' => '🇮🇳'],
        'fr' => ['label' => 'Français', 'flag' => '🇫🇷'],
    ];
@endphp

<header class="app-topbar">
    <div class="topbar-left">
        <button class="topbar-menu-btn d-lg-none" type="button" onclick="openSidebar()" aria-label="Open menu">
            <i class="bi bi-list"></i>
        </button>

        <div class="topbar-title d-none d-md-block">
            <nav aria-label="breadcrumb" class="topbar-breadcrumb">
                @hasSection('breadcrumb')
                    @yield('breadcrumb')
                @else
                    <span>Smart Agriculture Platform</span>
                    <i class="bi bi-dot"></i>
                    <span>@yield('title', 'Dashboard')</span>
                @endif
            </nav>
        </div>
    </div>

    <div class="topbar-center">
        <form class="topbar-search" role="search" onsubmit="return false;">
            <i class="bi bi-search topbar-search-icon"></i>
            <input type="text" class="topbar-search-input" placeholder="{{ __('Search anything...') }}" id="globalSearch">
            <span class="topbar-search-shortcut">/</span>
        </form>
    </div>

    <div class="topbar-actions">
        <div class="ui-dropdown">
            <button class="topbar-icon-btn" type="button" data-dropdown-trigger="languageMenu" aria-haspopup="true" aria-expanded="false" title="Language switcher">
                <i class="bi bi-translate"></i>
            </button>
            <div class="ui-dropdown-menu locale-dropdown" id="languageMenu">
                <div class="dropdown-menu-head">
                    <strong>Language</strong>
                    <span>Switch interface locale</span>
                </div>
                @foreach($localeOptions as $code => $locale)
                    <form action="{{ route('settings.locale.switch') }}" method="POST">
                        @csrf
                        <input type="hidden" name="locale" value="{{ $code }}">
                        <button type="submit" class="dropdown-item {{ app()->getLocale() === $code ? 'active' : '' }}">
                            <span class="dropdown-flag">{{ $locale['flag'] }}</span>
                            <span>{{ $locale['label'] }}</span>
                            @if(app()->getLocale() === $code)
                                <i class="bi bi-check2 ms-auto"></i>
                            @endif
                        </button>
                    </form>
                @endforeach
            </div>
        </div>

        <div class="ui-dropdown">
            <button class="topbar-icon-btn" type="button" data-dropdown-trigger="notificationMenu" aria-haspopup="true" aria-expanded="false" title="Notifications" id="notifBell">
                <i class="bi bi-bell-fill"></i>
                <span class="notif-badge" id="notifBadge" style="display:none;">0</span>
            </button>

            <div class="ui-dropdown-menu notif-dropdown" id="notificationMenu">
                <div class="notif-header">
                    <div>
                        <strong>Notifications</strong>
                        <span>Recent platform activity</span>
                    </div>
                    <form id="markAllForm" action="{{ route('notifications.mark-all-read') }}" method="POST">
                        @csrf
                        <button type="submit" class="notif-mark-all">Mark all read</button>
                    </form>
                </div>
                <div class="notif-scroll" id="notifList">
                    @forelse($recentNotifications as $notification)
                        @php
                            $data = $notification->data;
                            $type = strtolower(($data['title'] ?? '') . ' ' . ($data['message'] ?? ''));
                            $icon = $data['icon'] ?? (str_contains($type, 'dataset') ? 'database-fill' : (str_contains($type, 'analysis') ? 'flower1' : 'exclamation-triangle-fill'));
                        @endphp
                        <div class="notif-item {{ $notification->read_at ? '' : 'unread' }}">
                            <div class="notif-item-icon"><i class="bi bi-{{ $icon }}"></i></div>
                            <div class="notif-item-copy">
                                <div class="notif-item-title-row">
                                    <strong>{{ $data['title'] ?? 'Notification' }}</strong>
                                    @if(!$notification->read_at)<span class="notif-item-dot"></span>@endif
                                </div>
                                <p>{{ $data['message'] ?? 'A new system event has been logged.' }}</p>
                                <div class="notif-item-meta">
                                    <span>{{ $notification->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="notif-empty">
                            <i class="bi bi-bell-slash"></i>
                            <p>No new notifications</p>
                        </div>
                    @endforelse
                </div>
                <div class="notif-footer">
                    <a href="{{ route('notifications.index') }}" class="btn-outline btn-sm w-100 justify-content-center">View all</a>
                </div>
            </div>
        </div>

        <div class="ui-dropdown">
            <button class="topbar-avatar-btn" type="button" data-dropdown-trigger="profileMenu" aria-haspopup="true" aria-expanded="false">
                <img src="{{ $user->avatar_url }}" alt="Profile" class="topbar-avatar">
                <span class="topbar-user-copy d-none d-sm-grid">
                    <strong>{{ $user->name }}</strong>
                    <small>{{ ucfirst($user->role) }}</small>
                </span>
                <i class="bi bi-chevron-down ms-1"></i>
            </button>

            <div class="ui-dropdown-menu profile-dropdown" id="profileMenu">
                <div class="profile-card-head">
                    <img src="{{ $user->avatar_url }}" alt="Avatar" class="profile-card-avatar">
                    <div class="profile-card-copy">
                        <strong>{{ $user->name }}</strong>
                        <span>{{ $user->email }}</span>
                    </div>
                </div>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="{{ route('settings.profile') }}"><i class="bi bi-person"></i><span>Profile</span></a>
                <a class="dropdown-item" href="{{ route('settings.security') }}"><i class="bi bi-shield-lock"></i><span>Security</span></a>
                <form action="{{ route('settings.theme.toggle') }}" method="POST">
                    @csrf
                    <button type="submit" class="dropdown-item w-100">
                        @if(auth()->user()->theme === 'dark')
                            <i class="bi bi-sun"></i><span>Light Mode</span>
                        @else
                            <i class="bi bi-moon"></i><span>Dark Mode</span>
                        @endif
                    </button>
                </form>
                <div class="dropdown-divider"></div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="dropdown-item text-danger w-100"><i class="bi bi-box-arrow-right"></i><span>Logout</span></button>
                </form>
            </div>
        </div>
    </div>
</header>

@push('scripts')
<script>
function fetchUnreadCount() {
    fetch('{{ route('notifications.unread-count') }}', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
        .then(response => response.json())
        .then(data => {
            const badge = document.getElementById('notifBadge');
            const dot = document.getElementById('sidebarUnreadDot');

            if (!badge) return;

            if (data.count > 0) {
                badge.textContent = data.count > 9 ? '9+' : data.count;
                badge.style.display = 'flex';
                if (dot) dot.style.display = 'inline-flex';
            } else {
                badge.style.display = 'none';
                if (dot) dot.style.display = 'none';
            }
        })
        .catch(() => {});
}
</script>
@endpush
