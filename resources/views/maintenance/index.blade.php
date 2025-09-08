@extends('layouts.app')

@section('title', 'Admin - Maintenance Records')

@section('content')
<div class="container">
    
    <h1 class="mb-4">All Maintenance Records</h1>

    <p>
        <a href="{{ route('maintenance.create') }}" class="btn btn-success">+ Schedule Maintenance</a>
    </p>

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
                    $isOverdue = $r->status === 'Scheduled' && \Illuminate\Support\Carbon::parse($r->service_date)->isPast();
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

                    <td>{{ \Illuminate\Support\Carbon::parse($r->service_date)->format('d-m-Y') }}</td>
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

                        @if ($isOverdue)
                            <span class="badge bg-warning text-dark">Overdue</span>
                        @endif
                    </td>

                    <td>
                        <a href="{{ route('maintenance.edit', $r) }}" class="btn btn-warning">Edit</a>
                        <form action="{{ route('maintenance.destroy', $r) }}" method="post" style="display:inline">
                            @csrf
                            @method('delete')
                            <button onclick="return confirm('Delete this record?')" class="btn btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">No maintenance records yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $records->links() }}
@endsection

