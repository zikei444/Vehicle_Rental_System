
@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Rate Your Rental</h2>
    <p>Vehicle: <strong>{{ $reservation->vehicle->brand }} {{ $reservation->vehicle->model }}</strong></p>

    <form action="{{ route('ratings.store', $reservation->id) }}" method="POST">
        @csrf

        <div class="mb-3">
            <label class="form-label">Rating (1-5):</label>
            <input type="number" name="rating" min="1" max="5" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Feedback:</label>
            <textarea name="feedback" class="form-control"></textarea>
        </div>

        <button type="submit" class="btn btn-success">Submit Feedback</button>
    </form>
</div>
@endsection
