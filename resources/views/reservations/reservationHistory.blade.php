@extends('layouts.app')

@section('content')
<div class="container my-5">
    <h2 class="mb-4">Reservation History</h2>

    @if($reservations->isEmpty())
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> You have no past reservations.
        </div>
    @else
        @foreach($reservations as $reservation)
            <div class="card mb-3 shadow-sm rounded-4">
                <div class="card-header bg-secondary text-white rounded-top-4">
                    <strong>Status:</strong> {{ ucfirst($reservation->status) }}
                </div>
                <div class="card-body">
                    <p><strong>Vehicle:</strong> {{ $reservation->vehicle->brand ?? 'Unknown' }} {{ $reservation->vehicle->model ?? '' }} ({{ $reservation->vehicle->registration_number ?? 'Unknown' }})</p>
                    <p><strong>Pickup:</strong> {{ $reservation->pickup_date }}</p>
                    <p><strong>Return:</strong> {{ $reservation->return_date }}</p>
                    <p><strong>Days:</strong> {{ $reservation->days }}</p>
                    <p><strong>Total Cost:</strong> RM {{ number_format($reservation->total_cost, 2) }}</p>
                    @if($reservation->hasRated)
                        <button class="btn btn-success" disabled>Rated</button>
                    @else
                        <a href="{{ route('rating.create', ['vehicle' => $reservation->vehicle_id]) }}" class="btn btn-primary">Rate Vehicle</a>
                        @endif
                </div>
            </div>
        @endforeach
    @endif
</div>
@endsection
