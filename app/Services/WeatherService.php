<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * WeatherService — fetches real-time weather from Open-Meteo API (free, no API key).
 * API Docs: https://open-meteo.com/en/docs
 */
class WeatherService
{
    protected string $baseUrl = 'https://api.open-meteo.com/v1/forecast';

    /**
     * Fetch 7-day weather forecast for given coordinates.
     * Results are cached per location (rounded to 2 decimal places) for 1 hour.
     */
    public function fetchWeather(float $lat, float $lng): array
    {
        $cacheKey = 'weather_' . round($lat, 2) . '_' . round($lng, 2);

        return Cache::remember($cacheKey, 3600, function () use ($lat, $lng) {
            try {
                $response = Http::timeout(10)->get($this->baseUrl, [
                    'latitude'        => round($lat, 4),
                    'longitude'       => round($lng, 4),
                    'daily'           => 'temperature_2m_max,temperature_2m_min,precipitation_sum,windspeed_10m_max,weathercode',
                    'hourly'          => 'temperature_2m,relative_humidity_2m,precipitation,windspeed_10m',
                    'current_weather' => true,
                    'timezone'        => 'Asia/Kolkata',
                    'forecast_days'   => 7,
                ]);

                if (!$response->successful()) {
                    return $this->fallbackWeather();
                }

                $data = $response->json();
                return $this->parseWeatherResponse($data);

            } catch (\Throwable $e) {
                Log::warning('WeatherService: API call failed — ' . $e->getMessage());
                return $this->fallbackWeather();
            }
        });
    }

    /**
     * Parse Open-Meteo API response into a clean structure.
     */
    private function parseWeatherResponse(array $data): array
    {
        $current = $data['current_weather'] ?? [];
        $daily   = $data['daily'] ?? [];
        $hourly  = $data['hourly'] ?? [];

        // Extract daily data
        $dailyData = [];
        $dates = $daily['time'] ?? [];
        foreach ($dates as $i => $date) {
            $dailyData[] = [
                'date'            => $date,
                'temp_max'        => $daily['temperature_2m_max'][$i] ?? null,
                'temp_min'        => $daily['temperature_2m_min'][$i] ?? null,
                'precipitation'   => $daily['precipitation_sum'][$i] ?? 0,
                'wind_speed'      => $daily['windspeed_10m_max'][$i] ?? null,
                'weather_code'    => $daily['weathercode'][$i] ?? 0,
                'weather_label'   => $this->weatherCodeLabel($daily['weathercode'][$i] ?? 0),
            ];
        }

        // Calculate 7-day aggregate metrics
        $totalRain   = array_sum(array_column($dailyData, 'precipitation'));
        $avgTempMax  = count($dailyData) ? round(array_sum(array_column($dailyData, 'temp_max')) / count($dailyData), 1) : 0;
        $avgTempMin  = count($dailyData) ? round(array_sum(array_column($dailyData, 'temp_min')) / count($dailyData), 1) : 0;
        $maxRainDay  = max(array_column($dailyData, 'precipitation') ?: [0]);

        // Current weather
        $currentHumidityIdx = 0; // Use first hourly reading as approximate current
        $currentHumidity    = $hourly['relative_humidity_2m'][$currentHumidityIdx] ?? null;

        $alerts = $this->checkAlerts($totalRain, $maxRainDay);

        return [
            'current' => [
                'temperature'  => $current['temperature'] ?? null,
                'wind_speed'   => $current['windspeed'] ?? null,
                'weather_code' => $current['weathercode'] ?? 0,
                'weather_label'=> $this->weatherCodeLabel($current['weathercode'] ?? 0),
                'humidity'     => $currentHumidity,
                'is_day'       => $current['is_day'] ?? 1,
            ],
            'daily'      => $dailyData,
            'aggregates' => [
                'total_rain_7d'   => round($totalRain, 1),
                'avg_temp_max_7d' => $avgTempMax,
                'avg_temp_min_7d' => $avgTempMin,
                'max_rain_day'    => round($maxRainDay, 1),
            ],
            'alerts'     => $alerts,
            'fetched_at' => now()->toIso8601String(),
            'api_source' => 'Open-Meteo',
        ];
    }

    /**
     * Generate drought/flood alerts based on precipitation thresholds.
     */
    public function checkAlerts(float $totalRain7d, float $maxRainDay): array
    {
        $alerts = [];

        if ($totalRain7d < 5.0) {
            $alerts[] = [
                'type'    => 'drought',
                'level'   => 'warning',
                'title'   => 'Drought Risk Alert',
                'message' => "Only {$totalRain7d}mm of rain in the last 7 days. Consider scheduling irrigation.",
                'icon'    => 'sun-fill',
                'color'   => 'warning',
            ];
        }

        if ($maxRainDay > 50.0) {
            $alerts[] = [
                'type'    => 'flood',
                'level'   => 'danger',
                'title'   => 'Heavy Rainfall Alert',
                'message' => "Extreme rainfall of {$maxRainDay}mm expected. Monitor fields for waterlogging.",
                'icon'    => 'cloud-rain-heavy-fill',
                'color'   => 'danger',
            ];
        }

        return $alerts;
    }

    /**
     * Minimal fallback data when API is unreachable.
     */
    private function fallbackWeather(): array
    {
        return [
            'current'    => ['temperature' => null, 'humidity' => null, 'wind_speed' => null, 'weather_label' => 'Unavailable'],
            'daily'      => [],
            'aggregates' => ['total_rain_7d' => 0, 'avg_temp_max_7d' => null, 'avg_temp_min_7d' => null, 'max_rain_day' => 0],
            'alerts'     => [],
            'fetched_at' => now()->toIso8601String(),
            'api_source' => 'Offline',
            'error'      => 'Weather data temporarily unavailable.',
        ];
    }

    /**
     * Convert WMO weather code to human-readable label.
     */
    private function weatherCodeLabel(int $code): string
    {
        return match (true) {
            $code === 0              => 'Clear Sky',
            in_array($code, [1,2,3])=> 'Partly Cloudy',
            in_array($code, [45,48])=> 'Foggy',
            in_array($code, [51,53,55]) => 'Drizzle',
            in_array($code, [61,63,65]) => 'Rain',
            in_array($code, [71,73,75]) => 'Snow',
            in_array($code, [80,81,82]) => 'Rain Showers',
            in_array($code, [95,96,99]) => 'Thunderstorm',
            default                  => 'Unknown',
        };
    }
}
