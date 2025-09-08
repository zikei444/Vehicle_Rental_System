@extends('layouts.app')

@section('content')
<div class="container my-5">
    <h2 class="mb-4">Reservation History</h2>

    @forelse($reservations as $reservation)
        <div class="card mb-3 shadow-sm rounded-4" id="reservation-{{ $reservation->id }}">
            <div class="card-header bg-secondary text-white rounded-top-4">
                <strong>Status:</strong> {{ ucfirst($reservation->status) }}
            </div>
            <div class="card-body">
                <p><strong>Vehicle:</strong> {{ $reservation->vehicle->brand ?? 'Unknown' }} {{ $reservation->vehicle->model ?? '' }}</p>
                <p><strong>Pickup:</strong> {{ $reservation->pickup_date }}</p>
                <p><strong>Return:</strong> {{ $reservation->return_date }}</p>
                <p><strong>Total Cost:</strong> RM {{ number_format($reservation->total_cost, 2) }}</p>
                @if($reservation->hasRated())
                    <button class="btn btn-success" disabled>Rated</button>
                    <a href="{{ route('ratings.viewRating', $reservation->id) }}" class="btn btn-info text-white">
                     View Rating
                    </a>
                @else
                    <button class="btn btn-primary" onclick="openModal({{ $reservation->id }}, {{ $reservation->vehicle->id }})">
                        Rate Now
                    </button>
                @endif

            </div>
        </div>
    @empty
        <div class="alert alert-info">You have no past reservations.</div>
    @endforelse
</div>

<!-- Rate Modal -->
<div class="modal" id="rateModal" style="display:none; position:fixed; top:20%; left:35%; width:30%; background:#fff; border-radius:15px; border:1px solid #ccc; padding:25px; z-index:1000; box-shadow: 0 5px 15px rgba(0,0,0,.3);">
    <h4 class="mb-3">Rate Vehicle</h4>
    <div id="success-message" class="alert alert-success d-none"></div>
    <div id="error-message" class="alert alert-danger d-none"></div>
    <form id="rateForm">
        <label for="score">Rating (1-5):</label>
        <select id="score" class="form-control mb-3" required>
            <option value="">--Select--</option>
            <option value="1">1 ⭐</option>
            <option value="2">2 ⭐⭐</option>
            <option value="3">3 ⭐⭐⭐</option>
            <option value="4">4 ⭐⭐⭐⭐</option>
            <option value="5">5 ⭐⭐⭐⭐⭐</option>
        </select>

        <label for="content">Comment (optional):</label>
        <textarea id="content" class="form-control mb-3" rows="3"></textarea>

        <input type="hidden" id="reservation_id">
        <input type="hidden" id="vehicle_id">

        <div class="d-flex justify-content-between">
            <button type="submit" class="btn btn-success">Submit</button>
            <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
        </div>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function openModal(reservationId, vehicleId) {
    $("#reservation_id").val(reservationId);
    $("#vehicle_id").val(vehicleId);
    $("#success-message").addClass('d-none').text('');
    $("#error-message").addClass('d-none').text('');
    $("#rateModal").fadeIn();
}

function closeModal() {
    $("#rateModal").fadeOut();
    $("#rateForm")[0].reset();
}

$("#rateForm").on("submit", function(e) {
    e.preventDefault();

    const reservationId = $("#reservation_id").val();
    const vehicleId = $("#vehicle_id").val();
    const score = $("#score").val();
    const content = $("#content").val();

    $.ajax({
        url: `/api/ratings`,
        method: "POST",
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        contentType: "application/json",
        data: JSON.stringify({
            customer_id: {{ auth()->id() ?? 1 }}, // 登录用户
            vehicle_id: vehicleId,
            rating: score,
            feedback: content
        }),
        success: function(response) {
            $("#success-message").removeClass('d-none').text('Review submitted successfully!');
            $("#error-message").addClass('d-none');
            closeModal();

            // 更新按钮为 Rated
            $(`#reservation-${reservationId} button`).replaceWith(
                `<button class="btn btn-success" disabled>Rated (${score}⭐)</button>`
            );
        }

        error: function(xhr) {
            const msg = xhr.responseJSON?.error || (xhr.responseJSON?.errors ? Object.values(xhr.responseJSON.errors).flat().join(', ') : 'Failed');
            $("#error-message").removeClass('d-none').text(msg);
            $("#success-message").addClass('d-none');
        }
    });
});

</script>
@endsection
