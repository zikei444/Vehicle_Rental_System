@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Vehicle Management</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="mb-3">
        <a href="{{ route('admin.vehicles.create') }}" class="btn btn-primary">+ Add New Vehicle</a>
    </div>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Image</th> 
                <th>Type</th>
                <th>Brand</th>
                <th>Model</th>
                <th>Year</th> 
                <th>Registration</th>
                <th>Price (RM)</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($vehicles as $v)
                <tr>
                    <td>{{ $v['id'] }}</td>
                    <td class="text-center">
                        @if($v['image'])
                            <img src="{{ asset('images/vehicles/' . $v['image']) }}" 
                                alt="{{ $v['brand'] }} {{ $v['model'] }}" 
                                style="height: 50px; width: auto; border-radius: 5px;">
                        @else
                            <img src="https://via.placeholder.com/50x30?text=No+Image" 
                                alt="No Image" 
                                style="height: 50px; width: auto; border-radius: 5px;">
                        @endif
                    </td>
                    <td>{{ ucfirst($v['type']) }}</td>
                    <td>{{ $v['brand'] }}</td>
                    <td>{{ $v['model'] }}</td>
                    <td>{{ $v['year_of_manufacture'] ?? 'N/A' }}</td> 
                    <td>{{ $v['registration_number'] }}</td>
                    <td>{{ number_format($v['rental_price'], 2) }}</td>
                    <td>
                        @if($v['availability_status'] === 'available')
                            <span class="badge bg-success">Available</span>
                        @elseif($v['availability_status'] === 'rented')
                            <span class="badge bg-warning">Rented</span>
                        @elseif($v['availability_status'] === 'reserved')
                            <span class="badge bg-info">Reserved</span>
                        @else
                            <span class="badge bg-danger">Under Maintenance</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('admin.vehicles.show', $v['id']) }}" class="btn btn-sm btn-info">View</a>
                        <a href="{{ route('admin.vehicles.edit', $v['id']) }}" class="btn btn-sm btn-warning">Edit</a>

                        <form action="{{ route('admin.vehicles.destroy', $v['id']) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Are you sure you want to delete this vehicle?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center">No vehicles found.</td> 
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection