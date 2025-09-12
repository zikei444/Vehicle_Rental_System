<!-- STUDENT NAME: Kek Xin Ying
STUDENT ID: 23WMR14547 -->

@extends('layouts.app')

@section('content')
<div class="container">
    <h2>My Feedback</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Vehicle</th>
                <th>Rating</th>
                <th>Feedback</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
        @foreach($ratings as $rating)
            <tr>
                <td>{{ $rating->vehicle->brand }} {{ $rating->vehicle->model }}</td>
                <td>{{ $rating->rating }} ⭐</td>
                <td>{{ $rating->feedback ?? '-' }}</td>
                <td>{{ $rating->created_at->format('Y-m-d') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
