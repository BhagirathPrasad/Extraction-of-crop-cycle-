<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CropCycle extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'dataset_id', 'user_id', 'crop_type', 'variety', 'region', 'field_id',
        'season_year', 'season',
        'sowing_date', 'emergence_date', 'tillering_date', 'jointing_date',
        'heading_date', 'peak_growth_date', 'maturity_date', 'harvest_date',
        'ndvi_max', 'ndvi_min', 'ndvi_mean', 'ndvi_at_sowing', 'ndvi_at_peak', 'ndvi_at_harvest',
        'yield_prediction', 'yield_unit', 'actual_yield', 'yield_category',
        'irrigation_suggestions', 'fertilizer_suggestions', 'notes', 'status',
    ];

    protected function casts(): array
    {
        return [
            'sowing_date'             => 'date',
            'emergence_date'          => 'date',
            'tillering_date'          => 'date',
            'jointing_date'           => 'date',
            'heading_date'            => 'date',
            'peak_growth_date'        => 'date',
            'maturity_date'           => 'date',
            'harvest_date'            => 'date',
            'ndvi_max'                => 'decimal:4',
            'ndvi_min'                => 'decimal:4',
            'ndvi_mean'               => 'decimal:4',
            'irrigation_suggestions'  => 'array',
            'fertilizer_suggestions'  => 'array',
        ];
    }

    // ─── Relationships ───────────────────────────────────────────────────────

    public function dataset(): BelongsTo
    {
        return $this->belongsTo(Dataset::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ndviRecords(): HasMany
    {
        return $this->hasMany(NdviRecord::class)->orderBy('observation_date');
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopeByCropType($query, string $type)
    {
        return $query->where('crop_type', $type);
    }

    public function scopeByRegion($query, string $region)
    {
        return $query->where('region', $region);
    }

    public function scopeByYear($query, int $year)
    {
        return $query->where('season_year', $year);
    }

    public function scopeBySeason($query, string $season)
    {
        return $query->where('season', $season);
    }

    // ─── Computed ────────────────────────────────────────────────────────────

    /** Total growing days from sowing to harvest */
    public function getGrowingDaysAttribute(): ?int
    {
        if ($this->sowing_date && $this->harvest_date) {
            return $this->sowing_date->diffInDays($this->harvest_date);
        }
        return null;
    }

    /** NDVI range breadth */
    public function getNdviRangeAttribute(): ?float
    {
        if ($this->ndvi_max !== null && $this->ndvi_min !== null) {
            return round((float)$this->ndvi_max - (float)$this->ndvi_min, 4);
        }
        return null;
    }

    public function getYieldBadgeClassAttribute(): string
    {
        return match ($this->yield_category) {
            'high'   => 'badge-success',
            'medium' => 'badge-warning',
            'low'    => 'badge-danger',
            default  => 'badge-secondary',
        };
    }
}
