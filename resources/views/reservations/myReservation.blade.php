@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="bg-primary text-white p-3 rounded">My Current Reservation</h1>

    @if(count($reservations) === 0)
        <div class="alert alert-info">You have no current reservations.</div>
    @else
        <div class="table-responsive">
            <table class="table table-bordered table-striped mt-3">
                <thead>
                    <tr>
                        <th>Reservation ID</th>
                        <th>Vehicle</th>
                        <th>Registration Number</th>
                        <th>Type</th>
                        <th>Pickup Date</th>
                        <th>Return Date</th>
                        <th>Days</th>
                        <th>Total Cost</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reservations as $res)
                        <tr>
                            <td>{{ $res['id'] }}</td>
                            <td>
                                @if($res['vehicle'])
                                    {{ $res['vehicle']['brand'] ?? '' }} {{ $res['vehicle']['model'] ?? '' }}
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>{{ $res['vehicle']['registration_number'] ?? 'N/A' }}</td>
                            <td>{{ $res['vehicle']['type'] ?? 'N/A' }}</td>
                            <td>{{ $res['pickup_date'] }}</td>
                            <td>{{ $res['return_date'] }}</td>
                            <td>{{ $res['days'] }}</td>
                            <td>RM {{ $res['total_cost'] }}</td>
                            <td>{{ ucfirst($res['status']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
