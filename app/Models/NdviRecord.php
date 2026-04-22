<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NdviRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'crop_cycle_id', 'observation_date', 'ndvi_value', 'evi_value', 'savi_value',
        'lai_value', 'growth_stage', 'temperature', 'rainfall', 'humidity',
        'soil_moisture', 'day_of_year', 'satellite_source', 'cloud_cover', 'band_values',
    ];

    protected function casts(): array
    {
        return [
            'observation_date' => 'date',
            'band_values'      => 'array',
            'ndvi_value'       => 'decimal:4',
            'evi_value'        => 'decimal:4',
            'savi_value'       => 'decimal:4',
        ];
    }

    public function cropCycle(): BelongsTo
    {
        return $this->belongsTo(CropCycle::class);
    }

    /** User-friendly growth stage label */
    public function getGrowthStageLabelAttribute(): string
    {
        return match ($this->growth_stage) {
            'pre_sowing'   => 'Pre-Sowing',
            'germination'  => 'Germination',
            'emergence'    => 'Emergence',
            'tillering'    => 'Tillering',
            'jointing'     => 'Jointing',
            'heading'      => 'Heading',
            'flowering'    => 'Flowering',
            'grain_filling'=> 'Grain Filling',
            'maturity'     => 'Maturity',
            'post_harvest' => 'Post Harvest',
            default        => ucfirst(str_replace('_', ' ', $this->growth_stage ?? '')),
        };
    }

    /** NDVI health indicator: 0–0.2 bare, 0.2–0.5 sparse, 0.5–1.0 dense */
    public function getNdviClassAttribute(): string
    {
        $v = (float) $this->ndvi_value;
        if ($v <= 0.2) return 'bare';
        if ($v <= 0.5) return 'sparse';
        if ($v <= 0.75) return 'moderate';
        return 'dense';
    }
}
