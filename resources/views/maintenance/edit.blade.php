@extends('layouts.app')

@section('title', 'Admin - Maintenance Records')

@section('content')
<div class="container">
    <h1 class="bg-success text-white p-3 rounded">
        Edit Maintenance #{{ $maintenance->id }}
    </h1>

    <form method="POST" action="{{ route('maintenance.update', $maintenance) }}">
        @csrf
        @method('PUT')

        <div class="card p-3 shadow-sm">
            <!-- Vehicle Details -->
            @php($v = $maintenance->vehicle)
            <div class="alert alert-secondary">
                <div class="fw-bold fs-4">Vehicle #{{ $maintenance->vehicle_id }}</div>
                <div>
                    Brand: <span class="text-muted">{{ $v->brand ?? 'N/A' }}</span><br>
                    Model: <span class="text-muted">{{ $v->model ?? 'N/A' }}</span><br>
                    Year: <span class="text-muted">{{ $v->year_of_manufacture ?? 'N/A' }}</span><br>
                    Reg. No: <span class="text-muted">{{ $v->registration_number ?? 'N/A' }}</span>
                </div>
            </div>

            <div class="text-muted mb-3">
                <div class="text-primary">
                    Created at: {{ $maintenance->created_at?->format('Y-m-d H:i') }}
                </div>
                <div class="text-warning-emphasis">
                    Updated at: {{ $maintenance->updated_at?->format('Y-m-d H:i') }}
                </div>
                <div class="text-success">
                    Completed at: {{ $maintenance->completed_at ? $maintenance->completed_at->format('Y-m-d H:i') : '—' }}
                </div>
            </div>

            <!-- Maintenance Form Fields -->
            <div>
                <label for="maintenance_type" class="form-label">Type</label>
                <input id="maintenance_type" name="maintenance_type"
                       value="{{ old('maintenance_type', $maintenance->maintenance_type) }}"
                       class="form-control mb-3" required {{ $terminal ? 'readonly' : '' }}>
            </div>

            <div>
                <label for="service_date" class="form-label">Service Date</label>
                <input id="service_date" type="date" name="service_date"
                       value="{{ old('service_date', optional($maintenance->service_date)->format('Y-m-d')) }}"
                       class="form-control mb-3" required {{ $terminal ? 'readonly' : '' }}>
            </div>

            <div class="mb-3">
                <label for="status" class="form-label">Status</label>

                <select id="status" name="status" class="form-control" {{ $terminal ? 'disabled' : '' }}>
                    <option value="Scheduled"  {{ $maintenance->status==='Scheduled'  ? 'selected' : '' }}>Scheduled</option>
                    <option value="Completed"  {{ $maintenance->status==='Completed'  ? 'selected' : '' }}>Completed</option>
                    <option value="Cancelled"  {{ $maintenance->status==='Cancelled'  ? 'selected' : '' }}>Cancelled</option>
                </select>

                @if($terminal)
                    <!-- Disabled inputs don’t submit; hidden keeps server-side consistent -->
                    <input type="hidden" name="status" value="{{ $maintenance->status }}">
                    <small class="text-muted">
                        This maintenance is {{ strtolower($maintenance->status) }} and its status cannot be changed.
                    </small>
                @endif

                @error('status')
                  <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label for="cost" class="form-label">Cost</label>
                <input id="cost" type="number" step="any" min="1" name="cost"
                       value="{{ old('cost', $maintenance->cost) }}"
                       class="form-control mb-3" required {{ $terminal ? 'readonly' : '' }}>
            </div>

            <div>
                <label for="notes" class="form-label">Notes</label>
                <textarea id="notes" name="notes" rows="3"
                          class="form-control mb-3" {{ $terminal ? 'readonly' : '' }}>{{ old('notes', $maintenance->notes) }}</textarea>
            </div>
        </div>

        <br>
        <button type="submit" class="btn btn-success" {{ $terminal ? 'disabled' : '' }}>Update</button>
        <a href="{{ route('maintenance.index') }}" class="btn btn-outline-secondary">Back to All Maintenance Records</a>
    </form>
</div>
@endsection