@extends('layouts.app')

@section('content')
    <h1>Edit Maintenance #{{ $maintenance->id }}</h1>

    <p>Vehicle: #{{ $maintenance->vehicle_id }}</p>

    {{-- Use app timezone when displaying timestamps --}}
    @php($tz = config('app.timezone'))

    <div class="text-muted" style="margin-bottom:12px;">
        Created:
        {{ $maintenance->created_at?->timezone($tz)->format('Y-m-d H:i') }}
        • Updated:
        {{ $maintenance->updated_at?->timezone($tz)->format('Y-m-d H:i') }}
        • Completed:
        {{ $maintenance->completed_at ? $maintenance->completed_at->timezone($tz)->format('Y-m-d H:i') : '—' }}
    </div>

    <form method="post" action="{{ route('maintenance.update', $maintenance) }}">
        @csrf
        @method('put')

        <div>
            <label for="maintenance_type">Type</label>
            <input id="maintenance_type" name="maintenance_type"
                   value="{{ old('maintenance_type', $maintenance->maintenance_type) }}" required>
            @error('maintenance_type') <div style="color:red">{{ $message }}</div> @enderror
        </div>

        <div>
            <label for="service_date">Service Date</label>
            <input id="service_date" type="date" name="service_date"
                   value="{{ old('service_date', optional($maintenance->service_date)->format('Y-m-d')) }}" required>
            @error('service_date') <div style="color:red">{{ $message }}</div> @enderror
        </div>

        <div>
            <label for="status">Status</label>
            <select id="status" name="status" required>
                @foreach (['Scheduled','Completed','Cancelled'] as $s)
                    <option value="{{ $s }}" {{ old('status', $maintenance->status) === $s ? 'selected' : '' }}>
                        {{ $s }}
                    </option>
                @endforeach
            </select>
            @error('status') <div style="color:red">{{ $message }}</div> @enderror
        </div>

        <div>
            <label for="cost">Cost</label>
            <input id="cost" type="number" step="0.01" min="0" name="cost"
                   value="{{ old('cost', $maintenance->cost) }}">
            @error('cost') <div style="color:red">{{ $message }}</div> @enderror
        </div>

        <div>
            <label for="notes">Notes</label>
            <textarea id="notes" name="notes">{{ old('notes', $maintenance->notes) }}</textarea>
            @error('notes') <div style="color:red">{{ $message }}</div> @enderror
        </div>

        <button type="submit">Update</button>
        <a href="{{ route('maintenance.index') }}">Back</a>
    </form>
@endsection