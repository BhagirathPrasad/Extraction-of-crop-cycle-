<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FarmField extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'farm_fields';

    protected $fillable = [
        'user_id',
        'name',
        'crop_type',
        'soil_type',
        'area_hectares',
        'coordinates',   // array of [lat, lng] pairs (polygon)
        'center',        // { lat: float, lng: float }
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'coordinates'  => 'array',
            'center'       => 'array',
            'area_hectares'=> 'decimal:4',
            'is_active'    => 'boolean',
        ];
    }

    // ─── Relationships ───────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cropCycles()
    {
        return $this->hasMany(CropCycle::class, 'field_id');
    }

    // ─── Helpers ────────────────────────────────────────────────────────────

    /** Get center latitude */
    public function getCenterLatAttribute(): ?float
    {
        return $this->center['lat'] ?? null;
    }

    /** Get center longitude */
    public function getCenterLngAttribute(): ?float
    {
        return $this->center['lng'] ?? null;
    }

    public function toGeoJson(): array
    {
        $coordinates = $this->coordinates ?? [];
        $geoJsonCoords = array_map(function($coord) {
            return [(float)($coord[1] ?? 0), (float)($coord[0] ?? 0)];
        }, $coordinates);

        return [
            'type'       => 'Feature',
            'geometry'   => [
                'type'        => 'Polygon',
                'coordinates' => [$geoJsonCoords],
            ],
            'properties' => [
                'id'           => (string) $this->id,
                'name'         => $this->name,
                'crop_type'    => $this->crop_type,
                'soil_type'    => $this->soil_type,
                'area_hectares'=> $this->area_hectares,
                'show_url'     => route('farm-fields.show', $this),
            ],
        ];
    }
}
