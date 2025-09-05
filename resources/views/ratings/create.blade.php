@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Rate Your Rental</h2>
    <p>Vehicle: <strong>{{ $rental->vehicle->brand }} {{ $rental->vehicle->model }}</strong></p>

    <form id="ratingForm">
        @csrf
        <input type="hidden" name="rental_id" value="{{ $rental->id }}">
        <input type="hidden" name="vehicle_id" value="{{ $rental->vehicle->id }}">
        <input type="hidden" name="user_id" value="{{ $rental->customer->user_id }}">

        <div class="mb-3">
            <label>Rating (1-5):</label>
            <input type="number" name="rating" min="1" max="5" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Feedback:</label>
            <textarea name="comment" class="form-control"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Submit Rating</button>
    </form>
</div>

<script>
document.getElementById('ratingForm').addEventListener('submit', async function(e){
    e.preventDefault();
    let formData = new FormData(this);
    let data = Object.fromEntries(formData.entries());

    let res = await fetch('/api/ratings', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify(data)
    });
    let json = await res.json();
    alert(json.message);
});
</script>
@endsection
