<?php

namespace App\Http\Controllers;

use App\Exports\CropCyclesExport;
use App\Exports\DatasetsExport;
use App\Http\Requests\StoreReportRequest;
use App\Models\ActivityLog;
use App\Models\CropCycle;
use App\Models\Dataset;
use App\Models\Report;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{

    public function index(Request $request): View
    {
        $query = auth()->user()->isAdmin()
            ? Report::with('user')
            : Report::where('user_id', auth()->id());

        if ($request->filled('type'))     $query->where('type', $request->type);
        if ($request->filled('category')) $query->where('report_category', $request->category);
        if ($request->filled('status'))   $query->where('status', $request->status);

        $reports = $query->latest()->paginate(15)->withQueryString();

        return view('reports.index', compact('reports'));
    }

    public function create(): View
    {
        return view('reports.create');
    }

    public function store(StoreReportRequest $request): RedirectResponse
    {
        $report = Report::create([
            'user_id'          => auth()->id(),
            'title'            => $request->title,
            'description'      => $request->description,
            'type'             => $request->type,
            'report_category'  => $request->report_category,
            'filters'          => $request->only(['date_from', 'date_to', 'crop_type', 'region', 'season']),
            'status'           => 'generating',
        ]);

        // Generate the report synchronously (can be moved to a Queue job)
        $this->generateReport($report, $request);

        return redirect()->route('reports.show', $report)
            ->with('success', 'Report generated successfully!');
    }

    public function show(Report $report): View
    {
        $this->authorizeReport($report);
        return view('reports.show', compact('report'));
    }

    /** Download the generated file */
    public function download(Report $report): mixed
    {
        $this->authorizeReport($report);

        if (!$report->isReady() || !$report->file_path) {
            return back()->with('error', 'Report file is not ready yet.');
        }

        $report->increment('download_count');
        return response()->download(storage_path('app/private/' . $report->file_path));
    }

    public function destroy(Report $report): RedirectResponse
    {
        $this->authorizeReport($report);
        $report->delete();
        return redirect()->route('reports.index')->with('success', 'Report deleted.');
    }

    /** Quick export CSV/Excel for crop cycles (no DB record) */
    public function exportExcel(Request $request): mixed
    {
        $filename = 'crop-cycles-' . now()->format('Y-m-d') . '.xlsx';
        ActivityLog::log('exported', 'Exported crop cycles to Excel.');
        return Excel::download(new CropCyclesExport($request->all()), $filename);
    }

    /** Quick export PDF for crop cycles (no DB record) */
    public function exportPdf(Request $request): Response
    {
        $filters = $request->only(['crop_type', 'region', 'date_from', 'date_to']);
        $query   = auth()->user()->isAdmin()
            ? CropCycle::with(['dataset'])
            : CropCycle::where('user_id', auth()->id())->with(['dataset']);

        if (!empty($filters['crop_type'])) $query->where('crop_type', $filters['crop_type']);
        if (!empty($filters['region']))    $query->where('region', $filters['region']);
        if (!empty($filters['date_from'])) $query->whereDate('sowing_date', '>=', $filters['date_from']);
        if (!empty($filters['date_to']))   $query->whereDate('harvest_date', '<=', $filters['date_to']);

        $cropCycles = $query->latest()->take(100)->get();

        $pdf = Pdf::loadView('reports.pdf.crop-cycles', compact('cropCycles', 'filters'))
            ->setPaper('a4', 'landscape');

        ActivityLog::log('exported', 'Exported crop cycles to PDF.');
        return $pdf->download('crop-cycles-' . now()->format('Y-m-d') . '.pdf');
    }

    // ─── Private ─────────────────────────────────────────────────────────────

    private function generateReport(Report $report, Request $request): void
    {
        try {
            $filters = $report->filters ?? [];
            $query   = auth()->user()->isAdmin()
                ? CropCycle::with(['dataset'])
                : CropCycle::where('user_id', auth()->id())->with(['dataset']);

            if (!empty($filters['crop_type'])) $query->where('crop_type', $filters['crop_type']);
            if (!empty($filters['region']))    $query->where('region', $filters['region']);
            if (!empty($filters['date_from'])) $query->whereDate('sowing_date', '>=', $filters['date_from']);
            if (!empty($filters['date_to']))   $query->whereDate('harvest_date', '<=', $filters['date_to']);

            $cropCycles  = $query->get();
            $recordCount = $cropCycles->count();

            $path     = null;
            $fileSize = 0;

            if ($report->type === 'PDF') {
                $pdf      = Pdf::loadView('reports.pdf.crop-cycles', compact('cropCycles', 'filters'));
                $filename = 'reports/' . $report->id . '-' . now()->format('YmdHis') . '.pdf';
                $pdf->save(storage_path('app/private/' . $filename));
                $path     = $filename;
                $fileSize = filesize(storage_path('app/private/' . $filename));

            } elseif ($report->type === 'Excel') {
                $filename = 'reports/' . $report->id . '-' . now()->format('YmdHis') . '.xlsx';
                $filePath = storage_path('app/private/' . $filename);
                @mkdir(dirname($filePath), 0775, true);
                Excel::store(new CropCyclesExport($filters), $filename, 'private');
                $path     = $filename;
                $fileSize = file_exists($filePath) ? filesize($filePath) : 0;
            }

            $report->update([
                'status'       => 'ready',
                'file_path'    => $path,
                'file_size'    => $fileSize,
                'record_count' => $recordCount,
                'generated_at' => now(),
                'expires_at'   => now()->addDays(7),
            ]);

            ActivityLog::log('report_generated', "Report '{$report->title}' generated.", Report::class, $report->id);

        } catch (\Throwable $e) {
            $report->update(['status' => 'failed', 'generated_at' => now()]);
            throw $e;
        }
    }

    private function authorizeReport(Report $report): void
    {
        if (!auth()->user()->isAdmin() && $report->user_id !== auth()->id()) {
            abort(403);
        }
    }
}
