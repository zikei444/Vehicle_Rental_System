@extends('layouts.app')

@section('title', 'Admin - Maintenance Records')

@section('content')
<div class="container">
    <h1 class="bg-success text-white p-3 rounded">Edit Maintenance #{{ $maintenance->id }}</h1>

    <form method="POST" action="{{ route('maintenance.update', $maintenance) }}">
        @csrf
        @method('PUT')

        <div class="card p-3 shadow-sm">
            <h4>Vehicle: #{{ $maintenance->vehicle_id }}</h4>

            <!-- Use app timezone when displaying timestamps -->
            @php($tz = config('app.timezone'))

            <div class="text-muted mb-3">
                <div class="text-primary">Created: {{ $maintenance->created_at?->timezone($tz)->format('Y-m-d H:i') }}</div>
                <div class="text-warning-emphasis">Updated: {{ $maintenance->updated_at?->timezone($tz)->format('Y-m-d H:i') }}</div>
                <div class="text-success">Completed: {{ $maintenance->completed_at ? $maintenance->completed_at->timezone($tz)->format('Y-m-d H:i') : '—' }}</div>
            </div>

            <div>
                <label for="maintenance_type">Type</label>
                <input id="maintenance_type" name="maintenance_type" value="{{ old('maintenance_type', $maintenance->maintenance_type) }}" placeholder="Enter Maintenance Service Type" class="form-control mb-3" required>
            </div>

            <div>
                <label for="service_date">Service Date</label>
                <input id="service_date" type="date" name="service_date" value="{{ old('service_date', optional($maintenance->service_date)->format('Y-m-d')) }}" class="form-control mb-3" required>
            </div>

            <div>
                <label for="status" class="form-label">Status</label>
                <select id="status" name="status" class="form-select" required>
                    @php($current = old('status', $maintenance->status))
                    @foreach (['Scheduled','Completed','Cancelled'] as $s)
                        <option value="{{ $s }}" {{ $current === $s ? 'selected' : '' }}>
                            {{ $s }}
                        </option>
                    @endforeach
                </select>
            </div>
            <br>
            <div>
                <label for="cost">Cost</label>
                <input id="cost" type="number" step="any" min="1" name="cost" value="{{ old('cost', $maintenance->cost) }}" placeholder="RM 0.00" class="form-control mb-3" required>
            </div>

            <div>
                <label for="notes">Notes</label>
                <input id="notes" name="notes" value="{{ old('notes', $maintenance->notes) }}" placeholder="Add remarks for this maintenance (max 500 chars)" class="form-control mb-3">
            </div>
        </div>        
        <br>
        <button type="submit" class="btn btn-success">Update</button>
        <a href="{{ route('maintenance.index') }}" class="btn btn-secondary">Back to All Maintenance Records</a>
    </form>
</div>
@endsection