@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h2 class="mb-4">Reservation Summary</h2>

    <!-- Vehicle Info -->
    <div class="card mb-4">
        <div class="row g-0">
            <div class="col-md-4">
                <img src="{{ asset('images/vehicles/placeholder.png') }}" class="img-fluid rounded-start" alt="Vehicle Image">
            </div>
            <div class="col-md-8">
                <div class="card-body">
                    <h5 class="card-title">Vehicle Brand Model</h5>
                    <p class="card-text">
                        Type: Car/Truck/Van <br>
                        Price per day: RM 0.00
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Selection Form -->
    <form>
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="pickup_date" class="form-label">Pick-Up Date</label>
                <input type="date" id="pickup_date" name="pickup_date" class="form-control">
            </div>
            <div class="col-md-6">
                <label for="return_date" class="form-label">Return Date</label>
                <input type="date" id="return_date" name="return_date" class="form-control">
            </div>
        </div>

        <!-- Cost Display -->
        <div class="mb-3">
            <h5>Total Cost: RM 0.00</h5>
        </div>

        <!-- Buttons -->
        <button type="submit" class="btn btn-primary">Calculate / Update Cost</button>
        <a href="#" class="btn btn-success">Confirm Reservation</a>
    </form>
</div>
@endsection