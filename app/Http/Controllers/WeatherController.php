<?php

namespace App\Http\Controllers;

use App\Models\FarmField;
use App\Services\WeatherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WeatherController extends Controller
{
    public function __construct(protected WeatherService $weatherService) {}

    /**
     * AJAX endpoint: fetch weather for given lat/lng coordinates.
     * Used by chatbot 'show_weather' command and crop cycle show page.
     */
    public function fetch(Request $request): JsonResponse
    {
        $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        $weather = $this->weatherService->fetchWeather(
            (float) $request->lat,
            (float) $request->lng
        );

        return response()->json($weather);
    }

    /**
     * Fetch weather for a specific FarmField by its stored center coordinates.
     */
    public function fetchForField(FarmField $farmField): JsonResponse
    {
        // Ensure user can only fetch weather for their own fields
        if ($farmField->user_id !== (string) auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $lat = $farmField->center_lat;
        $lng = $farmField->center_lng;

        if (!$lat || !$lng) {
            return response()->json([
                'error' => 'Farm field does not have valid coordinates.',
            ], 422);
        }

        $weather = $this->weatherService->fetchWeather($lat, $lng);

        return response()->json(array_merge($weather, [
            'field_name' => $farmField->name,
            'field_id'   => (string) $farmField->id,
        ]));
    }
}
