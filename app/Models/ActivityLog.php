<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id', 'action', 'model_type', 'model_id', 'description',
        'old_values', 'new_values', 'ip_address', 'user_agent', 'url', 'method',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Log an activity from a request context.
     */
    public static function log(
        string $action,
        string $description = '',
        ?string $modelType = null,
        ?int $modelId = null,
        array $oldValues = [],
        array $newValues = []
    ): static {
        return static::create([
            'user_id'     => auth()->id(),
            'action'      => $action,
            'description' => $description,
            'model_type'  => $modelType,
            'model_id'    => $modelId,
            'old_values'  => $oldValues ?: null,
            'new_values'  => $newValues ?: null,
            'ip_address'  => request()->ip(),
            'user_agent'  => request()->userAgent(),
            'url'         => request()->fullUrl(),
            'method'      => request()->method(),
        ]);
    }
}
