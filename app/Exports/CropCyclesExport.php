<?php

namespace App\Exports;

use App\Models\CropCycle;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CropCyclesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle
{
    public function __construct(protected array $filters = []) {}

    public function title(): string { return 'Crop Cycles'; }

    public function collection(): Collection
    {
        $query = auth()->user()->isAdmin()
            ? CropCycle::with(['dataset', 'user'])
            : CropCycle::where('user_id', auth()->id())->with('dataset');

        if (!empty($this->filters['crop_type'])) $query->where('crop_type', $this->filters['crop_type']);
        if (!empty($this->filters['region']))     $query->where('region', $this->filters['region']);
        if (!empty($this->filters['date_from']))  $query->whereDate('sowing_date', '>=', $this->filters['date_from']);
        if (!empty($this->filters['date_to']))    $query->whereDate('harvest_date', '<=', $this->filters['date_to']);

        return $query->latest()->get();
    }

    public function headings(): array
    {
        return [
            'ID', 'Crop Type', 'Variety', 'Region', 'Season', 'Year',
            'Sowing Date', 'Emergence Date', 'Peak Growth Date', 'Harvest Date',
            'Growing Days', 'NDVI Max', 'NDVI Min', 'NDVI Mean',
            'Yield Prediction (kg/ha)', 'Actual Yield (kg/ha)', 'Yield Category',
            'Status', 'Dataset', 'Created',
        ];
    }

    public function map($row): array
    {
        return [
            $row->id,
            ucfirst($row->crop_type),
            $row->variety,
            $row->region,
            $row->season,
            $row->season_year,
            $row->sowing_date?->format('Y-m-d'),
            $row->emergence_date?->format('Y-m-d'),
            $row->peak_growth_date?->format('Y-m-d'),
            $row->harvest_date?->format('Y-m-d'),
            $row->growing_days,
            $row->ndvi_max,
            $row->ndvi_min,
            $row->ndvi_mean,
            $row->yield_prediction,
            $row->actual_yield,
            ucfirst($row->yield_category ?? '-'),
            ucfirst($row->status),
            $row->dataset?->name,
            $row->created_at?->format('Y-m-d'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font'    => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'    => ['fillType' => 'solid', 'startColor' => ['rgb' => '166534']],
            ],
        ];
    }
}
