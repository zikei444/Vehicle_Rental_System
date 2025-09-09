@extends('layouts.app')

@section('content')
<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Reservation Ratings</h2>
        <a href="{{ url()->previous() }}" class="btn btn-secondary">Back</a>
    </div>

    <div class="card shadow-sm rounded-4">
        <div class="card-header bg-secondary text-white rounded-top-4">
            <strong>Vehicle:</strong> {{ $reservation->vehicle->brand ?? 'Unknown' }} {{ $reservation->vehicle->model ?? '' }}
        </div>
        <div class="card-body">
           @if($rating)
        <p><strong>Rating:</strong> {{ $rating->rating }} ⭐</p>
        <p><strong>Comment:</strong> {{ $rating->feedback ?? 'No comment' }}</p>
        <p><strong>Date:</strong> {{ $rating->created_at->format('d/m/Y H:i') }}</p>
            @else
                <div class="alert alert-info">No rating submitted for this reservation.</div>
            @endif
        </div>
    </div>
    <p><strong>Admin Reply:</strong> {{ $rating->admin_reply ?? 'No reply yet' }}</p>

</div>
@endsection
