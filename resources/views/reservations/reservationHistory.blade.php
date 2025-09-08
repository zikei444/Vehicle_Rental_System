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
                            <button class="btn btn-primary" onclick="openModal({{ $reservation->id }})">
                                Rate Now
                            </button>                        @endif
                </div>
            </div>
        @endforeach
    @endif
</div>

<!-- Rate Modal -->
<div class="modal" id="rateModal" style="display:none; position:fixed; top:20%; left:35%; width:30%; background:#fff; border:1px solid #ccc; padding:20px; z-index:1000;">
    <h4>Rate Vehicle</h4>
    <form id="rateForm">
        <label for="score">Rating (1-5):</label>
        <select id="score" name="score" class="form-control" required>
            <option value="">--Select--</option>
            <option value="1">1 ⭐</option>
            <option value="2">2 ⭐⭐</option>
            <option value="3">3 ⭐⭐⭐</option>
            <option value="4">4 ⭐⭐⭐⭐</option>
            <option value="5">5 ⭐⭐⭐⭐⭐</option>
        </select>
        <br>
        <label for="content">Comment (optional):</label>
        <textarea id="content" name="content" class="form-control" rows="3"></textarea>
        <input type="hidden" id="reservation_id">
        <br>
        <button type="submit" class="btn btn-success">Submit</button>
        <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
    </form>
</div>

<!-- new added by xy -->
 <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function openModal(reservationId) {
        $("#reservation_id").val(reservationId);
        $("#rateModal").show();
    }

    function closeModal() {
        $("#rateModal").hide();
        $("#rateForm")[0].reset();
    }

    $("#rateForm").on("submit", function(e) {
        e.preventDefault();

        const reservationId = $("#reservation_id").val();

        $.ajax({
            url: `/api/reservations/${reservationId}/review`,
            method: "POST",
            data: {
                score: $("#score").val(),
                content: $("#content").val()
            },
            success: function(response) {
                alert("Review submitted!");
                closeModal();

                // 更新该条记录显示
                $(`#reservation-${reservationId}`).html(`
                    <button class="btn btn-success" disabled>
                        Rated (${response.rating.score}⭐)
                    </button>
                    ${response.comment ? `<p class="mt-2"><em>"${response.comment.content}"</em></p>` : ""}
                `);
            },
            error: function(xhr) {
                alert("Error: " + (xhr.responseJSON.error || "Failed"));
            }
        });
    });
</script>


@endsection
