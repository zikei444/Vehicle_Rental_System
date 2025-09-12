<!-- 
STUDENT NAME: WONG XINN
STUDENT ID: 23WMR14632
-->

@extends('layouts.app')

@section('title', 'Profile')

@section('content')

<div class="container py-5">

    {{-- Display Errors --}}
    @if ($errors->any())
        <div class="alert alert-danger shadow-sm">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li><i class="bi bi-exclamation-circle"></i> {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Display Success Message --}}
    @if (session('success'))
        <div class="alert alert-success shadow-sm">
            <ul class="mb-0">
                <li><i class="bi bi-check-circle"></i> {{ session('success') }}</li>
            </ul>
        </div>
    @endif

    @php
        $userObj = session('user');
        if (isset($userData)) {
            $userObj = (object) $userData;
        }
    @endphp

    {{-- Profile Box --}}
    <div class="card shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-0 text-center">
            <h2 class="fw-bold mb-0">Profile</h2>
        </div>
        <div class="card-body">

            {{-- Update Profile Form --}}
            <form action="{{ route('profile.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3 row">
                    <label class="col-sm-3 col-form-label">Username</label>
                    <div class="col-sm-9">
                        <input type="text" name="username"
                               class="form-control"
                               value="{{ old('username', Auth::user()->name ?? '') }}"
                               {{ request()->get('edit') !== 'true' ? 'disabled' : '' }}>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label class="col-sm-3 col-form-label">Phone</label>
                    <div class="col-sm-9">
                        <input type="text" name="phone"
                               class="form-control"
                               value="{{ old('phone', $userObj->phone ?? $userObj->phoneNo ?? '') }}"
                               {{ request()->get('edit') !== 'true' ? 'disabled' : '' }}>
                    </div>
                </div>



                <div class="text-center">
                    @if(request()->get('edit') !== 'true')
                        <a href="{{ route('profile.edit', ['edit' => 'true']) }}" class="btn btn-warning">
                            Edit
                        </a>
                    @else
                        <button type="submit" class="btn btn-success me-2">Save</button>
                        <a href="{{ route('profile.edit') }}" class="btn btn-danger">Cancel</a>
                    @endif
                </div>
            </form>

            {{-- Delete Account Form --}}
            <form action="{{ route('profile.destroy') }}" method="POST" class="text-center mt-4"
                onsubmit="return confirm('Are you sure you want to delete your account? After deletion, all your data cannot be restored!');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Delete Account</button>
            </form>
        </div>
    </div>

    {{-- Reservation History --}}
    <div class="card shadow-sm rounded-4 mb-4 text-center">
        <div class="card-header bg-white border-0">
            <h3 class="fw-bold mb-0">My Reservation History</h3>
        </div>
        <a href="{{ route('reservations.history') }}" class="card-body d-flex flex-column align-items-center text-decoration-none text-dark p-4">
            <img src="{{ asset('images/dashboard/reservation_icon.png') }}" alt="Reservation History"
                class="mb-3 img-fluid bg-light p-2 border rounded" style="width:100px; height:100px;">
            <p class="text-muted small mb-0">View all your past rentals in one place</p>
        </a>
    </div>

    {{-- Reservation Summary --}}
    <div class="card shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-0 text-center">
            <h3 class="fw-bold mb-0">Reservation Summary</h3>
        </div>
        <div class="card-body">
            <table class="table table-bordered text-center mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Status</th>
                        <th>Number</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-primary">Ongoing</td>
                        <td>{{ $reservationSummary['ongoing'] ?? 0 }}</td>
                    </tr>
                    <tr>
                        <td class="text-success">Completed</td>
                        <td>{{ $reservationSummary['completed'] ?? 0 }}</td>
                    </tr>
                    <tr>
                        <td class="text-danger">Cancelled</td>
                        <td>{{ $reservationSummary['cancelled'] ?? 0 }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

@endsection
