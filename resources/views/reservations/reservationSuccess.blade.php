@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="bg-success text-white p-3 rounded">Reservation Successful</h1>

    <div class="alert alert-success">
        ✅ Your reservation has been confirmed!
    </div>

    <p><strong>Vehicle ID:</strong> {{ $vehicle_id }}</p>
    <p><strong>Pickup Date:</strong> {{ $pickup_date }}</p>
    <p><strong>Return Date:</strong> {{ $return_date }}</p>
    <p><strong>Days:</strong> {{ $days }}</p>
    <p><strong>Total Cost:</strong> RM {{ $total_cost }}</p>
    <p><strong>Payment Method:</strong> {{ $payment_method }}</p>

    <a href="{{ url('vehicles') }}" class="btn btn-primary mt-3">Back to Vehicles</a>
</div>
@endsection