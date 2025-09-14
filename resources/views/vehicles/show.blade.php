<!-- 
STUDENT NAME: Lian Wei Ying 
STUDENT ID: 23WMR14568
-->

@extends('layouts.app')

@section('content')
<div class="container">
    <h1>{{ ucfirst($vehicle->type) }} Details</h1>

    <!-- Vahicle image -->
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

    <!-- Rating -->
    <div class="card mb-3">
        <div class="card-header">Ratings</div>
        <div class="card-body">
            @php
                $avg   = $vehicle->average_rating ?? ($vehicle->ratingSummary['average'] ?? null);
                $count = $vehicle->ratings_count  ?? ($vehicle->ratingSummary['count'] ?? 0);
            @endphp

            @if($avg)
                <p><strong>Rating:</strong> ⭐ {{ number_format($avg, 1) }} / 5 
                    ({{ $count }} reviews)</p>
            @else
                <p><strong>Rating:</strong> No ratings yet</p>
            @endif
        </div>
    </div>

    <!-- Vehicle information -->
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

    <!-- Type-specific information -->
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

    <!-- Vehicle documents -->
    <div class="card mb-3">
        <div class="card-header">
            Vehicle Documents
            <button class="btn btn-sm btn-primary float-end" type="button" data-bs-toggle="collapse" data-bs-target="#adminDocs" aria-expanded="false" aria-controls="adminDocs">
                Show / Hide Documents
            </button>
        </div>
        <div class="collapse" id="adminDocs">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 text-center mb-2">
                        <p><strong>Insurance Document</strong></p>
                        @if($vehicle->insurance_doc)
                            <a href="{{ asset('documents/vehicles/' . $vehicle->insurance_doc) }}" target="_blank">
                                View Document
                            </a>
                        @else
                            <span class="text-muted">Not uploaded</span>
                        @endif
                    </div>
                    <div class="col-md-4 text-center mb-2">
                        <p><strong>Registration Document</strong></p>
                        @if($vehicle->registration_doc)
                            <a href="{{ asset('documents/vehicles/' . $vehicle->registration_doc) }}" target="_blank">
                                View Document
                            </a>
                        @else
                            <span class="text-muted">Not uploaded</span>
                        @endif
                    </div>
                    <div class="col-md-4 text-center mb-2">
                        <p><strong>Road Tax Document</strong></p>
                        @if($vehicle->roadtax_doc)
                            <a href="{{ asset('documents/vehicles/' . $vehicle->roadtax_doc) }}" target="_blank">
                                View Document
                            </a>
                        @else
                            <span class="text-muted">Not uploaded</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(Route::is('admin.vehicles.show'))
        <a href="{{ route('admin.vehicles.index') }}" class="btn btn-secondary">Back to List</a>
    @else
        <a href="{{ route('vehicles.index') }}" class="btn btn-secondary">Back to List</a>
    @endif

</div>
@endsection