<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dataset extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'name', 'description', 'type', 'file_path', 'original_filename',
        'file_size', 'crop_type', 'region', 'country', 'latitude', 'longitude',
        'data_start_date', 'data_end_date', 'record_count', 'status',
        'processing_notes', 'processed_at', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'data_start_date' => 'date',
            'data_end_date'   => 'date',
            'processed_at'    => 'datetime',
            'metadata'        => 'array',
            'latitude'        => 'decimal:7',
            'longitude'       => 'decimal:7',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (Dataset $dataset) {
            if ($dataset->isForceDeleting()) {
                foreach ($dataset->cropCycles()->withTrashed()->get() as $cycle) {
                    $cycle->forceDelete();
                }
            } else {
                foreach ($dataset->cropCycles as $cycle) {
                    $cycle->delete();
                }
            }
        });
    }

    // ─── Relationships ───────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cropCycles(): HasMany
    {
        return $this->hasMany(CropCycle::class);
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopeProcessed($query)
    {
        return $query->where('status', 'processed');
    }

    public function scopeByUser($query, string $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByRegion($query, string $region)
    {
        return $query->where('region', $region);
    }

    public function scopeByCropType($query, string $cropType)
    {
        return $query->where('crop_type', $cropType);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function getFileSizeFormattedAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024)    return round($bytes / 1024, 2) . ' KB';
        return $bytes . ' B';
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'processed'  => 'badge-success',
            'processing' => 'badge-warning',
            'failed'     => 'badge-danger',
            default      => 'badge-secondary',
        };
    }

    public function isPending(): bool    { return $this->status === 'pending'; }
    public function isProcessed(): bool  { return $this->status === 'processed'; }
    public function isFailed(): bool     { return $this->status === 'failed'; }
}
