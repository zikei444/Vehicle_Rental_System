@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Add New Vehicle</h1>

    <form action="{{ route('admin.vehicles.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- General Vehicle Info --}}
        <div class="mb-3">
            <label>Type</label>
            <select name="type" id="vehicleType" class="form-control" required>
                <option value="">Select Type</option>
                <option value="car">Car</option>
                <option value="truck">Truck</option>
                <option value="van">Van</option>
            </select>
        </div>

        <div class="mb-3">
            <label>Brand</label>
            <input type="text" name="brand" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Model</label>
            <input type="text" name="model" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Year Manufacturer</label>
            <input type="number" name="year_of_manufacture" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Registration Number</label>
            <input type="text" name="registration_number" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Rental Price</label>
            <input type="number" step="0.01" name="rental_price" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Status</label>
            <select name="availability_status" class="form-control" required>
                <option value="available">Available</option>
                <option value="rented">Rented</option>
                <option value="reserved">Reserved</option>
                <option value="under_maintenance">Under Maintenance</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="image" class="form-label">Vehicle Image</label>
            <input type="file" name="image" class="form-control" id="image">
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="insurance_doc" class="form-label">Insurance Document</label>
                <input type="file" name="insurance_doc" class="form-control" id="insurance_doc">
            </div>
            <div class="col-md-4">
                <label for="registration_doc" class="form-label">Registration Document</label>
                <input type="file" name="registration_doc" class="form-control" id="registration_doc">
            </div>
            <div class="col-md-4">
                <label for="roadtax_doc" class="form-label">Road Tax Document</label>
                <input type="file" name="roadtax_doc" class="form-control" id="roadtax_doc">
            </div>
        </div>

        {{-- Type-Specific Fields --}}
        <div id="carFields" class="type-specific d-none">
            <h4>Car Details</h4>
            <div class="mb-3">
                <label>Fuel Type</label>
                <select name="fuel_type" class="form-control">
                    <option value="petrol">Petrol</option>
                    <option value="diesel">Diesel</option>
                    <option value="electric">Electric</option>
                </select>
            </div>
            <div class="mb-3">
                <label>Transmission</label>
                <select name="transmission" class="form-control">
                    <option value="manual">Manual</option>
                    <option value="automatic">Automatic</option>
                </select>
            </div>
            <div class="mb-3">
                <label>Air-Conditioning</label>
                <select name="air_conditioning" class="form-control">
                    <option value="yes">Yes</option>
                    <option value="no">No</option>
                </select>
            </div>
            <div class="mb-3">
                <label>Seats</label>
                <input type="number" name="seats" class="form-control">
            </div>
            <div class="mb-3">
                <label>Fuel Efficiency (km/l)</label>
                <input type="number" step="0.01" name="fuel_efficiency" class="form-control">
            </div>
        </div>

        <div id="truckFields" class="type-specific d-none">
            <h4>Truck Details</h4>
            <div class="mb-3">
                <label>Load Capacity (tons)</label>
                <input type="number" step="0.01" name="load_capacity" class="form-control">
            </div>
            <div class="mb-3">
                <label>Fuel Type</label>
                <select name="fuel_type" class="form-control">
                    <option value="petrol">Petrol</option>
                    <option value="diesel">Diesel</option>
                </select>
            </div>
            <div class="mb-3">
                <label>Truck Type</label>
                <select name="truck_type" class="form-control">
                    <option value="pickup">Pickup</option>
                    <option value="lorry">Lorry</option>
                    <option value="container">Container</option>
                    <option value="flatbed">Flatbed</option>
                </select>
            </div>
        </div>

        <div id="vanFields" class="type-specific d-none">
            <h4>Van Details</h4>
            <div class="mb-3">
                <label>Passenger Capacity</label>
                <input type="number" name="passenger_capacity" class="form-control">
            </div>
            <div class="mb-3">
                <label>Fuel Type</label>
                <select name="fuel_type" class="form-control">
                    <option value="petrol">Petrol</option>
                    <option value="diesel">Diesel</option>
                </select>
            </div>
            <div class="mb-3">
                <label>Air-Conditioning</label>
                <select name="air_conditioning" class="form-control">
                    <option value="yes">Yes</option>
                    <option value="no">No</option>
                </select>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Add Vehicle</button>
    </form>
</div>

<script>
    const vehicleType = document.getElementById('vehicleType');
    const typeSpecifics = document.querySelectorAll('.type-specific');

    vehicleType.addEventListener('change', function() {
        typeSpecifics.forEach(div => div.classList.add('d-none'));
        if (this.value === 'car') document.getElementById('carFields').classList.remove('d-none');
        if (this.value === 'truck') document.getElementById('truckFields').classList.remove('d-none');
        if (this.value === 'van') document.getElementById('vanFields').classList.remove('d-none');
    });
</script>
@endsection