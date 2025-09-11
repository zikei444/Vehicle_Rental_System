@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

<div class="container py-5">
    <h1 class="text-center mb-5">Dashboard</h1>

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">

        {{-- Edit Profile --}}
        <div class="col">
            <a href="{{ route('profile.edit') }}" class="text-decoration-none">
                <div class="card h-100 text-center border-success shadow-sm">
                    <img src="{{ asset('images/dashboard/acc_icon.png') }}" class="card-img-top mx-auto mt-3" style="width:120px; height:120px;" alt="Edit Profile">
                    <div class="card-body">
                        <h5 class="card-title text-success">Edit Profile</h5>
                    </div>
                </div>
            </a>
        </div>

        {{-- Reservation History --}}
        <div class="col">
            <a href="{{ route('reservations.history') }}" class="text-decoration-none">
                <div class="card h-100 text-center border-primary shadow-sm">
                    <img src="{{ asset('images/dashboard/reservation_icon.png') }}" class="card-img-top mx-auto mt-3" style="width:120px; height:120px;" alt="Reservation History">
                    <div class="card-body">
                        <h5 class="card-title text-primary">My Reservation History</h5>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>

@endsection
