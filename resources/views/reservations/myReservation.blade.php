@extends('layouts.app')

@section('content')
<div class="container my-5">
    <h2 class="mb-4 text-center text-primary">My Reservation</h2>

    @if($reservation)
        <div class="card shadow-lg rounded-4 border-0">
            <div class="card-header bg-gradient-primary text-white rounded-top-4 py-3 text-center">
                <h5 class="mb-0"><i class="bi bi-car-front-fill me-2"></i> Ongoing Reservation</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush mb-3">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><strong>Vehicle:</strong></span>
                        <span>{{ $reservation->vehicle->brand ?? 'Unknown' }} {{ $reservation->vehicle->model ?? '' }} ({{ $reservation->vehicle->registration_number ?? 'Unknown' }})</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><strong>Pickup:</strong></span>
                        <span>{{ $reservation->pickup_date }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><strong>Return:</strong></span>
                        <span>{{ $reservation->return_date }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><strong>Days:</strong></span>
                        <span>{{ $reservation->days }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><strong>Total Cost:</strong></span>
                        <span class="text-success fw-bold">RM {{ number_format($reservation->total_cost, 2) }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><strong>Status:</strong></span>
                        <span class="badge bg-info text-dark">{{ ucfirst($reservation->status ?? 'Ongoing') }}</span>
                    </li>
                </ul>

                <div class="text-center mt-3">
                    <form action="{{ route('reservations.complete', $reservation->id) }}" 
                        method="POST" 
                        class="d-inline"
                        onsubmit="return confirm('Mark this reservation as complete?');">
                        @csrf
                        <button type="submit" class="btn btn-success btn-lg shadow-sm me-2">
                            <i class="bi bi-check-circle me-2"></i> Mark as Complete
                        </button>
                    </form>

                    <form action="{{ route('reservations.cancel', $reservation->id) }}" 
                        method="POST" 
                        class="d-inline"
                        onsubmit="return confirm('Are you sure you want to cancel this reservation? This cannot be undone.');">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-lg shadow-sm">
                            <i class="bi bi-x-circle me-2"></i> Cancel Rental
                        </button>
                    </form>
                </div>

                <div class="text-center mt-3">
                    <a href="{{ url('vehicles') }}" class="btn btn-primary btn-lg shadow-sm">
                        <i class="bi bi-arrow-left-circle me-2"></i> Back to Vehicles
                    </a>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-info shadow-sm d-flex align-items-center rounded-4" role="alert">
            <i class="bi bi-info-circle me-2 fs-4"></i>
            <div>You have no ongoing reservations.</div>
        </div>
    @endif

    @if(isset($noOngoing) && $noOngoing)
    <script>
        window.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'info',
                title: 'No Ongoing Reservation',
                text: 'You currently have no ongoing reservations.',
                confirmButtonColor: '#0d6efd'
            });
        });
        
    </script>
    @endif
</div>
@endsection
