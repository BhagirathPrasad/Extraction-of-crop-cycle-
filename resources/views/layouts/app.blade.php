<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="{{ auth()->check() ? auth()->user()->theme : 'light' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="CropsCycle — Extraction of Crop Cycle Parameters from Multi-Temporal Satellite Data">
    <meta name="theme-color" content="#166534">

    <title>{{ config('app.name', 'CropsCycle') }} — @yield('title', 'Dashboard')</title>

    {{-- PWA Manifest --}}
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/icon-192x192.png">

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Bootstrap 5 Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    {{-- App CSS --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body class="app-body">
    <div class="page-loader active" id="pageLoader" aria-hidden="true">
        <div class="page-loader-spinner"></div>
        <div class="page-loader-copy">
            <strong>Loading smart agriculture dashboard</strong>
            <span>Preparing datasets, NDVI trends, and crop-cycle insights...</span>
        </div>
    </div>

    {{-- Mobile sidebar overlay --}}
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    <div class="app-wrapper">
        {{-- ── Sidebar ────────────────────────────────────────────────── --}}
        @auth
            @include('components.sidebar')
        @endauth

        {{-- ── Main Content ───────────────────────────────────────────── --}}
        <div class="main-content" id="mainContent">

            {{-- Top Navigation Bar --}}
            @auth
                @include('components.topbar')
            @endauth

            {{-- Flash Messages --}}
            @if (session('success'))
                <div class="alert-toast alert-toast-success" id="flashSuccess">
                    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                    <button onclick="this.parentElement.remove()" class="alert-toast-close">&times;</button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert-toast alert-toast-danger" id="flashError">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                    <button onclick="this.parentElement.remove()" class="alert-toast-close">&times;</button>
                </div>
            @endif

            {{-- Page Content --}}
            <main class="page-content">
                @yield('content')
            </main>

            {{-- Footer --}}
            <footer class="app-footer">
                <div class="app-footer-inner">
                    <span>© {{ date('Y') }} <strong>CropsCycle</strong> — Smart dashboard for multi-temporal crop intelligence.</span>
                    <span class="text-muted small">Laravel Workspace | PHP {{ PHP_VERSION }}</span>
                </div>
            </footer>
        </div>
    </div>

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>

    {{-- Auto-dismiss flash toasts --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const pageLoader = document.getElementById('pageLoader');
            ['flashSuccess', 'flashError'].forEach(id => {
                const el = document.getElementById(id);
                if (el) setTimeout(() => el.style.opacity = '0', 4000);
            });

            initializeDashboardShell();

            window.setTimeout(() => {
                pageLoader?.classList.remove('active');
            }, 350);
        });

        function initializeDashboardShell() {
            initializeDropdowns();
            initializeSidebarState();
            initializeSearchShortcut();

            if (typeof fetchUnreadCount === 'function') {
                fetchUnreadCount();
                window.setInterval(fetchUnreadCount, 60000);
            }
        }

        function initializeDropdowns() {
            const triggers = document.querySelectorAll('[data-dropdown-trigger]');
            triggers.forEach(trigger => {
                trigger.addEventListener('click', event => {
                    event.stopPropagation();
                    const menuId = trigger.getAttribute('data-dropdown-trigger');
                    const menu = document.getElementById(menuId);
                    if (!menu) return;

                    const isOpen = menu.classList.contains('open');
                    closeAllDropdowns();

                    if (!isOpen) {
                        menu.classList.add('open');
                        trigger.setAttribute('aria-expanded', 'true');
                        trigger.classList.add('active');
                    }
                });
            });

            document.addEventListener('click', event => {
                if (!event.target.closest('.ui-dropdown')) closeAllDropdowns();
            });

            document.addEventListener('keydown', event => {
                if (event.key === 'Escape') closeAllDropdowns();
            });
        }

        function closeAllDropdowns() {
            document.querySelectorAll('.ui-dropdown-menu.open').forEach(menu => {
                menu.classList.remove('open');
            });
            document.querySelectorAll('[data-dropdown-trigger]').forEach(trigger => {
                trigger.setAttribute('aria-expanded', 'false');
                trigger.classList.remove('active');
            });
        }

        function initializeSidebarState() {
            const collapsed = localStorage.getItem('sidebar-collapsed') === 'true';
            if (collapsed && window.innerWidth > 1024) {
                document.body.classList.add('sidebar-collapsed');
            }
        }

        function toggleSidebarCollapse() {
            document.body.classList.toggle('sidebar-collapsed');
            localStorage.setItem('sidebar-collapsed', document.body.classList.contains('sidebar-collapsed'));
        }

        function openSidebar()  {
            document.getElementById('appSidebar')?.classList.add('open');
            document.getElementById('sidebarOverlay')?.classList.add('active');
        }

        function closeSidebar() {
            document.getElementById('appSidebar')?.classList.remove('open');
            document.getElementById('sidebarOverlay')?.classList.remove('active');
        }

        function initializeSearchShortcut() {
            document.addEventListener('keydown', event => {
                if (event.key === '/' && !['INPUT', 'TEXTAREA'].includes(document.activeElement?.tagName)) {
                    event.preventDefault();
                    document.getElementById('globalSearch')?.focus();
                }
            });
        }
    </script>

    @auth
        @include('components.chatbot')
    @endauth

    @stack('scripts')
</body>
</html>
