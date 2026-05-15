@extends('layouts.app')
@section('title', 'Edit Dataset')
@section('page-title', '✏️ Edit Dataset')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h2>Edit Dataset: {{ $dataset->name }}</h2>
        <p>Update dataset metadata.</p>
    </div>
    <a href="{{ route('datasets.index') }}" class="btn-outline"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<div style="max-width: 800px;">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="bi bi-pencil me-2 text-primary"></i>Dataset Information</h3>
        </div>
        <div class="card-body">

            <form action="{{ route('datasets.update', $dataset) }}" method="POST">
                @csrf
                @method('PUT')

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">

                    <div class="form-group">
                        <label class="form-label">Dataset Name <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control"
                               value="{{ old('name', $dataset->name) }}" required>
                        @error('name') <div class="form-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Crop Type</label>
                        <select name="crop_type" class="form-control">
                            <option value="">Select crop...</option>
                            @foreach(['wheat','rice','maize','cotton','soybean','barley','millet','sugarcane','sunflower','mustard'] as $crop)
                                <option value="{{ $crop }}" {{ old('crop_type', strtolower($dataset->crop_type)) === strtolower($crop) ? 'selected' : '' }}>{{ ucfirst($crop) }}</option>
                            @endforeach
                        </select>
                        @error('crop_type') <div class="form-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Region / State</label>
                        <select name="region" class="form-control">
                            <option value="">Select region...</option>
                            @foreach(['Punjab','Haryana','Uttar Pradesh','Madhya Pradesh','Maharashtra','Gujarat','Rajasthan','Bihar','Andhra Pradesh','Karnataka','Tamil Nadu','Telangana','West Bengal','Odisha','Assam'] as $r)
                                <option value="{{ $r }}" {{ old('region', $dataset->region) === $r ? 'selected' : '' }}>{{ $r }}</option>
                            @endforeach
                        </select>
                        @error('region') <div class="form-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Country</label>
                        <input type="text" name="country" class="form-control" value="{{ old('country', $dataset->country) }}">
                        @error('country') <div class="form-error">{{ $message }}</div> @enderror
                    </div>

                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description', $dataset->description) }}</textarea>
                    @error('description') <div class="form-error">{{ $message }}</div> @enderror
                </div>

                <button type="submit" class="btn-primary-green" style="width:100%; justify-content:center;">
                    <i class="bi bi-save"></i> Save Changes
                </button>
            </form>

        </div>
    </div>
</div>
@endsection
