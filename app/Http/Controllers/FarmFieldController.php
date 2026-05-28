<?php

namespace App\Http\Controllers;

use App\Models\FarmField;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FarmFieldController extends Controller
{
    /** List all farm fields for current user (map + sidebar) */
    public function index(): View
    {
        $user   = auth()->user();
        $fields = FarmField::where('user_id', $user->id)->latest()->get();

        return view('farm-fields.index', compact('fields'));
    }

    /** Store a new farm field polygon */
    public function store(Request $request): \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'crop_type'     => 'nullable|string|max:100',
            'soil_type'     => 'nullable|string|in:loamy,sandy,clay,silt,black,red',
            'area_hectares' => 'nullable|numeric|min:0',
            'coordinates'   => 'required|array|min:3',
            'coordinates.*' => 'array|size:2',
            'center'        => 'nullable|array',
            'notes'         => 'nullable|string|max:500',
        ]);

        $validated['user_id']   = auth()->id();
        $validated['is_active'] = true;

        // Auto-calculate center from coordinates if not provided
        if (empty($validated['center']) && !empty($validated['coordinates'])) {
            $lats = array_column($validated['coordinates'], 0);
            $lngs = array_column($validated['coordinates'], 1);
            $validated['center'] = [
                'lat' => round(array_sum($lats) / count($lats), 6),
                'lng' => round(array_sum($lngs) / count($lngs), 6),
            ];
        }

        $field = FarmField::create($validated);

        \App\Models\ActivityLog::log('farm_field_created', "Farm field '{$field->name}' created.");

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'field'   => $field,
                'message' => "Farm field '{$field->name}' saved successfully.",
            ]);
        }

        return redirect()->route('farm-fields.index')->with('success', "Farm field '{$field->name}' saved.");
    }

    /** Show a single farm field detail */
    public function show(FarmField $farmField): View
    {
        abort_if($farmField->user_id !== auth()->id(), 403);
        $farmField->load('cropCycles');
        return view('farm-fields.show', compact('farmField'));
    }

    /** Update a farm field */
    public function update(Request $request, FarmField $farmField): \Illuminate\Http\RedirectResponse
    {
        abort_if($farmField->user_id !== auth()->id(), 403);

        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'crop_type'     => 'nullable|string|max:100',
            'soil_type'     => 'nullable|string|in:loamy,sandy,clay,silt,black,red',
            'area_hectares' => 'nullable|numeric|min:0',
            'notes'         => 'nullable|string|max:500',
        ]);

        $farmField->update($validated);

        // Invalidate dashboard cache for this user
        \Illuminate\Support\Facades\Cache::forget('dashboard_stats_' . auth()->id());

        return redirect()->route('farm-fields.show', $farmField)->with('success', 'Farm field updated.');
    }

    /** Delete a farm field */
    public function destroy(FarmField $farmField): \Illuminate\Http\RedirectResponse
    {
        abort_if($farmField->user_id !== auth()->id(), 403);
        $name = $farmField->name;
        $farmField->delete();

        \App\Models\ActivityLog::log('farm_field_deleted', "Farm field '{$name}' deleted.");

        return redirect()->route('farm-fields.index')->with('success', "Farm field '{$name}' deleted.");
    }

    /** GeoJSON endpoint — all user's fields as FeatureCollection for Leaflet.js */
    public function geojson(): \Illuminate\Http\JsonResponse
    {
        $user   = auth()->user();
        $fields = FarmField::where('user_id', $user->id)->get();

        $features = $fields->map(fn($f) => $f->toGeoJson())->values();

        return response()->json([
            'type'     => 'FeatureCollection',
            'features' => $features,
        ]);
    }
}
