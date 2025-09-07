@extends('layouts.app')

@section('content')
<div class="container my-5">
    <div class="card shadow-lg border-0 rounded-4">
        <div class="card-header bg-success text-white text-center rounded-top-4 py-4">
            <h2 class="mb-0">
                <i class="bi bi-check-circle-fill me-2"></i> Reservation Successful
            </h2>
        </div>

        <div class="card-body p-4">
            <div class="alert alert-success d-flex align-items-center" role="alert">
                <i class="bi bi-hand-thumbs-up-fill me-2"></i>
                <div>Your reservation has been confirmed!</div>
            </div>

            @if($reservation_id)
                <p><strong>Reservation ID:</strong> <span class="text-primary">{{ $reservation_id }}</span></p>
            @endif

            <ul class="list-group list-group-flush mb-4">
                <li class="list-group-item"><strong>🚗 Vehicle:</strong> {{ $vehicle->registration_number ?? 'Unknown' }}</li>
                <li class="list-group-item"><strong>📅 Pickup Date:</strong> {{ $pickup_date }}</li>
                <li class="list-group-item"><strong>📅 Return Date:</strong> {{ $return_date }}</li>
                <li class="list-group-item"><strong>📌 Days:</strong> {{ $days }}</li>
                <li class="list-group-item"><strong>💰 Total Cost:</strong> RM {{ number_format($total_cost, 2) }}</li>
                <li class="list-group-item"><strong>💳 Payment Method:</strong> {{ $payment_method }}</li>
            </ul>

            <div class="text-center mt-4">
                <a href="{{ url('vehicles') }}" class="btn btn-lg btn-primary px-4">
                    <i class="bi bi-arrow-left-circle me-2"></i> Back to Vehicles
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
