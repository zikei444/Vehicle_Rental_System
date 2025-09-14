<!-- 
STUDENT NAME: Lian Wei Ying 
STUDENT ID: 23WMR14568
-->

@extends('layouts.app')

@section('content')
<div class="container py-5">
    <h1 class="mb-4 text-center">Available Vehicles</h1>

    <!-- Fileter and search form -->
    <form action="{{ route('vehicles.index') }}" method="GET" class="mb-4 d-flex gap-2 align-items-center">
        
        <!-- Search -->
        <input type="text" name="search" class="form-control" placeholder="Search by brand or model" value="{{ request('search') }}">

        <select name="type" class="form-control">
            <option value="">All Types</option>
            <option value="car" {{ request('type')=='car' ? 'selected' : '' }}>Car</option>
            <option value="truck" {{ request('type')=='truck' ? 'selected' : '' }}>Truck</option>
            <option value="van" {{ request('type')=='van' ? 'selected' : '' }}>Van</option>
        </select>

        <!-- Sort -->
        <select name="sort_by" class="form-control">
            <option value="">Sort By</option>
            <option value="brand" {{ request('sort_by')=='brand' ? 'selected' : '' }}>Brand</option>
            <option value="model" {{ request('sort_by')=='model' ? 'selected' : '' }}>Model</option>
            <option value="rental_price" {{ request('sort_by')=='rental_price' ? 'selected' : '' }}>Price</option>
        </select>

        <!-- Sort (asecending and descending) -->
        <select name="order" class="form-control">
            <option value="asc" {{ request('order')=='asc' ? 'selected' : '' }}>Ascending</option>
            <option value="desc" {{ request('order')=='desc' ? 'selected' : '' }}>Descending</option>
        </select>

        <button type="submit" class="btn btn-primary">Apply</button>
        <a href="{{ route('vehicles.index') }}" class="btn btn-secondary">Reset</a>
    </form>

    <!-- Vehicle grid -->
    <div class="row g-4">
        @forelse($vehicles as $v)
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    
                    <!-- Image -->
                    <img src="{{ $v->image ? asset('images/vehicles/' . $v->image) : 'https://via.placeholder.com/400x200?text=Vehicle+Image' }}" 
                        alt="{{ $v->brand }} {{ $v->model }}" 
                        class="img-fluid rounded" 
                        style="width: 100%; height: 250px; object-fit: cover;">

                    <!-- Deatils -->
                    <div class="card-body">
                        <h5 class="card-title">{{ ucfirst($v['type']) }} - {{ $v['brand'] }} {{ $v['model'] }}</h5>
                        <p class="card-text mb-1"><strong>Year:</strong> {{ $v['year_of_manufacture'] ?? 'N/A' }}</p>
                        <p class="card-text mb-1"><strong>Price:</strong> RM {{ number_format($v['rental_price'], 2) }}</p>
                        
                        @php
                            $avg   = $v->average_rating ?? ($v->ratingSummary['average'] ?? null);
                            $count = $v->ratings_count  ?? ($v->ratingSummary['count'] ?? 0);
                        @endphp

                        @if($avg)
                            <p class="card-text mb-1">
                                <strong>Rating:</strong> ⭐ {{ number_format($avg, 1) }} / 5 
                                ({{ $count }} reviews)
                            </p>
                        @else
                            <p class="card-text mb-1"><strong>Rating:</strong> No ratings yet</p>
                        @endif

                        <p class="card-text">
                            <strong>Status:</strong>
                            @if($v['availability_status'] === 'available')
                                <span class="badge bg-success">Available</span>
                            @else
                                <span class="badge bg-secondary">{{ ucfirst($v['availability_status']) }}</span>
                            @endif
                        </p>
                    </div>

                    <div class="card-footer d-flex justify-content-between">
                        <a href="{{ route('vehicles.show', $v['id']) }}" class="btn btn-info btn-sm">View</a>
                        
                        @if($v['availability_status'] === 'available')
                            <a href="{{ route('vehicles.select', $v['id']) }}" class="btn btn-success btn-sm">Choose</a>
                        @else
                            <span class="text-muted">Not Available</span>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center">
                <p class="lead">No vehicles available at the moment.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection