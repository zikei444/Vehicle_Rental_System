<!-- 
STUDENT NAME: Lian Wei Ying 
STUDENT ID: 23WMR14568
-->

@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Vehicle</h1>

    <!-- Display error -->
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.vehicles.update', $vehicle->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- General information -->
        <div class="mb-3">
            <label>Type</label>
            <select name="type" id="vehicleType" class="form-control" required>
                <option value="car" {{ old('type', $vehicle->type)=='car' ? 'selected' : '' }}>Car</option>
                <option value="truck" {{ old('type', $vehicle->type)=='truck' ? 'selected' : '' }}>Truck</option>
                <option value="van" {{ old('type', $vehicle->type)=='van' ? 'selected' : '' }}>Van</option>
            </select>
        </div>

        <div class="mb-3">
            <label>Brand</label>
            <input type="text" name="brand" class="form-control" value="{{ old('brand', $vehicle->brand) }}" required>
            @error('brand')<div class="text-danger">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label>Model</label>
            <input type="text" name="model" class="form-control" value="{{ old('model', $vehicle->model) }}" required>
            @error('model')<div class="text-danger">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label>Year Manufacturer</label>
            <input type="number" name="year_of_manufacture" class="form-control" value="{{ old('year_of_manufacture', $vehicle->year_of_manufacture) }}" required>
            @error('year_of_manufacture')<div class="text-danger">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label>Registration Number</label>
            <input type="text" name="registration_number" class="form-control" value="{{ old('registration_number', $vehicle->registration_number) }}" required>
            @error('registration_number')<div class="text-danger">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label>Rental Price (RM)</label>
            <input type="number" step="0.01" name="rental_price" class="form-control" value="{{ old('rental_price', $vehicle->rental_price) }}" required>
            @error('rental_price')<div class="text-danger">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label>Status</label>
            <select name="availability_status" class="form-control" required>
                @php $status = old('availability_status', $vehicle->availability_status); @endphp
                <option value="available" {{ $status=='available' ? 'selected' : '' }}>Available</option>
                <option value="rented" {{ $status=='rented' ? 'selected' : '' }}>Rented</option>
                <option value="reserved" {{ $status=='reserved' ? 'selected' : '' }}>Reserved</option>
                <option value="under_maintenance" {{ $status=='under_maintenance' ? 'selected' : '' }}>Under Maintenance</option>
            </select>
        </div>

        <!-- Vehicle image -->
        <div class="mb-3">
            <label>Vehicle Image</label>
            <input type="file" name="image" class="form-control">
            <small class="text-muted">Upload new image to replace current one.</small>
        </div>

        <!-- Vehicle documents -->
        <div class="row mb-3">
            @foreach(['insurance_doc','registration_doc','roadtax_doc'] as $doc)
            <div class="col-md-4">
                <label>{{ ucfirst(str_replace('_',' ',$doc)) }}</label>
                <input type="file" name="{{ $doc }}" class="form-control">
                <small class="text-muted">Upload new document to replace current one (optional).</small>
            </div>
            @endforeach
        </div>

        <!-- Type-specific information -->
        <div id="carFields" class="type-specific {{ old('type', $vehicle->type)=='car' ? '' : 'd-none' }}">
            <h4>Car Details</h4>
            <div class="mb-3">
                <label>Fuel Type</label>
                <select name="fuel_type" class="form-control">
                    @php $fuel = old('fuel_type', $vehicle->car?->fuel_type); @endphp
                    <option value="petrol" {{ $fuel=='petrol' ? 'selected' : '' }}>Petrol</option>
                    <option value="diesel" {{ $fuel=='diesel' ? 'selected' : '' }}>Diesel</option>
                    <option value="electric" {{ $fuel=='electric' ? 'selected' : '' }}>Electric</option>
                </select>
            </div>
            <div class="mb-3">
                <label>Transmission</label>
                <select name="transmission" class="form-control">
                    @php $trans = old('transmission', $vehicle->car?->transmission); @endphp
                    <option value="manual" {{ $trans=='manual' ? 'selected' : '' }}>Manual</option>
                    <option value="automatic" {{ $trans=='automatic' ? 'selected' : '' }}>Automatic</option>
                </select>
            </div>
            <div class="mb-3">
                <label>Air-Conditioning</label>
                <select name="air_conditioning" class="form-control">
                    @php $ac = old('air_conditioning', $vehicle->car?->air_conditioning); @endphp
                    <option value="yes" {{ $ac=='yes' ? 'selected' : '' }}>Yes</option>
                    <option value="no" {{ $ac=='no' ? 'selected' : '' }}>No</option>
                </select>
            </div>
            <div class="mb-3">
                <label>Seats</label>
                <input type="number" name="seats" class="form-control" value="{{ old('seats', $vehicle->car?->seats) }}">
            </div>
            <div class="mb-3">
                <label>Fuel Efficiency (km/l)</label>
                <input type="number" step="0.01" name="fuel_efficiency" class="form-control" value="{{ old('fuel_efficiency', $vehicle->car?->fuel_efficiency) }}">
            </div>
        </div>

        <div id="truckFields" class="type-specific {{ old('type', $vehicle->type)=='truck' ? '' : 'd-none' }}">
            <h4>Truck Details</h4>
            <div class="mb-3">
                <label>Load Capacity (tons)</label>
                <input type="number" step="0.01" name="load_capacity" class="form-control" value="{{ old('load_capacity', $vehicle->truck?->load_capacity) }}">
            </div>
            <div class="mb-3">
                <label>Fuel Type</label>
                <select name="fuel_type" class="form-control">
                    @php $fuel = old('fuel_type', $vehicle->truck?->fuel_type); @endphp
                    <option value="petrol" {{ $fuel=='petrol' ? 'selected' : '' }}>Petrol</option>
                    <option value="diesel" {{ $fuel=='diesel' ? 'selected' : '' }}>Diesel</option>
                </select>
            </div>
            <div class="mb-3">
                <label>Truck Type</label>
                <select name="truck_type" class="form-control">
                    @php $ttype = old('truck_type', $vehicle->truck?->truck_type); @endphp
                    <option value="pickup" {{ $ttype=='pickup' ? 'selected' : '' }}>Pickup</option>
                    <option value="lorry" {{ $ttype=='lorry' ? 'selected' : '' }}>Lorry</option>
                    <option value="container" {{ $ttype=='container' ? 'selected' : '' }}>Container</option>
                    <option value="flatbed" {{ $ttype=='flatbed' ? 'selected' : '' }}>Flatbed</option>
                </select>
            </div>
        </div>

        <div id="vanFields" class="type-specific {{ old('type', $vehicle->type)=='van' ? '' : 'd-none' }}">
            <h4>Van Details</h4>
            <div class="mb-3">
                <label>Passenger Capacity</label>
                <input type="number" name="passenger_capacity" class="form-control" value="{{ old('passenger_capacity', $vehicle->van?->passenger_capacity) }}">
            </div>
            <div class="mb-3">
                <label>Fuel Type</label>
                <select name="fuel_type" class="form-control">
                    @php $fuel = old('fuel_type', $vehicle->van?->fuel_type); @endphp
                    <option value="petrol" {{ $fuel=='petrol' ? 'selected' : '' }}>Petrol</option>
                    <option value="diesel" {{ $fuel=='diesel' ? 'selected' : '' }}>Diesel</option>
                </select>
            </div>
            <div class="mb-3">
                <label>Air-Conditioning</label>
                <select name="air_conditioning" class="form-control">
                    @php $ac = old('air_conditioning', $vehicle->van?->air_conditioning); @endphp
                    <option value="yes" {{ $ac=='yes' ? 'selected' : '' }}>Yes</option>
                    <option value="no" {{ $ac=='no' ? 'selected' : '' }}>No</option>
                </select>
            </div>
        </div>

        <!-- Button -->
        <button type="submit" class="btn btn-primary">Update Vehicle</button>
        <a href="{{ route('admin.vehicles.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<script>
    const vehicleType = document.getElementById('vehicleType');
    const typeSpecifics = document.querySelectorAll('.type-specific');

    function toggleFields() {
        typeSpecifics.forEach(div => div.classList.add('d-none'));
        if(vehicleType.value==='car') document.getElementById('carFields').classList.remove('d-none');
        if(vehicleType.value==='truck') document.getElementById('truckFields').classList.remove('d-none');
        if(vehicleType.value==='van') document.getElementById('vanFields').classList.remove('d-none');
    }

    vehicleType.addEventListener('change', toggleFields);
    toggleFields();
</script>
@endsection