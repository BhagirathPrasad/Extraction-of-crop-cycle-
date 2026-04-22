@props(['title' => 'Secure Access'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Crop cycle intelligence portal for multi-temporal satellite analytics, NDVI trends, and smart agriculture decisions.">

    <title>{{ config('app.name', 'CropsCycle') }} | {{ $title }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Sora:wght@500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --guest-bg: #eef4eb;
            --guest-surface: rgba(255, 255, 255, 0.82);
            --guest-panel: linear-gradient(180deg, rgba(10, 21, 17, 0.96), rgba(22, 58, 36, 0.94));
            --guest-text: #0f172a;
            --guest-muted: #5b6978;
            --guest-border: rgba(15, 23, 42, 0.1);
            --guest-primary: #1f6f43;
            --guest-primary-dark: #163a24;
            --guest-primary-soft: rgba(31, 111, 67, 0.1);
            --guest-accent: #d9ee84;
            --guest-danger: #b91c1c;
            --guest-shadow: 0 28px 60px rgba(22, 58, 36, 0.14);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            color: var(--guest-text);
            font-family: 'Manrope', sans-serif;
            background:
                radial-gradient(circle at top left, rgba(217, 238, 132, 0.58), transparent 24%),
                radial-gradient(circle at bottom right, rgba(31, 111, 67, 0.14), transparent 24%),
                linear-gradient(180deg, #f7faf4 0%, #edf4eb 52%, #f8fafc 100%);
        }

        a { color: inherit; text-decoration: none; }

        .guest-shell {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.25rem;
        }

        .guest-frame {
            width: min(1180px, 100%);
            display: grid;
            grid-template-columns: 1.05fr 0.95fr;
            background: rgba(255, 255, 255, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.72);
            border-radius: 2rem;
            overflow: hidden;
            box-shadow: var(--guest-shadow);
            backdrop-filter: blur(18px);
        }

        .guest-aside {
            padding: 2rem;
            background: var(--guest-panel);
            color: #f8fafc;
            position: relative;
            overflow: hidden;
        }

        .guest-aside::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at top right, rgba(217, 238, 132, 0.18), transparent 26%),
                linear-gradient(135deg, rgba(255, 255, 255, 0.02), transparent 55%);
            pointer-events: none;
        }

        .guest-aside > * { position: relative; z-index: 1; }

        .brand-link {
            display: inline-flex;
            align-items: center;
            gap: 0.9rem;
            margin-bottom: 2rem;
        }

        .brand-badge {
            width: 50px;
            height: 50px;
            border-radius: 15px;
            background: linear-gradient(135deg, #1f6f43, #67b26d);
            display: grid;
            place-items: center;
            box-shadow: 0 18px 30px rgba(31, 111, 67, 0.28);
        }

        .brand-badge svg { width: 25px; height: 25px; }

        .brand-title small {
            display: block;
            color: rgba(255, 255, 255, 0.62);
            text-transform: uppercase;
            letter-spacing: 0.09em;
            font-size: 0.74rem;
            font-weight: 700;
            margin-bottom: 0.2rem;
        }

        .brand-title strong {
            display: block;
            font-family: 'Sora', sans-serif;
            font-size: 1rem;
        }

        .guest-kicker {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0.8rem;
            border-radius: 999px;
            background: rgba(217, 238, 132, 0.12);
            color: var(--guest-accent);
            font-size: 0.8rem;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .guest-aside h1 {
            margin: 1.2rem 0 0.9rem;
            font-family: 'Sora', sans-serif;
            font-size: clamp(2.2rem, 4vw, 3.7rem);
            line-height: 1.04;
            letter-spacing: -0.05em;
        }

        .guest-aside p {
            margin: 0;
            color: rgba(255, 255, 255, 0.72);
            line-height: 1.8;
            font-size: 1rem;
            max-width: 58ch;
        }

        .aside-metrics {
            margin-top: 1.8rem;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.85rem;
        }

        .aside-metric {
            padding: 1rem;
            border-radius: 1.15rem;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .aside-metric strong {
            display: block;
            color: #fff;
            font-size: 1.25rem;
            margin-bottom: 0.3rem;
        }

        .aside-metric span {
            color: rgba(255, 255, 255, 0.64);
            font-size: 0.84rem;
            line-height: 1.5;
        }

        .insight-card {
            margin-top: 1.4rem;
            padding: 1.1rem;
            border-radius: 1.2rem;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .insight-card small {
            display: block;
            color: rgba(255, 255, 255, 0.55);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-size: 0.72rem;
            margin-bottom: 0.75rem;
        }

        .insight-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.5rem 0;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            color: #f8fafc;
            font-size: 0.95rem;
        }

        .insight-row:first-of-type { border-top: none; }

        .insight-row span:last-child {
            color: var(--guest-accent);
            font-weight: 700;
        }

        .guest-main {
            padding: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.34);
        }

        .guest-card {
            width: min(480px, 100%);
            padding: 2rem;
            border-radius: 1.6rem;
            background: var(--guest-surface);
            border: 1px solid rgba(255, 255, 255, 0.72);
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
            backdrop-filter: blur(20px);
        }

        .guest-card-head {
            margin-bottom: 1.5rem;
        }

        .guest-card-head .eyebrow {
            display: inline-block;
            color: var(--guest-primary);
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 0.65rem;
        }

        .guest-card-head h2 {
            margin: 0;
            font-family: 'Sora', sans-serif;
            font-size: 1.7rem;
            line-height: 1.15;
        }

        .guest-card-head p {
            margin: 0.7rem 0 0;
            color: var(--guest-muted);
            line-height: 1.75;
            font-size: 0.95rem;
        }

        .auth-stack {
            display: grid;
            gap: 1rem;
        }

        .field-group {
            display: grid;
            gap: 0.45rem;
        }

        .field-row {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        .field-label {
            font-size: 0.88rem;
            font-weight: 700;
            color: #1e293b;
        }

        .field-input {
            width: 100%;
            border-radius: 0.95rem;
            border: 1px solid var(--guest-border);
            background: rgba(255, 255, 255, 0.92);
            padding: 0.95rem 1rem;
            font: inherit;
            color: var(--guest-text);
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
        }

        .field-input:focus {
            border-color: rgba(31, 111, 67, 0.45);
            box-shadow: 0 0 0 4px rgba(31, 111, 67, 0.12);
            transform: translateY(-1px);
        }

        .field-help,
        .field-error {
            font-size: 0.82rem;
            line-height: 1.55;
        }

        .field-help { color: var(--guest-muted); }
        .field-error { color: var(--guest-danger); }

        .auth-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .checkbox-wrap {
            display: inline-flex;
            align-items: center;
            gap: 0.65rem;
            color: var(--guest-muted);
            font-size: 0.92rem;
        }

        .checkbox-wrap input {
            width: 1rem;
            height: 1rem;
            accent-color: var(--guest-primary);
        }

        .text-link {
            color: var(--guest-primary);
            font-weight: 700;
        }

        .text-link:hover { color: var(--guest-primary-dark); }

        .auth-button {
            width: 100%;
            border: none;
            border-radius: 1rem;
            background: linear-gradient(135deg, var(--guest-primary), #2a925b);
            color: #fff;
            padding: 1rem 1.25rem;
            font: inherit;
            font-weight: 800;
            cursor: pointer;
            box-shadow: 0 18px 30px rgba(31, 111, 67, 0.18);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .auth-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 22px 36px rgba(31, 111, 67, 0.22);
        }

        .auth-footer {
            margin-top: 1.1rem;
            text-align: center;
            color: var(--guest-muted);
            font-size: 0.92rem;
        }

        .status-banner {
            margin-bottom: 1rem;
            border-radius: 1rem;
            padding: 0.9rem 1rem;
            background: rgba(31, 111, 67, 0.08);
            border: 1px solid rgba(31, 111, 67, 0.14);
            color: var(--guest-primary-dark);
            font-size: 0.92rem;
            line-height: 1.65;
        }

        @media (max-width: 1024px) {
            .guest-frame {
                grid-template-columns: 1fr;
            }

            .guest-main {
                padding-top: 0;
            }
        }

        @media (max-width: 640px) {
            .guest-shell,
            .guest-aside,
            .guest-main,
            .guest-card { padding: 1.2rem; }

            .field-row,
            .aside-metrics { grid-template-columns: 1fr; }

            .auth-meta { align-items: flex-start; flex-direction: column; }
        }
    </style>

    @stack('styles')
</head>
<body>
    <div class="guest-shell">
        <div class="guest-frame">
            <aside class="guest-aside">
                <a href="{{ route('home') }}" class="brand-link">
                    <span class="brand-badge" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 3 4 7.5v9L12 21l8-4.5v-9L12 3Z"/>
                            <path d="M12 12 4 7.5M12 12l8-4.5M12 12v9"/>
                        </svg>
                    </span>
                    <span class="brand-title">
                        <small>Crop Monitoring Platform</small>
                        <strong>Extraction of Crop Cycle Parameters</strong>
                    </span>
                </a>

                <div class="guest-kicker">Secure Research Workspace</div>
                <h1>Monitor the full crop season from sowing signal to harvest timing.</h1>
                <p>
                    Access dataset ingestion, NDVI analysis, crop-cycle extraction, report generation,
                    role-based administration, and decision-ready agriculture insights in one Laravel platform.
                </p>

                <div class="aside-metrics">
                    <div class="aside-metric">
                        <strong>Dashboard</strong>
                        <span>Charts, seasonal trends, and crop summaries</span>
                    </div>
                    <div class="aside-metric">
                        <strong>Datasets</strong>
                        <span>CSV and GeoTIFF processing workflows</span>
                    </div>
                    <div class="aside-metric">
                        <strong>Reports</strong>
                        <span>Exports, alerts, and API-driven analytics</span>
                    </div>
                </div>

                <div class="insight-card">
                    <small>Platform Highlights</small>
                    <div class="insight-row"><span>Sowing Date Detection</span><span>Automated</span></div>
                    <div class="insight-row"><span>Peak NDVI Tracking</span><span>Interactive</span></div>
                    <div class="insight-row"><span>Yield Prediction</span><span>Simulation Ready</span></div>
                    <div class="insight-row"><span>Sanctum API Access</span><span>Enabled</span></div>
                </div>
            </aside>

            <main class="guest-main">
                <div class="guest-card">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
