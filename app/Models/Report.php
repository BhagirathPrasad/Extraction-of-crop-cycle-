<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'title', 'description', 'type', 'report_category',
        'filters', 'file_path', 'file_size', 'status', 'record_count',
        'generated_at', 'expires_at', 'download_count',
    ];

    protected function casts(): array
    {
        return [
            'filters'      => 'array',
            'generated_at' => 'datetime',
            'expires_at'   => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

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
            'ready'      => 'badge-success',
            'generating' => 'badge-warning',
            'failed'     => 'badge-danger',
            default      => 'badge-secondary',
        };
    }

    public function isReady(): bool { return $this->status === 'ready'; }
    public function isFailed(): bool { return $this->status === 'failed'; }
}
