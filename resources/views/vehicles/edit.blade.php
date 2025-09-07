@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Vehicle</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.vehicles.update', $vehicle['id']) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- General Vehicle Info --}}
        <div class="mb-3">
            <label>Type</label>
            <select name="type" id="vehicleType" class="form-control" required>
                <option value="car" {{ $vehicle['type']=='car' ? 'selected' : '' }}>Car</option>
                <option value="truck" {{ $vehicle['type']=='truck' ? 'selected' : '' }}>Truck</option>
                <option value="van" {{ $vehicle['type']=='van' ? 'selected' : '' }}>Van</option>
            </select>
        </div>

        <div class="mb-3">
            <label>Brand</label>
            <input type="text" name="brand" class="form-control" value="{{ $vehicle['brand'] }}" required>
        </div>

        <div class="mb-3">
            <label>Model</label>
            <input type="text" name="model" class="form-control" value="{{ $vehicle['model'] }}" required>
        </div>

        <div class="mb-3">
            <label>Year Manufacturer</label>
            <input type="number" name="year_of_manufacture" class="form-control" 
                value="{{ $vehicle['year_of_manufacture'] }}" required>
        </div>

        <div class="mb-3">
            <label>Registration Number</label>
            <input type="text" name="registration_number" class="form-control" value="{{ $vehicle['registration_number'] }}" required>
        </div>

        <div class="mb-3">
            <label>Rental Price (RM)</label>
            <input type="number" step="0.01" name="rental_price" class="form-control" value="{{ $vehicle['rental_price'] }}" required>
        </div>

        <div class="mb-3">
            <label>Status</label>
            <select name="availability_status" class="form-control" required>
                <option value="available" {{ $vehicle['availability_status']=='available' ? 'selected' : '' }}>Available</option>
                <option value="rented" {{ $vehicle['availability_status']=='rented' ? 'selected' : '' }}>Rented</option>
                <option value="reserved" {{ $vehicle['availability_status']=='reserved' ? 'selected' : '' }}>Reserved</option>
                <option value="under_maintenance" {{ $vehicle['availability_status']=='under_maintenance' ? 'selected' : '' }}>Under Maintenance</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="image" class="form-label">Vehicle Image</label>
            <input type="file" name="image" class="form-control" id="image">
        </div>

        {{-- Type-Specific Fields --}}
        <div id="carFields" class="type-specific {{ $vehicle['type'] == 'car' ? '' : 'd-none' }}">
            <h4>Car Details</h4>
            <div class="mb-3">
                <label>Fuel Type</label>
                <select name="fuel_type" class="form-control">
                    <option value="petrol" {{ $vehicle->car?->fuel_type == 'petrol' ? 'selected' : '' }}>Petrol</option>
                    <option value="diesel" {{ $vehicle->car?->fuel_type == 'diesel' ? 'selected' : '' }}>Diesel</option>
                    <option value="electric" {{ $vehicle->car?->fuel_type == 'electric' ? 'selected' : '' }}>Electric</option>
                </select>
            </div>
            <div class="mb-3">
                <label>Transmission</label>
                <select name="transmission" class="form-control">
                    <option value="manual" {{ $vehicle->car?->transmission == 'manual' ? 'selected' : '' }}>Manual</option>
                    <option value="automatic" {{ $vehicle->car?->transmission == 'automatic' ? 'selected' : '' }}>Automatic</option>
                </select>
            </div>
            <div class="mb-3">
                <label>Air-Conditioning</label>
                <select name="air_conditioning" class="form-control">
                    <option value="yes" {{ $vehicle->car?->air_conditioning == 'yes' ? 'selected' : '' }}>Yes</option>
                    <option value="no" {{ $vehicle->car?->air_conditioning == 'no' ? 'selected' : '' }}>No</option>
                </select>
            </div>
            <div class="mb-3">
                <label>Seats</label>
                <input type="number" name="seats" class="form-control" value="{{ $vehicle->car?->seats }}">
            </div>
            <div class="mb-3">
                <label>Fuel Efficiency (km/l)</label>
                <input type="number" step="0.01" name="fuel_efficiency" class="form-control" value="{{ $vehicle->car?->fuel_efficiency }}">
            </div>
        </div>

        <div id="truckFields" class="type-specific {{ $vehicle['type'] == 'truck' ? '' : 'd-none' }}">
            <h4>Truck Details</h4>
            <div class="mb-3">
                <label>Load Capacity (tons)</label>
                <input type="number" step="0.01" name="load_capacity" class="form-control" value="{{ $vehicle->truck?->load_capacity }}">
            </div>
            <div class="mb-3">
                <label>Fuel Type</label>
                <select name="fuel_type" class="form-control">
                    <option value="petrol" {{ $vehicle->truck?->fuel_type == 'petrol' ? 'selected' : '' }}>Petrol</option>
                    <option value="diesel" {{ $vehicle->truck?->fuel_type == 'diesel' ? 'selected' : '' }}>Diesel</option>
                </select>
            </div>
            <div class="mb-3">
                <label>Truck Type</label>
                <select name="truck_type" class="form-control">
                    <option value="pickup" {{ $vehicle->truck?->truck_type == 'pickup' ? 'selected' : '' }}>Pickup</option>
                    <option value="lorry" {{ $vehicle->truck?->truck_type == 'lorry' ? 'selected' : '' }}>Lorry</option>
                    <option value="container" {{ $vehicle->truck?->truck_type == 'container' ? 'selected' : '' }}>Container</option>
                    <option value="flatbed" {{ $vehicle->truck?->truck_type == 'flatbed' ? 'selected' : '' }}>Flatbed</option>
                </select>
            </div>
        </div>

        <div id="vanFields" class="type-specific {{ $vehicle['type'] == 'van' ? '' : 'd-none' }}">
            <h4>Van Details</h4>
            <div class="mb-3">
                <label>Passenger Capacity</label>
                <input type="number" name="passenger_capacity" class="form-control" value="{{ $vehicle->van?->passenger_capacity }}">
            </div>
            <div class="mb-3">
                <label>Fuel Type</label>
                <select name="fuel_type" class="form-control">
                    <option value="petrol" {{ $vehicle->van?->fuel_type == 'petrol' ? 'selected' : '' }}>Petrol</option>
                    <option value="diesel" {{ $vehicle->van?->fuel_type == 'diesel' ? 'selected' : '' }}>Diesel</option>
                </select>
            </div>
            <div class="mb-3">
                <label>Air-Conditioning</label>
                <select name="air_conditioning" class="form-control">
                    <option value="yes" {{ $vehicle->van?->air_conditioning == 'yes' ? 'selected' : '' }}>Yes</option>
                    <option value="no" {{ $vehicle->van?->air_conditioning == 'no' ? 'selected' : '' }}>No</option>
                </select>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Update Vehicle</button>
        <a href="{{ route('admin.vehicles.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<script>
    const vehicleType = document.getElementById('vehicleType');
    const typeSpecifics = document.querySelectorAll('.type-specific');

    function toggleFields() {
        typeSpecifics.forEach(div => div.classList.add('d-none'));
        if (vehicleType.value === 'car') document.getElementById('carFields').classList.remove('d-none');
        if (vehicleType.value === 'truck') document.getElementById('truckFields').classList.remove('d-none');
        if (vehicleType.value === 'van') document.getElementById('vanFields').classList.remove('d-none');
    }

    vehicleType.addEventListener('change', toggleFields);
    toggleFields(); 
</script>
@endsection