@extends('layouts.app')

@section('content')
    <h1>Schedule Maintenance</h1>

    @if ($errors->any())
        <div>
            <ul>
                @foreach ($errors->all() as $e)
                    <li style="color:red">{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="post" action="{{ route('maintenance.store') }}">
        @csrf

        <div>
            <label for="vehicle_id">Vehicle</label>
            <select id="vehicle_id" name="vehicle_id" required>
                @foreach ($vehicles as $v)
                    <option value="{{ $v->id }}" {{ old('vehicle_id') == $v->id ? 'selected' : '' }}>
                        #{{ $v->id }} {{ $v->model ?? '' }} {{ $v->registration_number ?? '' }}
                    </option>
                @endforeach
            </select>
            @error('vehicle_id') <div style="color:red">{{ $message }}</div> @enderror
        </div>

        <div>
            <label for="maintenance_type">Type</label>
            <input id="maintenance_type" name="maintenance_type" value="{{ old('maintenance_type') }}" required>
            @error('maintenance_type') <div style="color:red">{{ $message }}</div> @enderror
        </div>

        <div>
            <label for="service_date">Service Date</label>
            <input id="service_date" type="date" name="service_date" value="{{ old('service_date') }}" required>
            @error('service_date') <div style="color:red">{{ $message }}</div> @enderror
        </div>

        <div>
            <label for="cost">Cost</label>
            <input id="cost" type="number" step="0.01" min="0" name="cost" value="{{ old('cost') }}">
            @error('cost') <div style="color:red">{{ $message }}</div> @enderror
        </div>

        <div>
            <label for="notes">Notes</label>
            <textarea id="notes" name="notes">{{ old('notes') }}</textarea>
            @error('notes') <div style="color:red">{{ $message }}</div> @enderror
        </div>

        <button type="submit">Save</button>
        <a href="{{ route('maintenance.index') }}">Back</a>
    </form>
@endsection