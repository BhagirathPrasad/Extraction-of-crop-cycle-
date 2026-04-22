@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Overview')

@section('breadcrumb')
    <span>Home</span>
    <i class="bi bi-chevron-right"></i>
    <span>Dashboard</span>
@endsection

@push('styles')
<style>
.dashboard-shell { display: grid; gap: 2rem; }

.hero-panel {
    position: relative;
    overflow: hidden;
    padding: 2rem;
    border-radius: var(--radius-lg);
    background: linear-gradient(135deg, #166534 0%, #0f766e 100%);
    color: #f8fafc;
    box-shadow: var(--shadow-lg);
}
.hero-grid {
    display: grid;
    grid-template-columns: 1.5fr 1fr;
    gap: 2rem;
    align-items: center;
}
.hero-content h2 {
    margin: 0.5rem 0 1rem;
    font-family: var(--font-heading);
    font-size: 2.2rem;
    line-height: 1.2;
}
.hero-content p {
    margin: 0;
    color: rgba(255, 255, 255, 0.8);
    font-size: 1.05rem;
    line-height: 1.6;
}
.hero-actions {
    display: flex;
    gap: 1rem;
    margin-top: 1.5rem;
}
.hero-btn {
    padding: 0.75rem 1.25rem;
    border-radius: 12px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}
.hero-btn.primary { background: white; color: #166534; }
.hero-btn.secondary { background: rgba(255, 255, 255, 0.1); color: white; border: 1px solid rgba(255, 255, 255, 0.2); }

.hero-stats-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}
.hero-stat-card {
    padding: 1.25rem;
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 18px;
}
.hero-stat-card small { display: block; color: rgba(255, 255, 255, 0.6); font-size: 0.75rem; text-transform: uppercase; margin-bottom: 0.25rem; }
.hero-stat-card strong { display: block; font-size: 1.5rem; font-family: var(--font-heading); }

.section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1rem;
}
.section-header h3 { margin: 0; font-family: var(--font-heading); font-size: 1.25rem; }

.quick-action-link {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 18px;
    transition: all 0.2s ease;
}
.quick-action-link:hover {
    transform: translateY(-2px);
    border-color: var(--brand-green);
    background: var(--bg-hover);
}
.quick-action-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}

@media (max-width: 992px) {
    .hero-grid { grid-template-columns: 1fr; }
}
</style>
@endpush

