<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Notification;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'locale', 'theme', 'avatar',
        'phone', 'organization', 'region', 'two_factor_enabled',
        'two_factor_secret', 'last_login_at', 'last_login_ip', 'is_active',
        // F5: Alert preferences
        'ndvi_alert_threshold', 'alert_email_enabled', 'alert_sms_enabled',
        // F1: Farm field relationship handled via model
    ];

    protected $hidden = [
        'password', 'remember_token', 'two_factor_secret',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'    => 'datetime',
            'last_login_at'        => 'datetime',
            'password'             => 'hashed',
            'two_factor_enabled'   => 'boolean',
            'is_active'            => 'boolean',
            'alert_email_enabled'  => 'boolean',
            'alert_sms_enabled'    => 'boolean',
            'ndvi_alert_threshold' => 'decimal:2',
        ];
    }

    // ─── Relationships ───────────────────────────────────────────────────────

    public function notifications()
    {
        return $this->morphMany(Notification::class, 'notifiable')->latest();
    }

    public function unreadNotifications()
    {
        return $this->notifications()->whereNull('read_at');
    }

    public function datasets(): HasMany
    {
        return $this->hasMany(Dataset::class);
    }

    public function cropCycles(): HasMany
    {
        return $this->hasMany(CropCycle::class);
    }

    public function farmFields(): HasMany
    {
        return $this->hasMany(\App\Models\FarmField::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isResearcher(): bool
    {
        return $this->role === 'researcher';
    }

    public function isFarmer(): bool
    {
        return $this->role === 'farmer';
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }
        $name = urlencode($this->name);
        return "https://ui-avatars.com/api/?name={$name}&background=16a34a&color=fff&size=128";
    }
}
