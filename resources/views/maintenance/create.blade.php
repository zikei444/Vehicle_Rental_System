@extends('layouts.app')

@section('title', 'Admin - Schedule Maintenance')

@section('content')
<div class="container">
    <h1 class="bg-success text-white p-3 rounded">Schedule Maintenance</h1>

    <!-- Create form: posts to maintenance.store to create a new maintenance record -->
    <form method="post" action="{{ route('maintenance.store') }}">
        @csrf

        <div class="card p-3 shadow-sm">
            <div>
                <label for="vehicle_id" class="form-label">Vehicle</label>
                <select id="vehicle_id" name="vehicle_id" class="form-select" required>
                    @foreach ($vehicles as $v)
                        <!-- Only show available vehicle -->
                        <option value="{{ $v->id }}" {{ old('vehicle_id') == $v->id ? 'selected' : '' }}>
                            #{{ $v->id }} {{ $v->model ?? '' }} {{ $v->registration_number ?? '' }}
                        </option>
                    @endforeach
                </select>
                @error('vehicle_id') <div style="color:red">{{ $message }}</div> @enderror
            </div>
            <br>
            <div>
                <label for="maintenance_type">Type</label>
                <input id="maintenance_type" name="maintenance_type" value="{{ old('maintenance_type') }}" class="form-control mb-3" required>
                @error('maintenance_type') <div style="color:red">{{ $message }}</div> @enderror
            </div>

            <div>
                <label for="service_date">Service Date</label>
                <input id="service_date" type="date" name="service_date" value="{{ old('service_date') }}" class="form-control mb-3" required>
                @error('service_date') <div style="color:red">{{ $message }}</div> @enderror
            </div>

            <div>
                <label for="cost">Cost</label>
                <input id="cost" type="number" step="0.01" min="0" name="cost" value="{{ old('cost') }}" class="form-control mb-3" required>
                @error('cost') <div style="color:red">{{ $message }}</div> @enderror
            </div>

            <div>
                <label for="notes">Notes</label>
                <input id="notes" name="notes" class="form-control mb-3">{{ old('notes') }}</input>
                @error('notes') <div style="color:red">{{ $message }}</div> @enderror
            </div>
        </div>
        <br>
        <button type="submit" class="btn btn-success">Save</button>
        <a href="{{ route('maintenance.index') }}" class="btn btn-secondary">Back to All Maintenance Records</a>
    </form>
</div>
@endsection