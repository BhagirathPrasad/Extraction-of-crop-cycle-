<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1a1a1a; margin: 0; padding: 20px; }
    .header { background: #166534; color: white; padding: 16px 20px; border-radius: 6px; margin-bottom: 20px; }
    .header h1 { margin: 0; font-size: 18px; }
    .header p  { margin: 4px 0 0; opacity: .8; font-size: 11px; }
    .meta { display: flex; gap: 20px; margin-bottom: 16px; font-size: 10px; color: #64748b; }
    table { width: 100%; border-collapse: collapse; }
    th { background: #f0fdf4; border: 1px solid #bbf7d0; padding: 7px 10px; text-align: left; font-weight: 700; font-size: 9px; text-transform: uppercase; color: #166534; }
    td { border: 1px solid #e2e8f0; padding: 6px 10px; vertical-align: middle; }
    tr:nth-child(even) td { background: #f8fafc; }
    .badge { padding: 2px 8px; border-radius: 999px; font-size: 9px; font-weight: 700; }
    .badge-high   { background: #dcfce7; color: #166534; }
    .badge-medium { background: #fef3c7; color: #92400e; }
    .badge-low    { background: #fee2e2; color: #991b1b; }
    .footer { margin-top: 20px; font-size: 9px; color: #94a3b8; text-align: center; border-top: 1px solid #e2e8f0; padding-top: 10px; }
    .section-title { font-size: 13px; font-weight: 700; color: #166534; margin: 16px 0 8px; }
</style>
</head>
<body>

<div class="header">
    <h1>🌾 CropsCycle — Crop Cycle Parameters Report</h1>
    <p>Generated: {{ now()->format('M d, Y H:i') }} | User: {{ auth()->user()->name }}</p>
</div>

@if($filters)
<div class="meta">
    @foreach(array_filter($filters) as $key => $val)
        <span><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> {{ $val }}</span>
    @endforeach
</div>
@endif

<h2 class="section-title">Crop Cycle Summary ({{ $cropCycles->count() }} records)</h2>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Crop Type</th>
            <th>Region</th>
            <th>Season</th>
            <th>Sowing Date</th>
            <th>Harvest Date</th>
            <th>Days</th>
            <th>NDVI Max</th>
            <th>Yield (kg/ha)</th>
            <th>Category</th>
        </tr>
    </thead>
    <tbody>
        @forelse($cropCycles as $c)
        <tr>
            <td>{{ $c->id }}</td>
            <td><strong>{{ ucfirst($c->crop_type) }}</strong></td>
            <td>{{ $c->region }}</td>
            <td>{{ $c->season }} {{ $c->season_year }}</td>
            <td>{{ $c->sowing_date?->format('Y-m-d') ?? '—' }}</td>
            <td>{{ $c->harvest_date?->format('Y-m-d') ?? '—' }}</td>
            <td>{{ $c->growing_days ?? '—' }}</td>
            <td>{{ $c->ndvi_max ?? '—' }}</td>
            <td>{{ $c->yield_prediction ? number_format($c->yield_prediction, 0) : '—' }}</td>
            <td>
                @if($c->yield_category)
                    <span class="badge badge-{{ $c->yield_category }}">{{ ucfirst($c->yield_category) }}</span>
                @else —
                @endif
            </td>
        </tr>
        @empty
        <tr><td colspan="10" style="text-align:center; color:#94a3b8;">No records found</td></tr>
        @endforelse
    </tbody>
</table>

<div class="footer">
    CropsCycle — Satellite Crop Intelligence Platform | Confidential Report | Page 1 of 1
</div>

</body>
</html>
