@extends('layouts.app')

@section('content')
<div class="container">
    <h2>My Reservation History</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Vehicle</th>
                <th>Pickup Date</th>
                <th>Return Date</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        @foreach($reservations as $res)
            <tr>
                <td>{{ $res->vehicle['brand'] ?? '' }} {{ $res->vehicle['model'] ?? '' }}</td>
                <td>{{ $res->pickup_date }}</td>
                <td>{{ $res->return_date }}</td>
                <td>{{ $res->status }}</td>
                <td>
                    @if($res->status === 'completed' && !$res->rating) 
                        <a href="{{ route('ratings.create', $res->id) }}" class="btn btn-primary btn-sm">Rate</a>
                    @elseif($res->rating)
                        Rated
                    @else
                        -
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
