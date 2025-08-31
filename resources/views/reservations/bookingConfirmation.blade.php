@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h2 class="mb-4">Reservation Confirmation</h2>

    <div class="card p-4">
        <h5>Vehicle: Toyota Corolla</h5>
        <p>Type: Car</p>
        <p>Pick-Up Date: 2025-09-01</p>
        <p>Return Date: 2025-09-03</p>
        <p>Total Cost: RM 450.00</p>
        <p>Status: Confirmed</p>

        <a href="{{ url('/reservations') }}" class="btn btn-primary mt-3">Back to My Reservations</a>
    </div>
</div>
@endsection