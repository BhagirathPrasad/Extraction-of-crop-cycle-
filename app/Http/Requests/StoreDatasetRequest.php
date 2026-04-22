<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDatasetRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check(); }

    public function rules(): array
    {
        return [
            'name'            => 'required|string|max:255',
            'description'     => 'nullable|string|max:1000',
            'type'            => 'required|in:CSV,GeoTIFF,JSON',
            'file'            => 'required|file|mimes:csv,txt,tif,tiff,json,geotiff|max:102400',
            'crop_type'       => 'nullable|string|max:100',
            'region'          => 'nullable|string|max:100',
            'country'         => 'nullable|string|max:100',
            'latitude'        => 'nullable|numeric|min:-90|max:90',
            'longitude'       => 'nullable|numeric|min:-180|max:180',
            'data_start_date' => 'nullable|date',
            'data_end_date'   => 'nullable|date|after_or_equal:data_start_date',
        ];
    }

    public function messages(): array
    {
        return [
            'file.max'       => 'File cannot be larger than 100 MB.',
            'file.mimes'     => 'Only CSV, GeoTIFF, and JSON files are supported.',
            'data_end_date.after_or_equal' => 'End date must be after or equal to start date.',
        ];
    }
}
