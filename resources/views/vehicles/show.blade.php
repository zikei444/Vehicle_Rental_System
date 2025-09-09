@extends('layouts.app')

@section('content')
<div class="container">
    <h1>{{ ucfirst($vehicle->type) }} Details</h1>

    <div class="mb-4 text-center">
        @if($vehicle->image)
            <img src="{{ asset('images/vehicles/' . $vehicle->image) }}" 
                 alt="{{ $vehicle->brand }} {{ $vehicle->model }}" 
                 class="img-fluid rounded" 
                 style="max-height: 300px;">
        @else
            <img src="https://via.placeholder.com/400x200?text=No+Image" 
                 alt="No Image" 
                 class="img-fluid rounded" 
                 style="max-height: 300px;">
        @endif
    </div>

    <div class="card mb-3">
        <div class="card-header">Ratings</div>
        <div class="card-body">
            @if($vehicle->ratingSummary && $vehicle->ratingSummary['count'] > 0)
                <p><strong>Average Rating:</strong> {{ $vehicle->ratingSummary['average'] }} / 5</p>
                <p><strong>Total Reviews:</strong> {{ $vehicle->ratingSummary['count'] }}</p>
            @else
                <p>No ratings yet</p>
            @endif
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">General Information</div>
        <div class="card-body">
            <p><strong>Brand:</strong> {{ $vehicle->brand }}</p>
            <p><strong>Model:</strong> {{ $vehicle->model }}</p>
            <p><strong>Year of Manufacture:</strong> {{ $vehicle->year_of_manufacture ?? 'N/A' }}</p> 
            <p><strong>Registration Number:</strong> {{ $vehicle->registration_number }}</p>
            <p><strong>Rental Price:</strong> RM {{ number_format($vehicle->rental_price, 2) }}</p>
            <p><strong>Status:</strong> {{ ucfirst($vehicle->availability_status) }}</p>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">Type-Specific Details</div>
        <div class="card-body">
            @if($vehicle->type == 'car' && $vehicle->car)
                <p><strong>Fuel Type:</strong> {{ $vehicle->car->fuel_type ?? 'N/A' }}</p>
                <p><strong>Transmission:</strong> {{ $vehicle->car->transmission ?? 'N/A' }}</p>
                <p><strong>Seats:</strong> {{ $vehicle->car->seats ?? 'N/A' }}</p>
                <p><strong>Air Conditioning:</strong> {{ $vehicle->car->air_conditioning ?? 'N/A' }}</p>
                <p><strong>Fuel Efficiency:</strong> {{ $vehicle->car->fuel_efficiency ?? 'N/A' }} km/l</p>
            
            @elseif($vehicle->type == 'truck' && $vehicle->truck)
                <p><strong>Truck Type:</strong> {{ $vehicle->truck->truck_type ?? 'N/A' }}</p>
                <p><strong>Load Capacity:</strong> {{ $vehicle->truck->load_capacity ?? 'N/A' }} tons</p>
                <p><strong>Fuel Type:</strong> {{ $vehicle->truck->fuel_type ?? 'N/A' }}</p>
            
            @elseif($vehicle->type == 'van' && $vehicle->van)
                <p><strong>Passenger Capacity:</strong> {{ $vehicle->van->passenger_capacity ?? 'N/A' }}</p>
                <p><strong>Fuel Type:</strong> {{ $vehicle->van->fuel_type ?? 'N/A' }}</p>
                <p><strong>Air Conditioning:</strong> {{ $vehicle->van->air_conditioning ?? 'N/A' }}</p>
            @endif
        </div>
    </div>

    <a href="{{ $back_route }}" class="btn btn-secondary">Back to List</a>

</div>
@endsection