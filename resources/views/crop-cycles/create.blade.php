@extends('layouts.app')
@section('title', 'Create Crop Cycle')
@section('page-title', '🌱 Create Crop Cycle')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h2>Create Crop Cycle</h2>
        <p>Manually define a crop cycle and its parameters.</p>
    </div>
    <a href="{{ route('crop-cycles.index') }}" class="btn-outline"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<div style="max-width: 800px;">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="bi bi-plus-circle me-2 text-primary"></i>Crop Cycle Details</h3>
        </div>
        <div class="card-body">

            <form action="{{ route('crop-cycles.store') }}" method="POST">
                @csrf

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">

                    <div class="form-group">
                        <label class="form-label">Dataset <span class="required">*</span></label>
                        <select name="dataset_id" class="form-control" required>
                            <option value="">Select a dataset...</option>
                            @foreach($datasets as $dataset)
                                <option value="{{ $dataset->id }}" {{ old('dataset_id') == $dataset->id ? 'selected' : '' }}>
                                    {{ $dataset->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('dataset_id') <div class="form-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Crop Type <span class="required">*</span></label>
                        <input type="text" name="crop_type" class="form-control" value="{{ old('crop_type') }}" required>
                        @error('crop_type') <div class="form-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Variety</label>
                        <input type="text" name="variety" class="form-control" value="{{ old('variety') }}">
                        @error('variety') <div class="form-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Region <span class="required">*</span></label>
                        <input type="text" name="region" class="form-control" value="{{ old('region') }}" required>
                        @error('region') <div class="form-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Field ID</label>
                        <input type="text" name="field_id" class="form-control" value="{{ old('field_id') }}">
                        @error('field_id') <div class="form-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Season Year <span class="required">*</span></label>
                        <input type="number" name="season_year" class="form-control" value="{{ old('season_year', date('Y')) }}" required>
                        @error('season_year') <div class="form-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Season <span class="required">*</span></label>
                        <select name="season" class="form-control" required>
                            <option value="">Select season...</option>
                            @foreach(['Kharif', 'Rabi', 'Zaid', 'Summer', 'Winter', 'Year-round'] as $season)
                                <option value="{{ $season }}" {{ old('season') == $season ? 'selected' : '' }}>{{ $season }}</option>
                            @endforeach
                        </select>
                        @error('season') <div class="form-error">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Sowing Date</label>
                        <input type="date" name="sowing_date" class="form-control" value="{{ old('sowing_date') }}">
                        @error('sowing_date') <div class="form-error">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Harvest Date</label>
                        <input type="date" name="harvest_date" class="form-control" value="{{ old('harvest_date') }}">
                        @error('harvest_date') <div class="form-error">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Actual Yield</label>
                        <input type="number" step="0.01" name="actual_yield" class="form-control" value="{{ old('actual_yield') }}">
                        @error('actual_yield') <div class="form-error">{{ $message }}</div> @enderror
                    </div>

                </div>

                <div class="form-group">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                    @error('notes') <div class="form-error">{{ $message }}</div> @enderror
                </div>

                <button type="submit" class="btn-primary-green" style="width:100%; justify-content:center;">
                    <i class="bi bi-save"></i> Create Crop Cycle
                </button>
            </form>

        </div>
    </div>
</div>
@endsection
