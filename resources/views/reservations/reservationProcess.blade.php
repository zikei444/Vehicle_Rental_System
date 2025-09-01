@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="bg-success text-white p-3 rounded">Reservation Process</h1>

    @if($vehicle)
        <div class="card p-3 shadow-sm">
            <p><strong>Vehicle:</strong> {{ $vehicle['brand'] }} {{ $vehicle['model'] }} ({{ $vehicle['type'] }})</p>
            <p><strong>Price per day:</strong> RM {{ $vehicle['rental_price'] }}</p>

            <form id="reservation-form">
                @csrf
                <input type="hidden" name="vehicle_id" value="{{ $vehicle['id'] }}">

                <label>Pickup Date:</label>
                <input type="date" name="pickup_date" id="pickup_date" class="form-control mb-3" required>

                <label>Number of Days:</label>
                <input type="number" name="days" id="days" class="form-control mb-3" min="1" required>
            </form>

            <div id="cost-result" class="alert alert-info mt-3" style="display:none;"></div>

            <!-- Proceed form (hidden until cost is shown) -->
            <form id="proceed-form" action="{{ route('reservation.confirm') }}" method="POST" style="display:none;">
                @csrf
                <input type="hidden" name="vehicle_id" id="confirm_vehicle_id">
                <input type="hidden" name="pickup_date" id="confirm_pickup">
                <input type="hidden" name="return_date" id="confirm_return">
                <input type="hidden" name="days" id="confirm_days">
                <input type="hidden" name="total_cost" id="confirm_total">
                <button type="submit" class="btn btn-primary mt-3">Proceed to Payment</button>
            </form>
        </div>
    @else
        <p>Vehicle not found.</p>
    @endif

    <a href="{{ url('vehicles') }}" class="btn btn-secondary mt-3">Back to Vehicle Selection</a>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const pickupInput = document.getElementById("pickup_date");
    const daysInput = document.getElementById("days");
    const resultDiv = document.getElementById("cost-result");
    const proceedForm = document.getElementById("proceed-form");

    function calculateCost() {
        const pickup = pickupInput.value;
        const days = daysInput.value;
        const vehicleId = document.querySelector("input[name='vehicle_id']").value;

        if (pickup && days > 0) {
            fetch("{{ route('reservation.calculate.ajax') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value
                },
                body: JSON.stringify({
                    vehicle_id: vehicleId,
                    pickup_date: pickup,
                    days: days
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.error) {
                    resultDiv.style.display = "block";
                    resultDiv.className = "alert alert-danger mt-3";
                    resultDiv.innerText = data.error;
                    proceedForm.style.display = "none";
                } else {
                    resultDiv.style.display = "block";
                    resultDiv.className = "alert alert-success mt-3";
                    resultDiv.innerHTML = `
                        <h4>Total Cost: RM ${data.totalCost}</h4>
                        <p><strong>Pickup:</strong> ${data.pickup}</p>
                        <p><strong>Return:</strong> ${data.return}</p>
                        <p><strong>Days:</strong> ${data.days}</p>
                    `;

                    // Populate hidden form fields
                    document.getElementById("confirm_vehicle_id").value = data.vehicle_id;
                    document.getElementById("confirm_pickup").value = data.pickup;
                    document.getElementById("confirm_return").value = data.return;
                    document.getElementById("confirm_days").value = data.days;
                    document.getElementById("confirm_total").value = data.totalCost;

                    // Show proceed form
                    proceedForm.style.display = "block";
                }
            });
        }
    }

    pickupInput.addEventListener("change", calculateCost);
    daysInput.addEventListener("input", calculateCost);
});
</script>
@endsection