@extends('layouts.app')
@section('title', 'Search Crop Cycles')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h2>🔍 Search Crop Cycles</h2>
        <p>Search and filter crop cycle parameters extracted from multi-temporal satellite data.</p>
    </div>
</div>

<div class="grid-cols-4" style="grid-template-columns: 300px 1fr; gap: 1.5rem; align-items: start;">
    <!-- Filters Sidebar -->
    <div class="card" style="position: sticky; top: calc(var(--topbar-height) + 1.5rem);">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin:0;"><i class="bi bi-funnel-fill text-success"></i> Filters</h3>
            <button type="button" id="clearFiltersBtn" class="btn-outline btn-sm" style="padding: 4px 10px; border-radius: 8px; font-size: 0.78rem;">Clear All</button>
        </div>
        <div class="card-body">
            <form id="searchFiltersForm" onsubmit="return false;" style="display: grid; gap: 1rem;">
                <!-- Query -->
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label" for="searchQ">Keyword Search</label>
                    <div style="position: relative;">
                        <input type="text" id="searchQ" name="q" class="form-control" placeholder="Search anything..." value="{{ request('q') }}" style="padding-left: 2.2rem;">
                        <i class="bi bi-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                    </div>
                </div>

                <!-- Crop Type -->
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label" for="filterCrop">Crop Name</label>
                    <select id="filterCrop" name="crop_type" class="filter-select">
                        <option value="">All Crops</option>
                        @foreach($cropTypes as $type)
                            <option value="{{ $type }}" {{ request('crop_type') === $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Season -->
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label" for="filterSeason">Season</label>
                    <select id="filterSeason" name="season" class="filter-select">
                        <option value="">All Seasons</option>
                        @foreach($seasons as $season)
                            <option value="{{ $season }}" {{ request('season') === $season ? 'selected' : '' }}>{{ $season }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Soil Type -->
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label" for="filterSoil">Soil Type</label>
                    <select id="filterSoil" name="soil_type" class="filter-select">
                        <option value="">All Soils</option>
                        @foreach($soilTypes as $soil)
                            <option value="{{ $soil }}" {{ request('soil_type') === $soil ? 'selected' : '' }}>{{ ucfirst($soil) }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Location / Region -->
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label" for="filterRegion">Location</label>
                    <select id="filterRegion" name="region" class="filter-select">
                        <option value="">All Locations</option>
                        @foreach($regions as $region)
                            <option value="{{ $region }}" {{ request('region') === $region ? 'selected' : '' }}>{{ $region }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- NDVI Max Range -->
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">NDVI Max range</label>
                    <div style="display: flex; gap: 8px;">
                        <input type="number" step="0.05" min="0" max="1" name="ndvi_min" id="ndviMin" class="form-control" placeholder="Min" value="{{ request('ndvi_min') }}">
                        <input type="number" step="0.05" min="0" max="1" name="ndvi_max_val" id="ndviMaxVal" class="form-control" placeholder="Max" value="{{ request('ndvi_max_val') }}">
                    </div>
                </div>

                <!-- Sorting parameters -->
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label" for="sortBy">Sort By</label>
                    <select id="sortBy" name="sort_by" class="filter-select">
                        <option value="created_at" {{ request('sort_by') === 'created_at' ? 'selected' : '' }}>Date Added</option>
                        <option value="sowing_date" {{ request('sort_by') === 'sowing_date' ? 'selected' : '' }}>Sowing Date</option>
                        <option value="ndvi_max" {{ request('sort_by') === 'ndvi_max' ? 'selected' : '' }}>Peak NDVI</option>
                        <option value="yield_prediction" {{ request('sort_by') === 'yield_prediction' ? 'selected' : '' }}>Yield Prediction</option>
                        <option value="crop_type" {{ request('sort_by') === 'crop_type' ? 'selected' : '' }}>Crop Name</option>
                        <option value="region" {{ request('sort_by') === 'region' ? 'selected' : '' }}>Location</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label" for="sortOrder">Order</label>
                    <select id="sortOrder" name="sort_order" class="filter-select">
                        <option value="desc" {{ request('sort_order') === 'desc' ? 'selected' : '' }}>Descending</option>
                        <option value="asc" {{ request('sort_order') === 'asc' ? 'selected' : '' }}>Ascending</option>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <!-- Results Panel -->
    <div style="display: grid; gap: 1rem;">
        <!-- Header status bar -->
        <div class="filters-bar" style="justify-content: space-between; border-radius: 16px;">
            <div style="font-weight: 500; font-size: 0.95rem;">
                Found <span id="resultsCountText" class="text-success" style="font-weight: 700;">{{ $results->total() }}</span> matching crop cycles.
            </div>
            <div id="loadingSpinner" style="display: none; align-items: center; gap: 6px; font-size: 0.88rem; color: var(--text-muted);">
                <i class="bi bi-arrow-repeat spin" style="font-size: 1.1rem; color: var(--brand-green);"></i> Updating search results...
            </div>
        </div>

        <!-- Results List Container -->
        <div id="searchResultsContainer" style="transition: opacity 150ms ease-in-out;">
            @include('search._results', ['results' => $results])
        </div>

        <!-- Pagination Container -->
        <div id="searchPaginationContainer">
            @include('search._pagination', ['results' => $results])
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('searchFiltersForm');
        const container = document.getElementById('searchResultsContainer');
        const countText = document.getElementById('resultsCountText');
        const paginationContainer = document.getElementById('searchPaginationContainer');
        const spinner = document.getElementById('loadingSpinner');
        const clearBtn = document.getElementById('clearFiltersBtn');

        let debounceTimeout;

        // Trigger AJAX search
        function performSearch(page = 1) {
            spinner.style.display = 'flex';
            container.style.opacity = '0.5';

            // Gather form data
            const formData = new FormData(form);
            formData.append('page', page);
            
            const params = new URLSearchParams(formData);

            // Update Browser URL without reloading
            const cleanUrl = `${window.location.pathname}?${params.toString()}`;
            window.history.pushState({ path: cleanUrl }, '', cleanUrl);

            fetch(cleanUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                container.innerHTML = data.html;
                paginationContainer.innerHTML = data.pagination;
                countText.textContent = data.count;
                
                // Bind AJAX click to new pagination links
                bindPaginationLinks();
            })
            .catch(err => {
                console.error("Search error:", err);
            })
            .finally(() => {
                spinner.style.display = 'none';
                container.style.opacity = '1';
            });
        }

        // Debounce text inputs (prevent search query flood)
        function debouncedSearch() {
            clearTimeout(debounceTimeout);
            debounceTimeout = setTimeout(() => {
                performSearch(1);
            }, 300);
        }

        // Event listeners for selects and inputs
        form.querySelectorAll('select').forEach(elem => {
            elem.addEventListener('change', () => performSearch(1));
        });

        form.querySelectorAll('input').forEach(elem => {
            elem.addEventListener('input', debouncedSearch);
        });

        // Clear filters
        clearBtn.addEventListener('click', () => {
            form.reset();
            // Need to manually reset value attribute for request logic to clear properly
            form.querySelectorAll('input').forEach(i => i.value = '');
            form.querySelectorAll('select').forEach(s => s.selectedIndex = 0);
            performSearch(1);
        });

        // Pagination links AJAX click bindings
        function bindPaginationLinks() {
            document.querySelectorAll('#searchPaginationContainer a').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const url = new URL(this.href);
                    const page = url.searchParams.get('page') || 1;
                    performSearch(page);
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                });
            });
        }

        // Initial pagination link binding
        bindPaginationLinks();
    });
</script>
@endpush
@endsection
