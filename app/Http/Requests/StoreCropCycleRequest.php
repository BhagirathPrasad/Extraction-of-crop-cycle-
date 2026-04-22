<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCropCycleRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check(); }

    public function rules(): array
    {
        return [
            'dataset_id'      => 'required|exists:datasets,id',
            'crop_type'       => 'required|string|max:100',
            'variety'         => 'nullable|string|max:100',
            'region'          => 'required|string|max:100',
            'field_id'        => 'nullable|string|max:50',
            'season_year'     => 'required|integer|min:1990|max:2100',
            'season'          => 'required|in:Kharif,Rabi,Zaid,Summer,Winter,Year-round',
            'sowing_date'     => 'nullable|date',
            'emergence_date'  => 'nullable|date|after_or_equal:sowing_date',
            'tillering_date'  => 'nullable|date|after_or_equal:emergence_date',
            'peak_growth_date'=> 'nullable|date',
            'maturity_date'   => 'nullable|date',
            'harvest_date'    => 'nullable|date|after_or_equal:sowing_date',
            'ndvi_max'        => 'nullable|numeric|min:-1|max:1',
            'ndvi_min'        => 'nullable|numeric|min:-1|max:1',
            'ndvi_mean'       => 'nullable|numeric|min:-1|max:1',
            'yield_prediction'=> 'nullable|numeric|min:0',
            'actual_yield'    => 'nullable|numeric|min:0',
            'notes'           => 'nullable|string|max:2000',
        ];
    }
}
