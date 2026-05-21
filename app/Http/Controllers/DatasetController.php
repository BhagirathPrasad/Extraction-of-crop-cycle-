<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDatasetRequest;
use App\Jobs\ProcessDatasetJob;
use App\Models\ActivityLog;
use App\Models\Dataset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DatasetController extends Controller
{
    public function clearAll(): RedirectResponse
    {
        $user = auth()->user();
        if ($user->isAdmin()) {
            // Delete all datasets (which cascades to crop_cycles, ndvi_records)
            Dataset::query()->delete();
        } else {
            Dataset::where('user_id', $user->id)->delete();
        }
        
        ActivityLog::log('deleted', "All dataset records cleared.", Dataset::class, null);

        return redirect()->back()->with('success', 'All datasets and analysis data have been cleared.');
    }

    public function index(Request $request): View
    {
        $query = auth()->user()->isAdmin()
            ? Dataset::with('user')
            : Dataset::where('user_id', auth()->id());

        // Filters
        if ($request->filled('status'))    $query->where('status', $request->status);
        if ($request->filled('crop_type')) $query->where('crop_type', $request->crop_type);
        if ($request->filled('region'))    $query->where('region', $request->region);
        if ($request->filled('type'))      $query->where('type', $request->type);
        if ($request->filled('date_from')) $query->whereDate('created_at', '>=', $request->date_from);
        if ($request->filled('date_to'))   $query->whereDate('created_at', '<=', $request->date_to);

        $datasets  = $query->latest()->paginate(15)->withQueryString();
        $cropTypes = Dataset::distinct()->pluck('crop_type')->filter()->sort()->values();
        $regions   = Dataset::distinct()->pluck('region')->filter()->sort()->values();

        return view('datasets.index', compact('datasets', 'cropTypes', 'regions'));
    }

    public function create(): View
    {
        return view('datasets.create');
    }

    public function store(StoreDatasetRequest $request): RedirectResponse
    {
        $file = $request->file('file');
        $path = $file->store('datasets/' . date('Y/m'), 'local');

        $dataset = Dataset::create([
            'user_id'            => auth()->id(),
            'name'               => $request->name,
            'description'        => $request->description,
            'type'               => $request->type,
            'file_path'          => $path,
            'original_filename'  => $file->getClientOriginalName(),
            'file_size'          => $file->getSize(),
            'crop_type'          => $request->crop_type,
            'region'             => $request->region,
            'country'            => $request->country,
            'latitude'           => $request->latitude,
            'longitude'          => $request->longitude,
            'data_start_date'    => $request->data_start_date,
            'data_end_date'      => $request->data_end_date,
            'status'             => 'pending',
        ]);

        ActivityLog::log('uploaded', "Dataset '{$dataset->name}' uploaded.", Dataset::class, $dataset->id);

        // Dispatch background processing job
        ProcessDatasetJob::dispatch($dataset);

        return redirect()->route('datasets.show', $dataset)
            ->with('success', 'Dataset uploaded! Processing started in the background.');
    }

    public function show(Dataset $dataset): View
    {
        $this->authorizeDataset($dataset);
        $dataset->load(['user', 'cropCycles.ndviRecords']);
        return view('datasets.show', compact('dataset'));
    }

    public function edit(Dataset $dataset): View
    {
        $this->authorizeDataset($dataset);
        return view('datasets.edit', compact('dataset'));
    }

    public function update(Request $request, Dataset $dataset): RedirectResponse
    {
        $this->authorizeDataset($dataset);

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'crop_type'   => 'nullable|string|max:100',
            'region'      => 'nullable|string|max:100',
            'country'     => 'nullable|string|max:100',
        ]);

        $dataset->update($validated);
        ActivityLog::log('updated', "Dataset '{$dataset->name}' updated.", Dataset::class, $dataset->id);

        return redirect()->route('datasets.show', $dataset)
            ->with('success', 'Dataset updated successfully.');
    }

    public function destroy(Dataset $dataset): RedirectResponse
    {
        $this->authorizeDataset($dataset);
        $name = $dataset->name;
        $dataset->delete();
        ActivityLog::log('deleted', "Dataset '{$name}' deleted.", Dataset::class, $dataset->id);

        return redirect()->route('datasets.index')
            ->with('success', 'Dataset deleted successfully.');
    }

    /** Reprocess a dataset by dispatching the job again */
    public function reprocess(Dataset $dataset): RedirectResponse
    {
        $this->authorizeDataset($dataset);
        $dataset->update(['status' => 'pending', 'processing_notes' => null]);
        ProcessDatasetJob::dispatch($dataset);
        return back()->with('success', 'Dataset requeued for processing.');
    }

    private function authorizeDataset(Dataset $dataset): void
    {
        if (!auth()->user()->isAdmin() && $dataset->user_id !== auth()->id()) {
            abort(403);
        }
    }
}
