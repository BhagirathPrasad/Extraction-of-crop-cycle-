<div class="table-wrapper">
    <table class="data-table">
        <thead>
            <tr>
                <th>Crop</th>
                <th>Region (Location)</th>
                <th>Season</th>
                <th>Soil Type</th>
                <th>Sowing Date</th>
                <th>Harvest Date</th>
                <th>Growing Days</th>
                <th>NDVI Peak</th>
                <th>Yield Prediction</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($results as $cycle)
            <tr>
                <td>
                    <span style="font-weight:600; color: var(--text-primary);">{{ ucfirst($cycle->crop_type) }}</span>
                    @if($cycle->variety)
                        <div style="font-size:11px;color:var(--text-muted);">{{ $cycle->variety }}</div>
                    @endif
                </td>
                <td>
                    <div style="display:flex; align-items:center; gap:6px;">
                        <i class="bi bi-geo-alt-fill text-muted" style="font-size: 13px;"></i>
                        <span>{{ $cycle->region }}</span>
                    </div>
                </td>
                <td>
                    <span class="badge-pill badge-secondary" style="font-size: 0.72rem;">
                        {{ $cycle->season }} {{ $cycle->season_year }}
                    </span>
                </td>
                <td>
                    <span style="font-weight: 500;">
                        {{ ucfirst($cycle->soil_type ?? 'N/A') }}
                    </span>
                </td>
                <td>{{ $cycle->sowing_date?->format('M d, Y') ?? '—' }}</td>
                <td>{{ $cycle->harvest_date?->format('M d, Y') ?? '—' }}</td>
                <td>
                    @if($cycle->growing_days)
                        <span style="font-weight:600;">{{ $cycle->growing_days }}</span> <small class="text-muted">days</small>
                    @else
                        —
                    @endif
                </td>
                <td>
                    @if($cycle->ndvi_max)
                        <div style="display:flex; align-items:center; gap:8px;">
                            <div class="ndvi-bar" style="width:60px; height: 6px; background: rgba(0,0,0,0.06); border-radius: 99px; overflow: hidden;">
                                <div class="ndvi-fill {{ $cycle->ndvi_max > 0.6 ? 'ndvi-fill-good' : ($cycle->ndvi_max > 0.35 ? 'ndvi-fill-medium' : 'ndvi-fill-low') }}"
                                     style="height: 100%; width:{{ min(round($cycle->ndvi_max * 100), 100) }}%; background: var(--brand-green);"></div>
                            </div>
                            <span style="font-size:12px; font-weight:600;">{{ number_format($cycle->ndvi_max, 2) }}</span>
                        </div>
                    @else
                        —
                    @endif
                </td>
                <td>
                    @if($cycle->yield_prediction)
                        <span class="badge-pill {{ $cycle->yield_badge_class }}">
                            {{ number_format($cycle->yield_prediction, 0) }} {{ $cycle->yield_unit }}
                        </span>
                    @else
                        —
                    @endif
                </td>
                <td>
                    <div style="display:flex; gap:5px;">
                        <a href="{{ route('crop-cycles.show', $cycle) }}" class="btn-outline btn-sm" title="View Details"><i class="bi bi-eye"></i></a>
                        <a href="{{ route('crop-cycles.edit', $cycle) }}" class="btn-outline btn-sm" title="Edit Cycle"><i class="bi bi-pencil"></i></a>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10">
                    <div class="empty-state" style="padding: 4rem 2rem;">
                        <div class="empty-state-icon" style="color: var(--text-muted);"><i class="bi bi-search-heart"></i></div>
                        <h4 style="font-family: var(--font-heading);">No Results Found</h4>
                        <p style="color: var(--text-muted); margin-bottom: 1.5rem;">We couldn't find any crop cycles matching your criteria. Try adjusting your filter parameters or search keyword.</p>
                        <button type="button" onclick="document.getElementById('clearFiltersBtn').click()" class="btn-primary-green">Reset Filters</button>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
