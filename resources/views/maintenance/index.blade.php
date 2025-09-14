<!-- STUDENT NAME: Loh Yun Le
STUDENT ID: 23WMR14583 -->

@extends('layouts.app')

@section('title', 'Admin - Maintenance Records')

@section('content')
<div class="container">
    
    <h1 class="mb-4">All Maintenance Records</h1>

    <a href="{{ route('maintenance.create') }}" class="btn btn-success w-100 py-3 fs-5">+ Schedule Maintenance</a><br><br>

    <form method="GET" action="{{ route('maintenance.index') }}" class="row gx-2 gy-2 mb-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label small text-muted mb-1 fw-bold">Search</label>
            <input type="text" name="search" class="form-control"
                placeholder="Search vehicle, type, notes, or cost…"
                value="{{ request('search') }}">
        </div>

        <div class="col-md-3">
            <label class="form-label small text-muted mb-1 fw-bold">Filter by</label>
            <select name="status" class="form-select">
                <option value="">-- Status --</option>
                <option value="Scheduled" {{ request('status')=='Scheduled' ? 'selected' : '' }}>Scheduled</option>
                <option value="Completed" {{ request('status')=='Completed' ? 'selected' : '' }}>Completed</option>
                <option value="Cancelled" {{ request('status')=='Cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label small text-muted mb-1 fw-bold">Sort by</label>
            <select name="sort" class="form-select">
                <option value="service_date" {{ request('sort','created_at')=='service_date' ? 'selected' : '' }}>Service Date</option>
                <option value="updated_at"   {{ request('sort','created_at')=='updated_at'   ? 'selected' : '' }}>Updated At</option>
                <option value="created_at"   {{ request('sort','created_at')=='created_at'   ? 'selected' : '' }}>Created At</option>
                <option value="cost"         {{ request('sort','created_at')=='cost'         ? 'selected' : '' }}>Cost</option>
            </select>
        </div>

        <div class="col-md-2">
            <label class="form-label small text-muted mb-1 fw-bold">Order</label>
            @php $dir = request('direction','desc'); @endphp
            <div class="d-flex gap-3">
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="direction" id="asc" value="asc" {{ $dir=='asc' ? 'checked' : '' }}>
                    <label class="form-check-label" for="asc">ASC</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="direction" id="desc" value="desc" {{ $dir=='desc' ? 'checked' : '' }}>
                    <label class="form-check-label" for="desc">DESC</label>
                </div>
            </div>
        </div>

        <div class="col-md-12 d-flex justify-content-center gap-3 mt-2">
            <button type="submit" class="btn btn-outline-primary" style="width: 200px;">Apply</button>
            <a href="{{ route('maintenance.index') }}" class="btn btn-outline-secondary" style="width: 200px;">Reset</a>
        </div>
    </form>

    <table class="table table-striped table-bordered">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Vehicle</th>
                <th>Type</th>
                <th>Notes</th>
                <th>Service Date</th>
                <th>Cost (RM)</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($records as $r)
                @php
                    $serviceDate = \Carbon\Carbon::parse($r->service_date);
                    $isOverdue = $r->status === 'Scheduled' && $serviceDate->isBefore(now()->startOfDay());
                @endphp

                <tr class="{{ $isOverdue ? 'table-warning' : '' }}">
                    <td>{{ $r->id }}</td>

                    <td class="small">
                        <div class="text-muted">#{{ $r->vehicle_id }}</div>
                        @if($r->relationLoaded('vehicle') && $r->vehicle)
                            <div>{{ $r->vehicle->brand }} {{ $r->vehicle->model }}</div>
                            <div class="text-muted">{{ $r->vehicle->registration_number }}</div>
                        @endif
                    </td>

                    <td>{{ $r->maintenance_type }}</td>

                    <td>
                        @if($r->notes)
                            {{ \Illuminate\Support\Str::limit($r->notes, 50) }}
                        @else
                            —
                        @endif
                    </td>

                    <td>{{ $serviceDate->format('d-m-Y') }}</td>
                    <td>{{ number_format($r->cost, 2) }}</td>
                    
                    <td>
                        @php
                            $badge = match ($r->status) {
                                'Scheduled' => 'bg-info',
                                'Completed' => 'bg-success',
                                'Cancelled' => 'bg-danger',
                                default => 'bg-secondary',
                            };
                        @endphp

                        <span class="badge {{ $badge }}">{{ $r->status }}</span>

                        @if($isOverdue)
                            <span class="badge bg-warning text-dark">Overdue</span>
                        @endif
                    </td>

                    <td>
                        <a href="{{ route('maintenance.edit', $r) }}" class="btn btn-warning">Edit</a>
                        <form action="{{ route('maintenance.destroy', $r->id) }}" method="POST"
                              onsubmit="return confirm('Delete this maintenance?')"
                              class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">No maintenance records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection