@extends('layouts.app')

@section('content')
    <h1>Maintenance Records</h1>

    <p>
        <a href="{{ route('maintenance.create') }}">+ Schedule Maintenance</a>
    </p>

    <table border="1" cellpadding="6" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Vehicle</th>
                <th>Type</th>
                <th>Service Date</th>
                <th>Cost</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($records as $r)
                <tr>
                    <td>{{ $r->id }}</td>
                    <td>#{{ $r->vehicle_id }}</td>
                    <td>{{ $r->maintenance_type }}</td>
                    <td>{{ \Illuminate\Support\Carbon::parse($r->service_date)->format('Y-m-d') }}</td>
                    <td>{{ number_format($r->cost, 2) }}</td>
                    <td>{{ $r->status }}</td>
                    <td>
                        <a href="{{ route('maintenance.edit', $r) }}">Edit</a>
                        <form action="{{ route('maintenance.destroy', $r) }}"
                              method="post"
                              style="display:inline">
                            @csrf
                            @method('delete')
                            <button onclick="return confirm('Delete this record?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">No maintenance records yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{ $records->links() }}
@endsection