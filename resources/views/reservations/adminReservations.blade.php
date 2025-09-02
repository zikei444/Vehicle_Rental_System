@extends('layouts.app')

@section('title', 'Admin - Reservations')

@section('content')
<div class="container">
    <h1 class="mb-4">All Reservations</h1>

    <table class="table table-striped table-bordered">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Customer ID</th>
                <th>Vehicle ID</th>
                <th>Pickup</th>
                <th>Return</th>
                <th>Days</th>
                <th>Total Cost</th>
                <th>Payment Method</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reservations as $res)
            <tr>
                <td>{{ $res['id'] }}</td>
                <td>{{ $res['customer_id'] }}</td>
                <td>{{ $res['vehicle_id'] }}</td>
                <td>{{ $res['pickup_date'] }}</td>
                <td>{{ $res['return_date'] }}</td>
                <td>{{ $res['days'] }}</td>
                <td>{{ $res['total_cost'] }}</td>
                <td>{{ ucfirst($res['payment_method']) }}</td>
                <td>
                    <span class="badge 
                        {{ $res['status'] === 'ongoing' ? 'bg-warning' : ($res['status'] === 'completed' ? 'bg-success' : 'bg-danger') }}">
                        {{ ucfirst($res['status']) }}
                    </span>
                </td>
                <td>
                    @if($res['status'] === 'ongoing')
                        <!-- Complete Form -->
                        <form action="{{ route('admin.reservations.updateStatus', $res['id']) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Mark this reservation as completed?');">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="completed">
                            <button type="submit" class="btn btn-sm btn-success">Complete</button>
                        </form>

                        <!-- Cancel Form -->
                        <form action="{{ route('admin.reservations.updateStatus', $res['id']) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Mark this reservation as cancelled?');">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="cancelled">
                            <button type="submit" class="btn btn-sm btn-danger">Cancel</button>
                        </form>
                    @endif

                    <!-- Delete Form -->
                    <form action="{{ route('admin.reservations.destroy', $res['id']) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this reservation?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
