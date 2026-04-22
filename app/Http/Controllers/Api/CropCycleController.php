<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CropCycle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CropCycleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = auth()->user()->isAdmin()
            ? CropCycle::with(['dataset', 'user'])
            : CropCycle::where('user_id', auth()->id())->with('dataset');

        if ($request->filled('crop_type'))  $query->where('crop_type', $request->crop_type);
        if ($request->filled('region'))     $query->where('region', $request->region);
        if ($request->filled('season'))     $query->where('season', $request->season);

        return response()->json($query->latest()->paginate(20));
    }

    public function show(CropCycle $cropCycle): JsonResponse
    {
        $this->authorize($cropCycle);
        return response()->json($cropCycle->load('ndviRecords'));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'dataset_id'   => 'required|exists:datasets,id',
            'crop_type'    => 'required|string|max:100',
            'region'       => 'required|string|max:100',
            'season_year'  => 'required|integer|min:1990|max:2100',
            'season'       => 'required|in:Kharif,Rabi,Zaid,Summer,Winter,Year-round',
            'sowing_date'  => 'nullable|date',
            'harvest_date' => 'nullable|date|after_or_equal:sowing_date',
        ]);

        $cropCycle = CropCycle::create(array_merge($validated, ['user_id' => auth()->id()]));

        return response()->json(['message' => 'Created.', 'crop_cycle' => $cropCycle], 201);
    }

    public function update(Request $request, CropCycle $cropCycle): JsonResponse
    {
        $this->authorize($cropCycle);
        $cropCycle->update($request->only([
            'crop_type', 'region', 'season', 'sowing_date', 'harvest_date',
            'ndvi_max', 'ndvi_min', 'yield_prediction', 'notes',
        ]));
        return response()->json(['message' => 'Updated.', 'crop_cycle' => $cropCycle]);
    }

    public function destroy(CropCycle $cropCycle): JsonResponse
    {
        $this->authorize($cropCycle);
        $cropCycle->delete();
        return response()->json(['message' => 'Deleted.']);
    }

    private function authorize(CropCycle $c): void
    {
        if (!auth()->user()->isAdmin() && $c->user_id !== auth()->id()) abort(403);
    }
}
