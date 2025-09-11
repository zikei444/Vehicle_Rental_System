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
                <option value="car" {{ old('type') == 'car' ? 'selected' : '' }}>Car</option>
                <option value="truck" {{ old('type') == 'truck' ? 'selected' : '' }}>Truck</option>
                <option value="van" {{ old('type') == 'van' ? 'selected' : '' }}>Van</option>
            </select>
        </div>

        <div class="mb-3">
            <label>Brand</label>
            <input type="text" name="brand" class="form-control" value="{{ old('brand') }}" required>
        </div>

        <div class="mb-3">
            <label>Model</label>
            <input type="text" name="model" class="form-control" value="{{ old('model') }}" required>
        </div>

        <div class="mb-3">
            <label>Year Manufacturer</label>
            <input type="number" name="year_of_manufacture" class="form-control" value="{{ old('year_of_manufacture') }}" required>
        </div>

        <div class="mb-3">
            <label>Registration Number</label>
            <input type="text" name="registration_number" class="form-control" value="{{ old('registration_number') }}" required>
            @error('registration_number')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label>Rental Price</label>
            <input type="number" step="0.01" name="rental_price" class="form-control" value="{{ old('rental_price') }}" required>
        </div>

        <div class="mb-3">
            <label>Status</label>
            <select name="availability_status" class="form-control" required>
                <option value="available" {{ old('availability_status') == 'available' ? 'selected' : '' }}>Available</option>
                <option value="rented" {{ old('availability_status') == 'rented' ? 'selected' : '' }}>Rented</option>
                <option value="reserved" {{ old('availability_status') == 'reserved' ? 'selected' : '' }}>Reserved</option>
                <option value="under_maintenance" {{ old('availability_status') == 'under_maintenance' ? 'selected' : '' }}>Under Maintenance</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="image" class="form-label">Vehicle Image</label>
            <input type="file" name="image" class="form-control" id="image" required>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="insurance_doc" class="form-label">Insurance Document</label>
                <input type="file" name="insurance_doc" class="form-control">
            </div>
            <div class="col-md-4">
                <label for="registration_doc" class="form-label">Registration Document</label>
                <input type="file" name="registration_doc" class="form-control">
            </div>
            <div class="col-md-4">
                <label for="roadtax_doc" class="form-label">Road Tax Document</label>
                <input type="file" name="roadtax_doc" class="form-control">
            </div>
        </div>

        {{-- Type-Specific Fields --}}
        <div id="carFields" class="type-specific d-none">
            <h4>Car Details</h4>
            <div class="mb-3">
                <label>Fuel Type</label>
                <select name="fuel_type" class="form-control">
                    <option value="petrol" {{ old('fuel_type') == 'petrol' ? 'selected' : '' }}>Petrol</option>
                    <option value="diesel" {{ old('fuel_type') == 'diesel' ? 'selected' : '' }}>Diesel</option>
                    <option value="electric" {{ old('fuel_type') == 'electric' ? 'selected' : '' }}>Electric</option>
                </select>
            </div>
            <div class="mb-3">
                <label>Transmission</label>
                <select name="transmission" class="form-control">
                    <option value="manual" {{ old('transmission') == 'manual' ? 'selected' : '' }}>Manual</option>
                    <option value="automatic" {{ old('transmission') == 'automatic' ? 'selected' : '' }}>Automatic</option>
                </select>
            </div>
            <div class="mb-3">
                <label>Air-Conditioning</label>
                <select name="air_conditioning" class="form-control">
                    <option value="yes" {{ old('air_conditioning') == 'yes' ? 'selected' : '' }}>Yes</option>
                    <option value="no" {{ old('air_conditioning') == 'no' ? 'selected' : '' }}>No</option>
                </select>
            </div>
            <div class="mb-3">
                <label>Seats</label>
                <input type="number" name="seats" class="form-control" value="{{ old('seats') }}">
            </div>
            <div class="mb-3">
                <label>Fuel Efficiency (km/l)</label>
                <input type="number" step="0.01" name="fuel_efficiency" class="form-control" value="{{ old('fuel_efficiency') }}">
            </div>
        </div>

        <div id="truckFields" class="type-specific d-none">
            <h4>Truck Details</h4>
            <div class="mb-3">
                <label>Load Capacity (tons)</label>
                <input type="number" step="0.01" name="load_capacity" class="form-control" value="{{ old('load_capacity') }}">
            </div>
            <div class="mb-3">
                <label>Fuel Type</label>
                <select name="fuel_type" class="form-control">
                    <option value="petrol" {{ old('fuel_type') == 'petrol' ? 'selected' : '' }}>Petrol</option>
                    <option value="diesel" {{ old('fuel_type') == 'diesel' ? 'selected' : '' }}>Diesel</option>
                </select>
            </div>
            <div class="mb-3">
                <label>Truck Type</label>
                <select name="truck_type" class="form-control">
                    <option value="pickup" {{ old('truck_type') == 'pickup' ? 'selected' : '' }}>Pickup</option>
                    <option value="lorry" {{ old('truck_type') == 'lorry' ? 'selected' : '' }}>Lorry</option>
                    <option value="container" {{ old('truck_type') == 'container' ? 'selected' : '' }}>Container</option>
                    <option value="flatbed" {{ old('truck_type') == 'flatbed' ? 'selected' : '' }}>Flatbed</option>
                </select>
            </div>
        </div>

        <div id="vanFields" class="type-specific d-none">
            <h4>Van Details</h4>
            <div class="mb-3">
                <label>Passenger Capacity</label>
                <input type="number" name="passenger_capacity" class="form-control" value="{{ old('passenger_capacity') }}">
            </div>
            <div class="mb-3">
                <label>Fuel Type</label>
                <select name="fuel_type" class="form-control">
                    <option value="petrol" {{ old('fuel_type') == 'petrol' ? 'selected' : '' }}>Petrol</option>
                    <option value="diesel" {{ old('fuel_type') == 'diesel' ? 'selected' : '' }}>Diesel</option>
                </select>
            </div>
            <div class="mb-3">
                <label>Air-Conditioning</label>
                <select name="air_conditioning" class="form-control">
                    <option value="yes" {{ old('air_conditioning') == 'yes' ? 'selected' : '' }}>Yes</option>
                    <option value="no" {{ old('air_conditioning') == 'no' ? 'selected' : '' }}>No</option>
                </select>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Add Vehicle</button>
    </form>
</div>

<script>
    const vehicleType = document.getElementById('vehicleType');
    const typeSpecifics = document.querySelectorAll('.type-specific');

    function showTypeFields(value) {
        typeSpecifics.forEach(div => {
            div.classList.add('d-none');
            div.querySelectorAll('input, select').forEach(el => el.required = false);
        });

        let activeDiv;
        if (value === 'car') activeDiv = document.getElementById('carFields');
        if (value === 'truck') activeDiv = document.getElementById('truckFields');
        if (value === 'van') activeDiv = document.getElementById('vanFields');

        if (activeDiv) {
            activeDiv.classList.remove('d-none');
            activeDiv.querySelectorAll('input, select').forEach(el => el.required = true);
        }
    }

    // Run on page load
    showTypeFields(vehicleType.value);

    // Run on type change
    vehicleType.addEventListener('change', function() {
        showTypeFields(this.value);
    });
</script>
@endsection