<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCropCycleRequest;
use App\Models\ActivityLog;
use App\Models\CropCycle;
use App\Models\Dataset;
use App\Models\NdviRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CropCycleController extends Controller
{

    public function index(Request $request): View
    {
        $query = auth()->user()->isAdmin()
            ? CropCycle::with(['user', 'dataset'])
            : CropCycle::where('user_id', auth()->id())->with(['dataset']);

        // Filters
        if ($request->filled('crop_type'))   $query->where('crop_type', $request->crop_type);
        if ($request->filled('region'))       $query->where('region', $request->region);
        if ($request->filled('season'))       $query->where('season', $request->season);
        if ($request->filled('season_year'))  $query->where('season_year', $request->season_year);
        if ($request->filled('yield_cat'))    $query->where('yield_category', $request->yield_cat);
        if ($request->filled('date_from'))    $query->whereDate('sowing_date', '>=', $request->date_from);
        if ($request->filled('date_to'))      $query->whereDate('harvest_date', '<=', $request->date_to);

        $cropCycles = $query->latest()->paginate(15)->withQueryString();

        $cropTypes   = CropCycle::distinct()->pluck('crop_type')->filter()->sort()->values();
        $regions     = CropCycle::distinct()->pluck('region')->filter()->sort()->values();
        $seasonYears = CropCycle::distinct()->orderByDesc('season_year')->pluck('season_year')->filter()->values();

        return view('crop-cycles.index', compact('cropCycles', 'cropTypes', 'regions', 'seasonYears'));
    }

    public function create(): View
    {
        $datasets = auth()->user()->isAdmin()
            ? Dataset::processed()->get()
            : Dataset::processed()->where('user_id', auth()->id())->get();

        return view('crop-cycles.create', compact('datasets'));
    }

    public function store(StoreCropCycleRequest $request): RedirectResponse
    {
        $cropCycle = CropCycle::create(array_merge($request->validated(), [
            'user_id' => auth()->id(),
        ]));
        ActivityLog::log('created', "Crop cycle created for '{$cropCycle->crop_type}'.", CropCycle::class, $cropCycle->id);

        return redirect()->route('crop-cycles.show', $cropCycle)
            ->with('success', 'Crop cycle created successfully.');
    }

    public function show(CropCycle $cropCycle): View
    {
        $this->authorizeCropCycle($cropCycle);
        $cropCycle->load(['dataset', 'user', 'ndviRecords']);

        // Build chart data arrays for JavaScript
        $ndviDates  = $cropCycle->ndviRecords->pluck('observation_date')->map->toDateString();
        $ndviValues = $cropCycle->ndviRecords->pluck('ndvi_value');
        $eviValues  = $cropCycle->ndviRecords->pluck('evi_value');

        return view('crop-cycles.show', compact('cropCycle', 'ndviDates', 'ndviValues', 'eviValues'));
    }

    public function edit(CropCycle $cropCycle): View
    {
        $this->authorizeCropCycle($cropCycle);
        $datasets = auth()->user()->isAdmin()
            ? Dataset::processed()->get()
            : Dataset::processed()->where('user_id', auth()->id())->get();

        return view('crop-cycles.edit', compact('cropCycle', 'datasets'));
    }

    public function update(StoreCropCycleRequest $request, CropCycle $cropCycle): RedirectResponse
    {
        $this->authorizeCropCycle($cropCycle);
        $cropCycle->update($request->validated());
        ActivityLog::log('updated', "Crop cycle updated.", CropCycle::class, $cropCycle->id);

        return redirect()->route('crop-cycles.show', $cropCycle)
            ->with('success', 'Crop cycle updated successfully.');
    }

    public function destroy(CropCycle $cropCycle): RedirectResponse
    {
        $this->authorizeCropCycle($cropCycle);
        $cropCycle->delete();
        ActivityLog::log('deleted', "Crop cycle deleted.", CropCycle::class, $cropCycle->id);

        return redirect()->route('crop-cycles.index')
            ->with('success', 'Crop cycle deleted.');
    }

    /** Get NDVI data for a specific cycle (AJAX) */
    public function ndviChart(CropCycle $cropCycle)
    {
        $this->authorizeCropCycle($cropCycle);
        $records = $cropCycle->ndviRecords()->get(['observation_date', 'ndvi_value', 'evi_value', 'growth_stage']);

        return response()->json([
            'dates'  => $records->pluck('observation_date'),
            'ndvi'   => $records->pluck('ndvi_value'),
            'evi'    => $records->pluck('evi_value'),
            'stages' => $records->pluck('growth_stage'),
        ]);
    }

    private function authorizeCropCycle(CropCycle $cropCycle): void
    {
        if (!auth()->user()->isAdmin() && $cropCycle->user_id !== auth()->id()) {
            abort(403);
        }
    }
}
