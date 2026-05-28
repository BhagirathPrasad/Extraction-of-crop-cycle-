@php
    $user = auth()->user();
    $pendingCount = $user->datasets()->whereIn('status', ['pending', 'processing'])->count();
    $navSections = [
        'Overview' => [
            [
                'label' => 'Dashboard',
                'route' => route('dashboard'),
                'active' => request()->routeIs('dashboard'),
                'icon' => 'grid-1x2-fill',
            ],
            [
                'label' => 'Analytics & AI',
                'route' => route('analytics.index'),
                'active' => request()->routeIs('analytics.*'),
                'icon' => 'graph-up-arrow',
                'badge' => 'New',
            ],
        ],
        'Data Hub' => [
            [
                'label' => 'Datasets',
                'route' => route('datasets.index'),
                'active' => request()->routeIs('datasets.*'),
                'icon' => 'database-fill',
                'badge' => $pendingCount > 0 ? $pendingCount : null,
                'badge_class' => $pendingCount > 0 ? 'sidebar-badge sidebar-badge-warning' : null,
            ],
            [
                'label' => 'Farm Fields',
                'route' => route('farm-fields.index'),
                'active' => request()->routeIs('farm-fields.*'),
                'icon' => 'map-fill',
                'badge' => 'New',
            ],
            [
                'label' => 'Crop Cycle Analysis',
                'route' => route('crop-cycles.index'),
                'active' => request()->routeIs('crop-cycles.*'),
                'icon' => 'arrow-repeat',
            ],
            [
                'label' => 'Search Cycles',
                'route' => route('search.index'),
                'active' => request()->routeIs('search.index'),
                'icon' => 'search',
            ],
            [
                'label' => 'Reports',
                'route' => route('reports.index'),
                'active' => request()->routeIs('reports.*'),
                'icon' => 'file-earmark-bar-graph-fill',
            ],
        ],
        'Workspace' => [
            [
                'label' => 'Notifications',
                'route' => route('notifications.index'),
                'active' => request()->routeIs('notifications.*'),
                'icon' => 'bell-fill',
                'dot' => true,
            ],
            [
                'label' => 'Settings',
                'route' => route('settings.profile'),
                'active' => request()->routeIs('settings.*'),
                'icon' => 'gear-fill',
            ],
        ],
    ];

    if ($user->isAdmin()) {
        $navSections['Administration'] = [
            [
                'label' => 'Users',
                'route' => route('users.index'),
                'active' => request()->routeIs('users.*'),
                'icon' => 'people-fill',
            ],
            [
                'label' => 'Activity Logs',
                'route' => route('activity-logs.index'),
                'active' => request()->routeIs('activity-logs.*'),
                'icon' => 'clock-history',
            ],
        ];
    }
@endphp

<aside class="app-sidebar" id="appSidebar">
    <div class="sidebar-brand">
        <a href="{{ route('dashboard') }}" class="sidebar-brand-link">
            <span class="brand-icon">
                <i class="bi bi-activity"></i>
            </span>
            <span class="brand-text">
                <span class="brand-name">CropsCycle</span>
                <span class="brand-tagline">Satellite Intelligence</span>
            </span>
        </a>
        <button class="sidebar-compact-btn d-none d-lg-inline-flex" type="button" onclick="toggleSidebarCollapse()">
            <i class="bi bi-list"></i>
        </button>
        <button class="sidebar-close-btn d-lg-none" type="button" onclick="closeSidebar()">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <div class="sidebar-user-card">
        <img src="{{ $user->avatar_url }}" alt="Avatar" class="sidebar-avatar">
        <div class="sidebar-user-copy">
            <p class="sidebar-user-name">{{ $user->name }}</p>
            <span class="sidebar-user-status"><i class="bi bi-circle-fill text-success" style="font-size: 0.5rem;"></i> {{ ucfirst($user->role) }}</span>
        </div>
    </div>

    <nav class="sidebar-nav">
        @foreach($navSections as $sectionLabel => $items)
            <div class="sidebar-nav-group">
                <p class="sidebar-section-label">{{ $sectionLabel }}</p>
                @foreach($items as $item)
                    <a href="{{ $item['route'] }}" class="sidebar-link {{ $item['active'] ? 'active' : '' }}">
                        <span class="sidebar-link-icon">
                            <i class="bi bi-{{ $item['icon'] }}"></i>
                        </span>
                        <span class="sidebar-link-text">{{ $item['label'] }}</span>
                        @if(!empty($item['badge']))
                            <span class="{{ $item['badge_class'] ?? 'sidebar-badge' }}">{{ $item['badge'] }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
        @endforeach
    </nav>

    <div class="sidebar-footer">
        <form action="{{ route('settings.theme.toggle') }}" method="POST" class="sidebar-footer-form mb-2">
            @csrf
            <button type="submit" class="theme-toggle-btn">
                @if(auth()->user()->theme === 'dark')
                    <i class="bi bi-sun-fill text-warning"></i>
                    <span>Light Mode</span>
                @else
                    <i class="bi bi-moon-stars-fill text-info"></i>
                    <span>Dark Mode</span>
                @endif
            </button>
        </form>
        <form action="{{ route('logout') }}" method="POST" class="sidebar-footer-form">
            @csrf
            <button type="submit" class="logout-btn">
                <i class="bi bi-box-arrow-right"></i>
                <span>Logout</span>
            </button>
        </form>
    </div>
</aside>
