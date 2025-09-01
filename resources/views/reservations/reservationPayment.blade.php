@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="bg-primary text-white p-3 rounded">Reservation Payment</h1>

    {{-- Display validation errors --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card p-3 shadow-sm">
        @if($vehicle)
            <p><strong>Vehicle:</strong> {{ $vehicle['brand'] ?? '' }} {{ $vehicle['model'] ?? '' }}</p>
        @else
            <p class="text-danger">Vehicle details not available.</p>
        @endif

        <p><strong>Pickup Date:</strong> {{ $pickup_date }}</p>
        <p><strong>Return Date:</strong> {{ $return_date }}</p>
        <p><strong>Days:</strong> {{ $days }}</p>
        <p><strong>Total Cost:</strong> RM {{ $total_cost }}</p>

        <hr>

        <form action="{{ route('reservation.payment.process') }}" method="POST">
            @csrf
            <input type="hidden" name="vehicle_id" value="{{ $vehicle['id'] }}">
            <input type="hidden" name="pickup_date" value="{{ $pickup_date }}">
            <input type="hidden" name="return_date" value="{{ $return_date }}">
            <input type="hidden" name="days" value="{{ $days }}">
            <input type="hidden" name="total_cost" value="{{ $total_cost }}">

            <h4>Select Payment Method</h4>

            <div class="form-check">
                <input class="form-check-input" type="radio" name="payment_method" id="cash" value="cash" required>
                <label class="form-check-label" for="cash">Cash on Delivery</label>
            </div>

            <div class="form-check">
                <input class="form-check-input" type="radio" name="payment_method" id="card" value="card">
                <label class="form-check-label" for="card">Credit/Debit Card</label>
            </div>

            <div id="card-fields" class="mt-3 p-3 border rounded d-none">
                <h5>Card Details</h5>
                <div class="mb-2">
                    <label>Cardholder Name</label>
                    <input type="text" name="card_name" class="form-control" placeholder="John Doe">
                </div>
                <div class="mb-2">
                    <label>Card Number</label>
                    <input type="text" name="card_number" class="form-control" placeholder="1234 5678 9012 3456">
                </div>
                <div class="mb-2">
                    <label>CVV</label>
                    <input type="password" name="cvv" class="form-control" maxlength="3" placeholder="123">
                </div>
                <div class="row">
                    <div class="col">
                        <label>Expiry Month</label>
                        <input type="number" name="expiry_month" class="form-control" min="1" max="12" placeholder="MM">
                    </div>
                    <div class="col">
                        <label>Expiry Year</label>
                        <input type="number" name="expiry_year" class="form-control" min="{{ date('Y') }}" placeholder="YYYY">
                    </div>
                </div>
            </div>

            <div class="form-check mt-3">
                <input class="form-check-input" type="radio" name="payment_method" id="bank" value="bank_transfer">
                <label class="form-check-label" for="bank">Bank Transfer</label>
            </div>

            <div id="bank-fields" class="mt-3 p-3 border rounded d-none">
                <h5>Scan QR Code to Pay</h5>
                <img src="{{ asset('images/bank_qr_placeholder.png') }}" alt="Bank QR" width="200">
                <p class="text-muted">Please scan this QR code with your bank app to complete payment.</p>
            </div>

            <button type="submit" class="btn btn-success mt-3">Confirm Payment</button>
        </form>
    </div>
</div>

{{-- Toggle payment fields --}}
<script>
document.addEventListener("DOMContentLoaded", function () {
    const cardRadio = document.getElementById("card");
    const bankRadio = document.getElementById("bank");
    const cashRadio = document.getElementById("cash");
    const cardFields = document.getElementById("card-fields");
    const bankFields = document.getElementById("bank-fields");

    function toggleFields() {
        cardFields.classList.add("d-none");
        bankFields.classList.add("d-none");

        if (cardRadio.checked) cardFields.classList.remove("d-none");
        if (bankRadio.checked) bankFields.classList.remove("d-none");
    }

    [cardRadio, bankRadio, cashRadio].forEach(radio => {
        radio.addEventListener("change", toggleFields);
    });
});
</script>
@endsection