@section('content')
<div class="dashboard-shell">
    <section class="hero-panel">
        <div class="hero-grid">
            <div class="hero-content">
                <div class="badge-pill badge-success" style="background: rgba(255,255,255,0.15); color: white;">
                    <i class="bi bi-stars"></i> Smart Agriculture AI
                </div>
                <h2>Welcome back, {{ auth()->user()->name }}.</h2>
                <p>
                    Your multi-temporal satellite workspace is synchronized. 
                    Review the latest NDVI trends and crop cycle extractions below.
                </p>
                <div class="hero-actions">
                    <a href="{{ route('datasets.create') }}" class="hero-btn primary">
                        <i class="bi bi-plus-lg"></i> Upload Dataset
                    </a>
                    <a href="{{ route('crop-cycles.index') }}" class="hero-btn secondary">
                        <i class="bi bi-graph-up"></i> View Analytics
                    </a>
                </div>
            </div>
            <div class="hero-stats-grid">
                <div class="hero-stat-card">
                    <small>NDVI Records</small>
                    <strong>{{ number_format($stats['total_ndvi_records']) }}</strong>
                </div>
                <div class="hero-stat-card">
                    <small>Processed</small>
                    <strong>{{ $stats['processed_datasets'] }}</strong>
                </div>
                <div class="hero-stat-card">
                    <small>Crop Cycles</small>
                    <strong>{{ $stats['total_crop_cycles'] }}</strong>
                </div>
                <div class="hero-stat-card">
                    <small>Active Alerts</small>
                    <strong>{{ $stats['unread_notifications'] }}</strong>
                </div>
            </div>
        </div>
    </section>

    <section>
        <div class="section-header">
            <h3>Operational Summary</h3>
        </div>
        <div class="grid-cols-4">
            <div class="stat-card stat-card-green">
                <div class="stat-icon stat-icon-green"><i class="bi bi-database"></i></div>
                <div class="stat-info">
                    <div class="stat-value">{{ $stats['total_datasets'] }}</div>
                    <div class="stat-label">Total Datasets</div>
                </div>
            </div >
            <div class="stat-card stat-card-blue">
                <div class="stat-icon stat-icon-blue"><i class="bi bi-cpu"></i></div>
                <div class="stat-info">
                    <div class="stat-value">{{ $stats['active_analyses'] }}</div>
                    <div class="stat-label">Active Analyses</div>
                </div>
            </div>
            <div class="stat-card stat-card-teal">
                <div class="stat-icon stat-icon-teal"><i class="bi bi-flower1"></i></div>
                <div class="stat-info">
                    <div class="stat-value">{{ $stats['total_crop_cycles'] }}</div>
                    <div class="stat-label">Crop Cycles</div>
                </div>
            </div>
            <div class="stat-card stat-card-amber">
                <div class="stat-icon stat-icon-amber"><i class="bi bi-bell"></i></div>
                <div class="stat-info">
                    <div class="stat-value">{{ $stats['alerts_count'] }}</div>
                    <div class="stat-label">Total Alerts</div>
                </div>
            </div>
        </div>
    </section>

    <div class="grid-cols-2">
        <section class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">NDVI Trends</h3>
                <span class="badge-pill badge-success">Live Signals</span>
            </div>
            <div class="card-body">
                <canvas id="ndviTrendChart" height="300"></canvas>
            </div>
        </section>
        <section class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Growth Stages</h3>
                <span class="badge-pill badge-info">Auto-detected</span>
            </div>
            <div class="card-body">
                <canvas id="growthStageChart" height="300"></canvas>
            </div>
        </section>
    </div>

    <section>
        <div class="section-header">
            <h3>Quick Workflows</h3>
        </div>
        <div class="grid-cols-4">
            <a href="{{ route('datasets.create') }}" class="quick-action-link">
                <div class="quick-action-icon stat-icon-green"><i class="bi bi-cloud-arrow-up"></i></div>
                <div>
                    <strong>Upload</strong>
                    <div class="text-muted small">New satellite data</div>
                </div>
            </a>
            <a href="{{ route('crop-cycles.create') }}" class="quick-action-link">
                <div class="quick-action-icon stat-icon-blue"><i class="bi bi-plus-circle"></i></div>
                <div>
                    <strong>Analyze</strong>
                    <div class="text-muted small">Extract parameters</div>
                </div>
            </a>
            <a href="{{ route('reports.index') }}" class="quick-action-link">
                <div class="quick-action-icon stat-icon-teal"><i class="bi bi-file-earmark-bar-graph"></i></div>
                <div>
                    <strong>Reports</strong>
                    <div class="text-muted small">View insights</div>
                </div>
            </a>
            <a href="{{ route('settings.profile') }}" class="quick-action-link">
                <div class="quick-action-icon stat-icon-amber"><i class="bi bi-gear"></i></div>
                <div>
                    <strong>Settings</strong>
                    <div class="text-muted small">Preferences</div>
                </div>
            </a>
        </div>
    </section>

    <section class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Recent Activity</h3>
            <a href="{{ route('reports.index') }}" class="btn-outline btn-sm">View History</a>
        </div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>Resource</th>
                        <th>Details</th>
                        <th>Status</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentActivities as $activity)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-{{ $activity['icon'] }} text-success"></i>
                                    <strong>{{ $activity['type'] }}</strong>
                                </div>
                            </td>
                            <td>{{ $activity['title'] }}</td>
                            <td class="text-muted">{{ $activity['meta'] }}</td>
                            <td><span class="badge-pill {{ $activity['status_class'] }}">{{ $activity['status'] }}</span></td>
                            <td class="text-muted small">{{ $activity['timestamp']->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">No recent activity recorded.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
const textColor = isDark ? '#8aa0bc' : '#64748b';
const gridColor = isDark ? 'rgba(148, 163, 184, 0.1)' : 'rgba(148, 163, 184, 0.2)';

Chart.defaults.color = textColor;
Chart.defaults.font.family = "'Inter', sans-serif";

const commonOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
        x: { grid: { display: false }, ticks: { color: textColor } },
        y: { grid: { color: gridColor }, ticks: { color: textColor } }
    }
};

new Chart(document.getElementById('ndviTrendChart'), {
    type: 'line',
    data: {
        labels: @json($ndviChartData['labels']),
        datasets: [{
            data: @json($ndviChartData['values']),
            borderColor: '#22c55e',
            backgroundColor: 'rgba(34, 197, 94, 0.1)',
            fill: true,
            tension: 0.4
        }]
    },
    options: commonOptions
});

new Chart(document.getElementById('growthStageChart'), {
    type: 'bar',
    data: {
        labels: @json($growthStageData['labels']),
        datasets: [{
            data: @json($growthStageData['values']),
            backgroundColor: ['#16a34a', '#22c55e', '#0f766e', '#2563eb', '#f59e0b'],
            borderRadius: 8
        }]
    },
    options: commonOptions
});
</script>
@endpush
