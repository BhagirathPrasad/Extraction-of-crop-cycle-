<?php

namespace App\Jobs;

use App\Models\CropCycle;
use App\Models\NdviRecord;
use App\Models\User;
use App\Notifications\CropHealthAlert;
use App\Services\WeatherService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * CheckCropHealthJob — runs nightly via Laravel scheduler.
 * Checks NDVI levels for all active crop cycles and sends alerts:
 *   - Low NDVI alert (below user's threshold, default 0.3)
 *   - Harvest ready alert (NDVI dropping after peak)
 *   - Weather-based drought risk alert
 */
class CheckCropHealthJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 300;

    public function handle(WeatherService $weatherService): void
    {
        Log::info('CheckCropHealthJob started at ' . now()->toDateTimeString());

        // Only process crop cycles that are active (status = active or null)
        $activeCycles = CropCycle::whereIn('status', ['active', null])
            ->whereNotNull('ndvi_max')
            ->with('user')
            ->get();

        foreach ($activeCycles as $cycle) {
            $user = $cycle->user;
            if (!$user) continue;

            // Get the most recent NDVI record
            $latestRecord = NdviRecord::where('crop_cycle_id', $cycle->id)
                ->orderByDesc('observation_date')
                ->first();

            if (!$latestRecord) continue;

            $currentNdvi    = (float) $latestRecord->ndvi_value;
            $threshold      = (float) ($user->ndvi_alert_threshold ?? 0.30);
            $ndviMax        = (float) ($cycle->ndvi_max ?? 0.5);
            $peakReached    = $currentNdvi < ($ndviMax * 0.80); // 80% of peak = declining phase

            // 1. Low NDVI Alert
            if ($currentNdvi < $threshold) {
                $this->sendAlertIfNotDuplicate($user, $cycle, 'low_ndvi',
                    "NDVI is critically low ({$currentNdvi}) for your {$cycle->crop_type} crop in {$cycle->region}. " .
                    "Consider irrigation or pest inspection.",
                    $currentNdvi
                );
            }

            // 2. Harvest Ready Alert (NDVI declining past 80% of peak AND harvest date near)
            $harvestDate = $cycle->harvest_date;
            if ($peakReached && $currentNdvi < 0.35 && $harvestDate) {
                $daysToHarvest = now()->diffInDays($harvestDate, false);
                if ($daysToHarvest >= 0 && $daysToHarvest <= 14) {
                    $this->sendAlertIfNotDuplicate($user, $cycle, 'harvest_ready',
                        "Your {$cycle->crop_type} crop in {$cycle->region} is approaching harvest. " .
                        "NDVI is {$currentNdvi} and harvest is {$daysToHarvest} days away.",
                        $currentNdvi
                    );
                }
            }

            // 3. Weather-based drought risk (if field has coordinates)
            $field = $cycle->farmField ?? null;
            if ($field && $field->center_lat && $field->center_lng) {
                $weather = $weatherService->fetchWeather($field->center_lat, $field->center_lng);
                $totalRain = $weather['aggregates']['total_rain_7d'] ?? 10;

                if ($totalRain < 5.0) {
                    $this->sendAlertIfNotDuplicate($user, $cycle, 'drought_risk',
                        "Only {$totalRain}mm of rainfall in the last 7 days near {$field->name}. " .
                        "Your {$cycle->crop_type} crop may need supplemental irrigation.",
                        $currentNdvi
                    );
                }
            }
        }

        Log::info('CheckCropHealthJob completed. Processed ' . $activeCycles->count() . ' cycles.');
    }

    /**
     * Avoid spamming: only send an alert if no alert of same type was sent in the last 3 days.
     */
    private function sendAlertIfNotDuplicate(User $user, CropCycle $cycle, string $type, string $message, float $ndvi): void
    {
        $recentAlert = $user->unreadNotifications()
            ->where('data.alert_type', $type)
            ->where('data.crop_cycle_id', (string) $cycle->id)
            ->where('created_at', '>=', now()->subDays(3))
            ->exists();

        if (!$recentAlert) {
            $user->notify(new CropHealthAlert($cycle, $type, $message, $ndvi));
            Log::info("Alert sent: {$type} for cycle {$cycle->id}, user {$user->id}");
        }
    }
}
