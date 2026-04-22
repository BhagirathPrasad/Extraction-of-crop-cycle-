<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessDatasetJob;
use App\Models\Dataset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DatasetController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = auth()->user()->isAdmin()
            ? Dataset::with('user')
            : Dataset::where('user_id', auth()->id());

        if ($request->filled('status'))    $query->where('status', $request->status);
        if ($request->filled('crop_type')) $query->where('crop_type', $request->crop_type);
        if ($request->filled('region'))    $query->where('region', $request->region);

        return response()->json($query->latest()->paginate(20));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'type'            => 'required|in:CSV,GeoTIFF,JSON',
            'file'            => 'required|file|mimes:csv,txt,tif,tiff,json|max:51200',
            'crop_type'       => 'nullable|string|max:100',
            'region'          => 'nullable|string|max:100',
            'data_start_date' => 'nullable|date',
            'data_end_date'   => 'nullable|date|after_or_equal:data_start_date',
        ]);

        $file = $request->file('file');
        $path = $file->store('datasets/' . date('Y/m'), 'private');

        $dataset = Dataset::create([
            'user_id'           => auth()->id(),
            'name'              => $validated['name'],
            'type'              => $validated['type'],
            'file_path'         => $path,
            'original_filename' => $file->getClientOriginalName(),
            'file_size'         => $file->getSize(),
            'crop_type'         => $validated['crop_type'] ?? null,
            'region'            => $validated['region'] ?? null,
            'data_start_date'   => $validated['data_start_date'] ?? null,
            'data_end_date'     => $validated['data_end_date'] ?? null,
            'status'            => 'pending',
        ]);

        ProcessDatasetJob::dispatch($dataset);

        return response()->json(['message' => 'Dataset uploaded & processing started.', 'dataset' => $dataset], 201);
    }

    public function show(Dataset $dataset): JsonResponse
    {
        $this->authorize($dataset);
        return response()->json($dataset->load('cropCycles'));
    }

    public function update(Request $request, Dataset $dataset): JsonResponse
    {
        $this->authorize($dataset);
        $dataset->update($request->only(['name', 'description', 'crop_type', 'region']));
        return response()->json(['message' => 'Updated.', 'dataset' => $dataset]);
    }

    public function destroy(Dataset $dataset): JsonResponse
    {
        $this->authorize($dataset);
        $dataset->delete();
        return response()->json(['message' => 'Deleted.']);
    }

    private function authorize(Dataset $dataset): void
    {
        if (!auth()->user()->isAdmin() && $dataset->user_id !== auth()->id()) {
            abort(403, 'Forbidden');
        }
    }
}
