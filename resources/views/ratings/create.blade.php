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

$("#review-form").on("submit", function(e) {
        e.preventDefault();

        $.ajax({
            url: `/api/vehicles/${vehicleId}/review`,
            method: "POST",
            data: {
                score: $("#score").val(),
                content: $("#content").val()
            },
            success: function(response) {
                $("#response-box").text(JSON.stringify(response, null, 4));
                alert("Review submitted successfully!");
                $("#review-form")[0].reset();
            },
            error: function(xhr) {
                $("#response-box").text(JSON.stringify(xhr.responseJSON, null, 4));
                alert("Error: " + xhr.responseJSON.message);
            }
        });
    });
</script>
@endsection
