<!-- 
STUDENT NAME: LIEW ZI KEI 
STUDENT ID: 23WMR14570
-->

@extends('layouts.app')

@section('content')
<div class="container py-4">

    <!-- Page Title -->
    <h1 class="bg-success text-white p-4 rounded shadow-sm text-center">
        Reservation
    </h1>

    @if($vehicle)
        <div class="card shadow-lg mt-4 border-0">
            <div class="card-body">
                <!-- Vehicle Info -->
                <div class="d-flex align-items-center mb-4">
                    <div class="me-3">
                        <i class="bi bi-car-front-fill text-success" style="font-size: 3rem;"></i>
                    </div>
                    <div>
                        <h4 class="mb-1 text-success">{{ $vehicle['brand'] }} {{ $vehicle['model'] }}</h4>
                        <p class="mb-0 text-muted">Type: {{ $vehicle['type'] }}</p>
                        <p class="mb-0 fw-bold">RM {{ $vehicle['rental_price'] }} / day</p>
                    </div>
                </div>

                <!-- Reservation Form -->
                <form id="reservation-form">
                    @csrf
                    <input type="hidden" name="vehicle_id" value="{{ $vehicle['id'] }}">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">📅 Pickup Date</label>
                            <!-- Today and onwards can pick -->
                            <input type="date" name="pickup_date" id="pickup_date" class="form-control" min="{{ \Carbon\Carbon::today()->toDateString() }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">🕑 Number of Days</label>
                            <input type="number" name="days" id="days" class="form-control" min="1" required>
                        </div>
                    </div>
                </form>

                <!-- Cost Result -->
                <div id="cost-result" class="alert mt-4" style="display:none;"></div>

                <!-- Proceed form -->
                <form id="proceed-form" action="{{ route('reservation.confirm') }}" method="POST" style="display:none;">
                    @csrf
                    <input type="hidden" name="vehicle_id" id="confirm_vehicle_id">
                    <input type="hidden" name="pickup_date" id="confirm_pickup">
                    <input type="hidden" name="return_date" id="confirm_return">
                    <input type="hidden" name="days" id="confirm_days">
                    <input type="hidden" name="total_cost" id="confirm_total">
                    <button type="submit" class="btn btn-primary w-100 mt-3 fw-semibold shadow-sm">
                        💳 Proceed to Payment
                    </button>
                </form>
            </div>
        </div>
    @else
        <div class="alert alert-danger mt-4 shadow-sm">
            Vehicle not found.
        </div>
    @endif

    <!-- Back Button -->
    <div class="text-center mt-4">
        <a href="{{ url('vehicles') }}" class="btn btn-outline-secondary px-4">
            Back to Vehicle Selection
        </a>
    </div>
</div>

@if(isset($error_popup))
<script>
    window.addEventListener('DOMContentLoaded', function() {
        alert("{{ $error_popup }}"); // simple alert
    });
</script>
@endif

<!-- Script -->
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
                resultDiv.className = "alert alert-danger mt-4";
                resultDiv.innerText = data.error;
                proceedForm.style.display = "none";
            } else {
                resultDiv.style.display = "block";
                resultDiv.className = "alert alert-success mt-4";

                // Build breakdown HTML
                let breakdownHtml = '';
                if (data.breakdown) {
                    breakdownHtml = '<ul class="mb-2">';
                    for (const [key, value] of Object.entries(data.breakdown)) {
                        breakdownHtml += `<li><strong>${key.replace('_', ' ')}:</strong> RM ${parseFloat(value).toFixed(2)}</li>`;
                    }
                    breakdownHtml += '</ul>';
                }

                resultDiv.innerHTML = `
                    <h5 class="mb-2">✅ Reservation Details</h5>
                    <p><strong>Total Cost:</strong> <span class="fw-bold text-success">RM ${parseFloat(data.totalCost).toFixed(2)}</span></p>
                    <p><strong>Pickup:</strong> ${data.pickup}</p>
                    <p><strong>Return:</strong> ${data.return}</p>
                    <p><strong>Days:</strong> ${data.days}</p>
                    <p><em>${data.message}</em></p>
                `;

                // Populate hidden form fields
                document.getElementById("confirm_vehicle_id").value = data.vehicle_id;
                document.getElementById("confirm_pickup").value = data.pickup;
                document.getElementById("confirm_return").value = data.return;
                document.getElementById("confirm_days").value = data.days;
                document.getElementById("confirm_total").value = data.totalCost;

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
