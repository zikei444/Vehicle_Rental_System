<!-- STUDENT NAME: Kek Xin Ying
STUDENT ID: 23WMR14547 -->
@extends('layouts.app')

@section('content')
<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>{{ $vehicle->brand  ?? 'Unknown'}} {{ $vehicle->model  ?? 'Unknown'}} - Ratings Details</h2>
        <a href="{{ route('ratings_admin.dashboard') }}" class="btn btn-secondary mb-3">Back to Dashboard</a>
    </div>
<table class="table">
    <thead>
        <tr>
            <th>Customer</th>
            <th>Rating</th>
            <th>Comment</th>
            <th>Date Submitted</th>
        </tr>
    </thead>
    <tbody>
        @foreach($vehicle->ratings as $rating)
        <tr>
            <td>{{ $rating->customer->user->name ?? 'Unknown' }}</td>
            <td>{{ $rating->rating }} ⭐</td>
            <td>{{ $rating->feedback }}</td>
            <td>{{ $rating->created_at->format('d/m/Y H:i') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
