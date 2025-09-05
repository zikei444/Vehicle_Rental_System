@extends('layouts.app')

@section('content')
<div class="container">
    <h2>My Rental History</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Vehicle</th>
                <th>Pickup Date</th>
                <th>Return Date</th>
                <th>Status</th>
                <th>Rating</th>
                <th>Feedback</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        @foreach($reservations as $res)
            <tr id="res-{{ $res->id }}">
                <td>{{ $res->vehicle->brand }} {{ $res->vehicle->model }}</td>
                <td>{{ $res->pickup_date }}</td>
                <td>{{ $res->return_date }}</td>
                <td>{{ ucfirst($res->status) }}</td>
                <td class="rating">
                    @if($res->rating)
                        {{ $res->rating->rating }}/5
                    @else
                        <input type="number" min="1" max="5" class="rating-input" 
                               data-reservation="{{ $res->id }}" 
                               data-vehicle="{{ $res->vehicle->id }}">
                    @endif
                </td>
                <td class="feedback">
                    @if($res->rating && $res->rating->feedback)
                        {{ $res->rating->feedback }}
                    @else
                        <input type="text" class="feedback-input" data-reservation="{{ $res->id }}">
                    @endif
                </td>
                <td class="action">
                    @if($res->status == 'completed' && !$res->rating)
                        <button class="btn btn-primary btn-sm submit-rating" 
                                data-reservation="{{ $res->id }}" 
                                data-vehicle="{{ $res->vehicle->id }}">Submit</button>
                    @elseif($res->rating)
                        <span class="badge bg-success">Rated</span>
                    @else
                        -
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<script>
document.querySelectorAll('.submit-rating').forEach(button => {
    button.addEventListener('click', async function() {
        let resId = this.dataset.reservation;
        let vehicleId = this.dataset.vehicle;

        let ratingInput = document.querySelector(`.rating-input[data-reservation='${resId}']`);
        let feedbackInput = document.querySelector(`.feedback-input[data-reservation='${resId}']`);

        let rating = ratingInput.value;
        let feedback = feedbackInput.value;

        if (!rating) {
            alert('Please enter a rating (1-5).');
            return;
        }

        try {
            let res = await fetch('/api/ratings', {
                method: 'POST',
                headers: {'Content-Type':'application/json'},
                body: JSON.stringify({
                    rental_id: resId,
                    vehicle_id: vehicleId,
                    user_id: {{ auth()->user()->id }},
                    rating: rating,
                    comment: feedback
                })
            });

            let json = await res.json();
            alert(json.message);

            if (json.status === 'success') {
                // Update table instantly
                document.querySelector(`#res-${resId} .rating`).innerText = rating + '/5';
                document.querySelector(`#res-${resId} .feedback`).innerText = feedback || '-';
                document.querySelector(`#res-${resId} .action`).innerHTML = '<span class="badge bg-success">Rated</span>';
            }
        } catch (err) {
            console.error(err);
            alert('Error submitting rating. Please try again.');
        }
    });
});
</script>
@endsection
