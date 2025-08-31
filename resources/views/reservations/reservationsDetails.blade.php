@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h2 class="mb-4">Modify Reservation</h2>

    <form>
        <div class="mb-3">
            <label class="form-label">Customer Name</label>
            <input type="text" class="form-control" value="John Doe" disabled>
        </div>
        <div class="mb-3">
            <label class="form-label">Vehicle</label>
            <input type="text" class="form-control" value="Toyota Corolla" disabled>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="pickup_date" class="form-label">Pick-Up Date</label>
                <input type="date" id="pickup_date" name="pickup_date" class="form-control" value="2025-09-01">
            </div>
            <div class="col-md-6">
                <label for="return_date" class="form-label">Return Date</label>
                <input type="date" id="return_date" name="return_date" class="form-control" value="2025-09-03">
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Total Cost</label>
            <input type="text" class="form-control" value="RM 450.00" disabled>
        </div>
        <div class="mb-3">
            <label class="form-label">Status</label>
            <select class="form-select">
                <option selected>Confirmed</option>
                <option>Cancelled</option>
                <option>Pending</option>
            </select>
        </div>

        <button type="submit" class="btn btn-success">Save Changes</button>
        <a href="{{ url('/admin/reservations') }}" class="btn btn-secondary">Back</a>
    </form>
</div>
@endsection