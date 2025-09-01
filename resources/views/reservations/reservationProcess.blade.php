@extends('layouts.app')

@section('content')
<h1>Reservation Process</h1>

@if($vehicle)
    <p><strong>Vehicle:</strong> {{ $vehicle['brand'] }} {{ $vehicle['model'] }} ({{ $vehicle['type'] }})</p>
    <p><strong>Price per day:</strong> RM {{ $vehicle['rental_price'] }}</p>

    <form action="{{ route('reservation.calculate') }}" method="POST">
        @csrf
        <input type="hidden" name="vehicle_id" value="{{ $vehicle['id'] }}">

        <label>Pickup Date:</label>
        <input type="date" name="pickup_date" required>

        <label>Return Date:</label>
        <input type="date" name="return_date" required>

        <button type="submit">Calculate Cost</button>
@else
    <p>Vehicle not found.</p>
@endif

@if(isset($totalCost))
    <h3>Total Cost: RM {{ $totalCost }}</h3>
@endif

<a href="{{ url('vehicles') }}">Back to Vehicle Selection</a>
@endsection