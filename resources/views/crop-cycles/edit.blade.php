@extends('layouts.app')
@section('title', 'Edit Crop Cycle')
@section('page-title', '✏️ Edit Crop Cycle')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h2>Edit Crop Cycle</h2>
        <p>Update crop cycle details.</p>
    </div>
    <a href="{{ route('crop-cycles.show', $cropCycle) }}" class="btn-outline"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<div style="max-width: 800px;">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="bi bi-pencil me-2 text-primary"></i>Crop Cycle Details</h3>
        </div>
        <div class="card-body">

            <form action="{{ route('crop-cycles.update', $cropCycle) }}" method="POST">
                @csrf
                @method('PUT')

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">

                    <div class="form-group">
                        <label class="form-label">Dataset <span class="required">*</span></label>
                        <select name="dataset_id" class="form-control" required>
                            @foreach($datasets as $dataset)
                                <option value="{{ $dataset->id }}" {{ old('dataset_id', $cropCycle->dataset_id) == $dataset->id ? 'selected' : '' }}>
                                    {{ $dataset->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('dataset_id') <div class="form-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Crop Type <span class="required">*</span></label>
                        <input type="text" name="crop_type" class="form-control" value="{{ old('crop_type', $cropCycle->crop_type) }}" required>
                        @error('crop_type') <div class="form-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Variety</label>
                        <input type="text" name="variety" class="form-control" value="{{ old('variety', $cropCycle->variety) }}">
                        @error('variety') <div class="form-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Region <span class="required">*</span></label>
                        <input type="text" name="region" class="form-control" value="{{ old('region', $cropCycle->region) }}" required>
                        @error('region') <div class="form-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Field ID</label>
                        <input type="text" name="field_id" class="form-control" value="{{ old('field_id', $cropCycle->field_id) }}">
                        @error('field_id') <div class="form-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Season Year <span class="required">*</span></label>
                        <input type="number" name="season_year" class="form-control" value="{{ old('season_year', $cropCycle->season_year) }}" required>
                        @error('season_year') <div class="form-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Season <span class="required">*</span></label>
                        <select name="season" class="form-control" required>
                            @foreach(['Kharif', 'Rabi', 'Zaid', 'Summer', 'Winter', 'Year-round'] as $season)
                                <option value="{{ $season }}" {{ old('season', $cropCycle->season) == $season ? 'selected' : '' }}>{{ $season }}</option>
                            @endforeach
                        </select>
                        @error('season') <div class="form-error">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Sowing Date</label>
                        <input type="date" name="sowing_date" class="form-control" value="{{ old('sowing_date', optional($cropCycle->sowing_date)->toDateString()) }}">
                        @error('sowing_date') <div class="form-error">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Harvest Date</label>
                        <input type="date" name="harvest_date" class="form-control" value="{{ old('harvest_date', optional($cropCycle->harvest_date)->toDateString()) }}">
                        @error('harvest_date') <div class="form-error">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Actual Yield</label>
                        <input type="number" step="0.01" name="actual_yield" class="form-control" value="{{ old('actual_yield', $cropCycle->actual_yield) }}">
                        @error('actual_yield') <div class="form-error">{{ $message }}</div> @enderror
                    </div>

                </div>

                <div class="form-group">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="3">{{ old('notes', $cropCycle->notes) }}</textarea>
                    @error('notes') <div class="form-error">{{ $message }}</div> @enderror
                </div>

                <button type="submit" class="btn-primary-green" style="width:100%; justify-content:center;">
                    <i class="bi bi-save"></i> Update Crop Cycle
                </button>
            </form>

        </div>
    </div>
</div>
@endsection
