<!-- STUDENT NAME: Kek Xin Ying
STUDENT ID: 23WMR14547 -->
@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Manage Customer Feedback</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Customer</th>
                <th>Vehicle</th>
                <th>Rating</th>
                <th>Feedback</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ratings as $rating)
                <tr>
                    <td>{{ $rating->customer->user->name ?? 'Unknown' }}</td>
                    <td>{{ $rating->vehicle->brand  ?? 'Unknown' }} {{ $rating->vehicle->model  ?? 'Unknown' }}</td>
                    <td>{{ $rating->rating }} / 5</td>
                    <td>{{ $rating->feedback }}</td>
                    <td>
                        <span class="badge 
                            @if($rating->status == 'approved') bg-success 
                            @elseif($rating->status == 'rejected') bg-danger 
                            @else bg-warning @endif">
                            {{ ucfirst($rating->status) }}
                        </span>
                    </td>
                    <td>
                        <form action="{{ route('feedback.updateStatus', $rating->id) }}" method="POST">
                            @csrf
                            <select name="status" class="form-select d-inline w-auto">
                                <option value="approved" {{ $rating->status == 'approved' ? 'selected' : '' }}>Approve</option>
                                <option value="rejected" {{ $rating->status == 'rejected' ? 'selected' : '' }}>Reject</option>
                                <option value="pending" {{ $rating->status == 'pending' ? 'selected' : '' }}>Pending</option>
                            </select>
                            <button type="submit" class="btn btn-primary btn-sm">Update</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
