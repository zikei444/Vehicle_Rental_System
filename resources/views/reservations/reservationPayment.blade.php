<!-- 
STUDENT NAME: LIEW ZI KEI 
STUDENT ID: 23WMR14570
-->

@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1 class="bg-success text-white p-4 rounded shadow-sm text-center">
        <i class="bi bi-credit-card"></i> Reservation Payment
    </h1>

    {{-- Display validation errors --}}
    @if ($errors->any())
        <div class="alert alert-danger shadow-sm">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li><i class="bi bi-exclamation-circle"></i> {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-lg p-4">
        {{-- Vehicle Summary --}}
        <div class="mb-3">
            @if($vehicle)
                <h4 class="text-secondary"><i class="bi bi-car-front-fill"></i> Vehicle Summary</h4>
                <p class="mb-1"><strong>Vehicle:</strong> {{ $vehicle['brand'] ?? '' }} {{ $vehicle['model'] ?? '' }}</p>
            @else
                <p class="text-danger"><i class="bi bi-exclamation-triangle"></i> Vehicle details not available.</p>
            @endif

            <p class="mb-1"><strong>Pickup Date:</strong> {{ $pickup_date }}</p>
            <p class="mb-1"><strong>Return Date:</strong> {{ $return_date }}</p>
            <p class="mb-1"><strong>Days:</strong> {{ $days }}</p>
            <p class="fw-bold text-success h5">
                <i class="bi bi-cash-coin"></i> Total Cost: RM {{ number_format($total_cost, 2) }}
            </p>
        </div>

        <hr>

        {{-- Payment Form --}}
        <form action="{{ route('reservation.payment.process') }}" method="POST">
            @csrf
            <input type="hidden" name="vehicle_id" value="{{ $vehicle['id'] }}">
            <input type="hidden" name="pickup_date" value="{{ $pickup_date }}">
            <input type="hidden" name="return_date" value="{{ $return_date }}">
            <input type="hidden" name="days" value="{{ $days }}">
            <input type="hidden" name="total_cost" value="{{ $total_cost }}">

            <h4 class="text-primary"><i class="bi bi-wallet2"></i> Select Payment Method</h4>

            <div class="list-group mb-3">
                @php $oldMethod = old('payment_method'); @endphp
                <label class="list-group-item">
                    <input class="form-check-input me-2" type="radio" name="payment_method" id="cash" value="cash"
                        {{ $oldMethod === 'cash' ? 'checked' : '' }} required>
                    <i class="bi bi-cash"></i> Cash on Delivery
                </label>
                <label class="list-group-item">
                    <input class="form-check-input me-2" type="radio" name="payment_method" id="card" value="card"
                        {{ $oldMethod === 'card' ? 'checked' : '' }}>
                    <i class="bi bi-credit-card-2-front"></i> Credit/Debit Card
                </label>
                <label class="list-group-item">
                    <input class="form-check-input me-2" type="radio" name="payment_method" id="bank" value="bank_transfer"
                        {{ $oldMethod === 'bank_transfer' ? 'checked' : '' }}>
                    <i class="bi bi-bank"></i> Bank Transfer
                </label>
            </div>

            {{-- Card Fields --}}
            <div id="card-fields" class="mt-3 p-3 border rounded bg-light shadow-sm
                 {{ $oldMethod === 'card' ? '' : 'd-none' }}">
                <h5 class="text-dark"><i class="bi bi-credit-card"></i> Card Details</h5>
                <div class="mb-2">
                    <label>Cardholder Name</label>
                    <input type="text" name="card_name" value="{{ old('card_name') }}" class="form-control"
                           placeholder="John Doe" {{ $oldMethod === 'card' ? 'required' : '' }}>
                </div>
                <div class="mb-2">
                    <label>Card Number</label>
                    <input type="text" name="card_number" value="{{ old('card_number') }}" class="form-control"
                           placeholder="1234 5678 9012 3456" {{ $oldMethod === 'card' ? 'required' : '' }}>
                </div>
                <div class="mb-2">
                    <label>CVV</label>
                    <input type="password" name="cvv" value="{{ old('cvv') }}" class="form-control" maxlength="3"
                           placeholder="123" {{ $oldMethod === 'card' ? 'required' : '' }}>
                </div>
                <div class="row">
                    <div class="col">
                        <label>Expiry Month</label>
                        <input type="number" name="expiry_month" value="{{ old('expiry_month') }}" class="form-control"
                               min="1" max="12" placeholder="MM" {{ $oldMethod === 'card' ? 'required' : '' }}>
                    </div>
                    <div class="col">
                        <label>Expiry Year</label>
                        <input type="number" name="expiry_year" value="{{ old('expiry_year') }}" class="form-control"
                               min="{{ date('Y') }}" placeholder="YYYY" {{ $oldMethod === 'card' ? 'required' : '' }}>
                    </div>
                </div>
            </div>

            {{-- Bank Transfer --}}
            <div id="bank-fields" class="mt-3 p-3 border rounded bg-light shadow-sm text-center
                 {{ $oldMethod === 'bank_transfer' ? '' : 'd-none' }}">
                <h5><i class="bi bi-qr-code-scan"></i> Scan QR Code to Pay</h5>
                <img src="{{ asset('images/bankTransfer.jpg') }}" alt="Bank QR" class="img-fluid mb-2" style="max-width:200px;">
                <p class="text-muted">Please scan this QR code with your bank app to complete payment.</p>
            </div>

            <button type="submit" class="btn btn-primary w-100 mt-3 fw-semibold shadow-sm">
                <i class="bi bi-check-circle"></i> Confirm Payment
            </button>
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

        // remove required dynamically
        cardFields.querySelectorAll("input").forEach(input => input.required = false);

        if (cardRadio.checked) {
            cardFields.classList.remove("d-none");
            cardFields.querySelectorAll("input").forEach(input => input.required = true);
        }
        if (bankRadio.checked) bankFields.classList.remove("d-none");
    }

    [cardRadio, bankRadio, cashRadio].forEach(radio => {
        radio.addEventListener("change", toggleFields);
    });
});
</script>
@endsection
