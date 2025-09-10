@extends('layouts.app')

@section('title', 'Dashboard - Admin')

@section('content')

<div class="container py-5">
    <h1 class="text-center mb-5">Admin Dashboard</h1>

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">

        {{-- Customer Account Management --}}
        <div class="col">
            <a href="{{ route('admin.customerManagement') }}" class="text-decoration-none">
                <div class="card h-100 text-center border-success shadow-sm">
                    <img src="{{ asset('images/dashboard/acc_icon.png') }}" class="card-img-top mx-auto mt-3" style="width:120px; height:120px;" alt="Customer Account Management">
                    <div class="card-body">
                        <h5 class="card-title text-success">Customer Account Management</h5>
                    </div>
                </div>
            </a>
        </div>

        {{-- Vehicle Management --}}
        <div class="col">
            <a href="{{ route('admin.vehicles.index') }}" class="text-decoration-none">
                <div class="card h-100 text-center border-primary shadow-sm">
                    <img src="{{ asset('images/dashboard/vehicle_icon.png') }}" class="card-img-top mx-auto mt-3" style="width:120px; height:120px;" alt="Vehicle Management">
                    <div class="card-body">
                        <h5 class="card-title text-primary">Vehicle Management</h5>
                    </div>
                </div>
            </a>
        </div>

        {{-- Reservation Management --}}
        <div class="col">
            <a href="{{ route('admin.reservations.index') }}" class="text-decoration-none">
                <div class="card h-100 text-center border-info shadow-sm">
                    <img src="{{ asset('images/dashboard/reservation_icon.png') }}" class="card-img-top mx-auto mt-3" style="width:120px; height:120px;" alt="Reservation Management">
                    <div class="card-body">
                        <h5 class="card-title text-info">Reservation Management</h5>
                    </div>
                </div>
            </a>
        </div>

        {{-- Rating & Feedback Management --}}
        <div class="col">
            <a href="{{ route('ratings_admin.index') }}" class="text-decoration-none">
                <div class="card h-100 text-center border-warning shadow-sm">
                    <img src="{{ asset('images/dashboard/rating_icon.png') }}" class="card-img-top mx-auto mt-3" style="width:120px; height:120px;" alt="Rating & Feedback Management">
                    <div class="card-body">
                        <h5 class="card-title text-warning">Rating & Feedback Management</h5>
                    </div>
                </div>
            </a>
        </div>

        {{-- Maintenance Management --}}
        <div class="col">
            <a href="{{ route('maintenance.index') }}" class="text-decoration-none">
                <div class="card h-100 text-center border-danger shadow-sm">
                    <img src="{{ asset('images/dashboard/maintenance_icon.png') }}" class="card-img-top mx-auto mt-3" style="width:120px; height:120px;" alt="Maintenance Management">
                    <div class="card-body">
                        <h5 class="card-title text-danger">Maintenance Management</h5>
                    </div>
                </div>
            </a>
        </div>

    </div>
</div>

@endsection
