<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Extraction of Crop Cycle Parameters from Multi-Temporal Data for crop monitoring, NDVI analysis, and smart agriculture decisions.">
    <meta name="theme-color" content="#1f6f43">

    <title>{{ config('app.name', 'CropsCycle') }} | Extraction of Crop Cycle Parameters</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Sora:wght@500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --landing-bg: #f4f7ef;
            --landing-surface: rgba(255, 255, 255, 0.78);
            --landing-text: #0f172a;
            --landing-muted: #526071;
            --landing-border: rgba(15, 23, 42, 0.09);
            --landing-primary: #1f6f43;
            --landing-primary-dark: #164e31;
            --landing-accent: #d6ef78;
            --landing-gold: #d4a72c;
            --landing-shadow: 0 30px 70px rgba(31, 111, 67, 0.15);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: 'Manrope', sans-serif;
            color: var(--landing-text);
            background:
                radial-gradient(circle at top left, rgba(214, 239, 120, 0.55), transparent 28%),
                radial-gradient(circle at top right, rgba(65, 152, 110, 0.18), transparent 26%),
                linear-gradient(180deg, #f8fbf4 0%, #eef5ec 58%, #f8fafc 100%);
        }

        a { color: inherit; text-decoration: none; }

        .landing-shell {
            min-height: 100vh;
            position: relative;
            overflow: hidden;
        }

        .landing-shell::before,
        .landing-shell::after {
            content: "";
            position: absolute;
            border-radius: 999px;
            filter: blur(12px);
            opacity: 0.45;
            pointer-events: none;
        }

        .landing-shell::before {
            width: 320px;
            height: 320px;
            background: rgba(31, 111, 67, 0.12);
            top: 4rem;
            left: -6rem;
        }

        .landing-shell::after {
            width: 280px;
            height: 280px;
            background: rgba(212, 167, 44, 0.12);
            right: -4rem;
            bottom: 8rem;
        }

        .container {
            width: min(1180px, calc(100% - 2rem));
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1.4rem 0;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 0.9rem;
            font-weight: 800;
        }

        .brand-mark {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: linear-gradient(135deg, #163a24, #2d8d56);
            display: grid;
            place-items: center;
            color: #fff;
            box-shadow: 0 18px 30px rgba(31, 111, 67, 0.24);
        }

        .brand-mark svg {
            width: 24px;
            height: 24px;
        }

        .brand-copy small {
            display: block;
            color: var(--landing-muted);
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }

        .brand-copy strong {
            display: block;
            font-family: 'Sora', sans-serif;
            font-size: 1rem;
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .chip-link {
            padding: 0.72rem 1rem;
            border-radius: 999px;
            border: 1px solid var(--landing-border);
            background: rgba(255, 255, 255, 0.58);
            color: var(--landing-muted);
            font-size: 0.95rem;
            font-weight: 700;
            backdrop-filter: blur(16px);
        }

        .chip-link:hover { color: var(--landing-primary); border-color: rgba(31, 111, 67, 0.28); }

        .chip-link.primary {
            background: linear-gradient(135deg, var(--landing-primary), #2a925b);
            color: #fff;
            border-color: transparent;
            box-shadow: 0 16px 28px rgba(31, 111, 67, 0.24);
        }

        .hero {
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 2rem;
            padding: 3rem 0 2rem;
            align-items: center;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            padding: 0.5rem 0.8rem;
            border-radius: 999px;
            background: rgba(214, 239, 120, 0.55);
            color: var(--landing-primary-dark);
            font-size: 0.84rem;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .hero h1 {
            margin: 1.1rem 0 1rem;
            font-family: 'Sora', sans-serif;
            font-size: clamp(2.6rem, 5vw, 4.9rem);
            line-height: 1.02;
            letter-spacing: -0.05em;
        }

        .hero h1 span {
            color: var(--landing-primary);
            display: block;
        }

        .hero p {
            margin: 0;
            max-width: 62ch;
            color: var(--landing-muted);
            font-size: 1.05rem;
            line-height: 1.8;
        }

        .hero-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1.75rem;
            flex-wrap: wrap;
        }

        .cta-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            padding: 0.95rem 1.35rem;
            border-radius: 1rem;
            font-weight: 800;
            font-size: 0.98rem;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .cta-btn:hover {
            transform: translateY(-2px);
        }

        .cta-btn.primary {
            background: linear-gradient(135deg, var(--landing-primary), #2a925b);
            color: #fff;
            box-shadow: 0 18px 32px rgba(31, 111, 67, 0.22);
        }

        .cta-btn.secondary {
            border: 1px solid var(--landing-border);
            background: rgba(255, 255, 255, 0.68);
            color: var(--landing-text);
            backdrop-filter: blur(14px);
        }

        .hero-metrics {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.9rem;
            margin-top: 2rem;
        }

        .metric-card {
            padding: 1rem 1.1rem;
            border-radius: 1.1rem;
            background: rgba(255, 255, 255, 0.72);
            border: 1px solid rgba(255, 255, 255, 0.65);
            box-shadow: var(--landing-shadow);
            backdrop-filter: blur(14px);
        }

        .metric-card strong {
            display: block;
            font-size: 1.45rem;
            font-weight: 800;
        }

        .metric-card span {
            display: block;
            margin-top: 0.35rem;
            color: var(--landing-muted);
            font-size: 0.9rem;
        }

        .hero-panel {
            padding: 1.25rem;
            border-radius: 1.8rem;
            background: linear-gradient(180deg, rgba(15, 23, 42, 0.96), rgba(21, 46, 32, 0.94));
            color: #f8fafc;
            box-shadow: 0 30px 70px rgba(15, 23, 42, 0.25);
            position: relative;
            overflow: hidden;
        }

        .hero-panel::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at top right, rgba(214, 239, 120, 0.18), transparent 28%),
                linear-gradient(135deg, transparent 0%, rgba(255, 255, 255, 0.04) 100%);
            pointer-events: none;
        }

        .hero-panel-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.1rem;
            position: relative;
            z-index: 1;
        }

        .panel-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.45rem 0.75rem;
            border-radius: 999px;
            background: rgba(214, 239, 120, 0.12);
            color: #d6ef78;
            font-size: 0.8rem;
            font-weight: 700;
        }

        .signal-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
            position: relative;
            z-index: 1;
        }

        .signal-card {
            padding: 1rem;
            border-radius: 1.15rem;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .signal-card small,
        .timeline-card small {
            display: block;
            color: rgba(255, 255, 255, 0.62);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-size: 0.72rem;
            margin-bottom: 0.55rem;
        }

        .signal-card strong {
            font-size: 1.5rem;
            display: block;
        }

        .timeline-card {
            margin-top: 1rem;
            padding: 1rem;
            border-radius: 1.15rem;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.08);
            position: relative;
            z-index: 1;
        }

        .timeline-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 0.55rem 0;
            font-size: 0.94rem;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }

        .timeline-row:first-of-type { border-top: none; }

        .timeline-row span:last-child {
            color: #d6ef78;
            font-weight: 700;
        }

        .section {
            padding: 2rem 0;
        }

        .section-heading {
            max-width: 700px;
            margin-bottom: 1.6rem;
        }

        .section-heading span {
            display: inline-block;
            color: var(--landing-primary);
            font-weight: 800;
            font-size: 0.84rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 0.65rem;
        }

        .section-heading h2 {
            margin: 0;
            font-family: 'Sora', sans-serif;
            font-size: clamp(1.9rem, 3vw, 3rem);
            line-height: 1.1;
        }

        .section-heading p {
            margin: 0.8rem 0 0;
            color: var(--landing-muted);
            line-height: 1.8;
        }

        .feature-grid,
        .flow-grid,
        .role-grid {
            display: grid;
            gap: 1rem;
        }

        .feature-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .flow-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .role-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .glass-card {
            padding: 1.35rem;
            border-radius: 1.35rem;
            background: var(--landing-surface);
            border: 1px solid rgba(255, 255, 255, 0.65);
            backdrop-filter: blur(16px);
            box-shadow: var(--landing-shadow);
        }

        .glass-card h3 {
            margin: 0.85rem 0 0.55rem;
            font-family: 'Sora', sans-serif;
            font-size: 1.05rem;
        }

        .glass-card p {
            margin: 0;
            color: var(--landing-muted);
            line-height: 1.75;
            font-size: 0.94rem;
        }

        .icon-pill {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, rgba(31, 111, 67, 0.12), rgba(214, 239, 120, 0.65));
            color: var(--landing-primary-dark);
            font-size: 1.3rem;
            font-weight: 900;
        }

        .list-clean {
            list-style: none;
            padding: 0;
            margin: 1rem 0 0;
            display: grid;
            gap: 0.65rem;
        }

        .list-clean li {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            color: var(--landing-muted);
            font-size: 0.94rem;
        }

        .list-clean i {
            width: 22px;
            height: 22px;
            border-radius: 999px;
            background: rgba(31, 111, 67, 0.12);
            color: var(--landing-primary);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-style: normal;
            font-weight: 800;
            flex-shrink: 0;
        }

        .cta-strip {
            margin: 2rem 0 3.5rem;
            padding: 1.6rem;
            border-radius: 1.8rem;
            background: linear-gradient(135deg, #163a24, #255b38 58%, #c8df6d 180%);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            box-shadow: 0 28px 60px rgba(22, 58, 36, 0.24);
        }

        .cta-strip h3 {
            margin: 0 0 0.35rem;
            font-family: 'Sora', sans-serif;
            font-size: 1.5rem;
        }

        .cta-strip p {
            margin: 0;
            color: rgba(255, 255, 255, 0.8);
        }

        .footer-note {
            padding: 0 0 2rem;
            text-align: center;
            color: var(--landing-muted);
            font-size: 0.92rem;
        }

        @media (max-width: 1024px) {
            .hero,
            .feature-grid,
            .flow-grid,
            .role-grid {
                grid-template-columns: 1fr;
            }

            .hero {
                padding-top: 2rem;
            }

            .hero-panel {
                order: -1;
            }

            .cta-strip {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        @media (max-width: 640px) {
            .container { width: min(100% - 1.25rem, 1180px); }
            .topbar { align-items: flex-start; flex-direction: column; }
            .hero-metrics,
            .signal-grid { grid-template-columns: 1fr; }
            .hero h1 { font-size: 2.4rem; }
            .cta-btn,
            .chip-link { width: 100%; }
            .hero-actions,
            .topbar-actions { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="landing-shell">
        <div class="container">
            <header class="topbar">
                <a href="{{ route('home') }}" class="brand">
                    <span class="brand-mark" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 3 4 7.5v9L12 21l8-4.5v-9L12 3Z"/>
                            <path d="M12 12 4 7.5M12 12l8-4.5M12 12v9"/>
                        </svg>
                    </span>
                    <span class="brand-copy">
                        <small>Smart Agriculture Intelligence</small>
                        <strong>Extraction of Crop Cycle Parameters</strong>
                    </span>
                </a>

                <div class="topbar-actions">
                    @auth
                        <a href="{{ route('dashboard') }}" class="chip-link">Open Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="chip-link">Research Portal</a>
                        <a href="{{ route('register') }}" class="chip-link primary">Create Account</a>
                    @endauth
                </div>
            </header>

            <section class="hero">
                <div>
                    <div class="eyebrow">Multi-Temporal Satellite Analytics</div>
                    <h1>
                        Turn raw remote sensing data into
                        <span>actionable crop cycle intelligence.</span>
                    </h1>
                    <p>
                        This platform automates extraction of sowing date, vegetation growth stages, peak growth,
                        harvesting time, and NDVI trends from multi-temporal datasets. It is built for monitoring crops,
                        improving yield prediction, and guiding real-world agricultural decisions.
                    </p>

                    <div class="hero-actions">
                        @auth
                            <a href="{{ route('dashboard') }}" class="cta-btn primary">Go to Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="cta-btn primary">Launch Monitoring Workspace</a>
                            <a href="{{ route('register') }}" class="cta-btn secondary">Register as Researcher or Farmer</a>
                        @endauth
                    </div>

                    <div class="hero-metrics">
                        <div class="metric-card">
                            <strong>NDVI</strong>
                            <span>Seasonal vegetation trend analysis</span>
                        </div>
                        <div class="metric-card">
                            <strong>CSV + GeoTIFF</strong>
                            <span>Flexible ingestion for temporal datasets</span>
                        </div>
                        <div class="metric-card">
                            <strong>Yield AI</strong>
                            <span>Prediction and irrigation guidance simulation</span>
                        </div>
                    </div>
                </div>

                <div class="hero-panel">
                    <div class="hero-panel-top">
                        <div>
                            <div class="panel-badge">Live Crop Cycle Snapshot</div>
                            <h2 style="margin:0.85rem 0 0; font-family:'Sora', sans-serif; font-size:1.7rem;">Season Monitoring Console</h2>
                        </div>
                        <div style="text-align:right;">
                            <small style="display:block; color:rgba(255,255,255,0.58); text-transform:uppercase; letter-spacing:0.08em;">Status</small>
                            <strong style="color:#d6ef78; font-size:1rem;">Processing Stable</strong>
                        </div>
                    </div>

                    <div class="signal-grid">
                        <div class="signal-card">
                            <small>Sowing Detection</small>
                            <strong>12 Jun</strong>
                            <div style="margin-top:0.45rem; color:rgba(255,255,255,0.7);">Detected from early NDVI rise</div>
                        </div>
                        <div class="signal-card">
                            <small>Peak Growth</small>
                            <strong>0.84 NDVI</strong>
                            <div style="margin-top:0.45rem; color:rgba(255,255,255,0.7);">Maximum canopy vigor recorded</div>
                        </div>
                    </div>

                    <div class="timeline-card">
                        <small>Cycle Parameters</small>
                        <div class="timeline-row"><span>Vegetative Stage</span><span>Detected</span></div>
                        <div class="timeline-row"><span>Reproductive Stage</span><span>Forecast Ready</span></div>
                        <div class="timeline-row"><span>Harvest Window</span><span>03 Oct - 12 Oct</span></div>
                        <div class="timeline-row"><span>Yield Prediction</span><span>4.7 t/ha</span></div>
                    </div>
                </div>
            </section>

            <section class="section">
                <div class="section-heading">
                    <span>Core Modules</span>
                    <h2>Everything needed for an industry-grade crop intelligence workflow.</h2>
                    <p>
                        The application supports secure user access, dashboard analytics, dataset ingestion,
                        crop-cycle extraction, report generation, API integrations, and export-ready research outputs.
                    </p>
                </div>

                <div class="feature-grid">
                    <article class="glass-card">
                        <div class="icon-pill">01</div>
                        <h3>Authentication and Role Control</h3>
                        <p>Support for Admin, Researcher, and Farmer roles with protected workflows, password reset, and secure access boundaries.</p>
                        <ul class="list-clean">
                            <li><i>✓</i>User registration and login</li>
                            <li><i>✓</i>Optional 2FA-ready profile model</li>
                            <li><i>✓</i>Role-scoped access management</li>
                        </ul>
                    </article>

                    <article class="glass-card">
                        <div class="icon-pill">02</div>
                        <h3>NDVI and Crop Cycle Extraction</h3>
                        <p>Upload temporal datasets, simulate NDVI processing, and derive sowing, stage transitions, peak vigor, and harvest windows.</p>
                        <ul class="list-clean">
                            <li><i>✓</i>CSV and GeoTIFF dataset support</li>
                            <li><i>✓</i>Growth stage timeline detection</li>
                            <li><i>✓</i>Graph and table result views</li>
                        </ul>
                    </article>

                    <article class="glass-card">
                        <div class="icon-pill">03</div>
                        <h3>Analytics, Reports, and API</h3>
                        <p>Interactive charts, seasonal summaries, export tools, notifications, and Sanctum-secured APIs for external systems.</p>
                        <ul class="list-clean">
                            <li><i>✓</i>Chart.js trend dashboards</li>
                            <li><i>✓</i>PDF and Excel export workflows</li>
                            <li><i>✓</i>REST API for all major modules</li>
                        </ul>
                    </article>
                </div>
            </section>

            <section class="section">
                <div class="section-heading">
                    <span>Processing Flow</span>
                    <h2>From satellite observations to field-level decisions.</h2>
                </div>

                <div class="flow-grid">
                    <article class="glass-card">
                        <div class="icon-pill">A</div>
                        <h3>Upload</h3>
                        <p>Ingest multi-temporal files and regional metadata into a structured dataset pipeline.</p>
                    </article>
                    <article class="glass-card">
                        <div class="icon-pill">B</div>
                        <h3>Process</h3>
                        <p>Generate NDVI-like temporal signals and normalize them for crop-cycle interpretation.</p>
                    </article>
                    <article class="glass-card">
                        <div class="icon-pill">C</div>
                        <h3>Extract</h3>
                        <p>Identify sowing date, growth stages, peak vigor, and harvest timing from trend patterns.</p>
                    </article>
                    <article class="glass-card">
                        <div class="icon-pill">D</div>
                        <h3>Decide</h3>
                        <p>Use reports, yield predictions, and irrigation suggestions for smarter interventions.</p>
                    </article>
                </div>
            </section>

            <section class="section">
                <div class="section-heading">
                    <span>Who It Serves</span>
                    <h2>Designed for admins, agricultural researchers, and farmers.</h2>
                </div>

                <div class="role-grid">
                    <article class="glass-card">
                        <div class="icon-pill">AD</div>
                        <h3>Admins</h3>
                        <p>Manage users, monitor processed records, review system activity, and maintain export pipelines across the platform.</p>
                    </article>
                    <article class="glass-card">
                        <div class="icon-pill">RS</div>
                        <h3>Researchers</h3>
                        <p>Study vegetation behavior over time, compare regions, validate growth stage extraction, and produce analytical reports.</p>
                    </article>
                    <article class="glass-card">
                        <div class="icon-pill">FM</div>
                        <h3>Farmers</h3>
                        <p>Track seasonal progress, interpret yield forecasts, receive alerts, and apply field decisions with clearer timing.</p>
                    </article>
                </div>
            </section>

            <section class="cta-strip">
                <div>
                    <h3>Ready to modernize crop monitoring?</h3>
                    <p>Access the platform to manage datasets, monitor NDVI trends, and generate crop intelligence reports.</p>
                </div>
                <div class="hero-actions" style="margin-top:0;">
                    @auth
                        <a href="{{ route('dashboard') }}" class="cta-btn primary">Open Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="cta-btn primary">Sign In</a>
                        <a href="{{ route('register') }}" class="cta-btn secondary" style="background:rgba(255,255,255,0.14); color:#fff; border-color:rgba(255,255,255,0.18);">Create Account</a>
                    @endauth
                </div>
            </section>

            <div class="footer-note">
                Built with Laravel for scalable crop monitoring, yield prediction, and smart agriculture decision support.
            </div>
        </div>
    </div>
</body>
</html>
