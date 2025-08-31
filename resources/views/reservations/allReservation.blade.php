@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h2 class="mb-4">All Reservations</h2>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Customer</th>
                <th>Vehicle</th>
                <th>Pick-Up</th>
                <th>Return</th>
                <th>Total Cost</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            {{-- Example row --}}
            <tr>
                <td>John Doe</td>
                <td>Toyota Corolla</td>
                <td>2025-09-01</td>
                <td>2025-09-03</td>
                <td>RM 450.00</td>
                <td>Confirmed</td>
                <td>
                    <a href="#" class="btn btn-sm btn-warning">Edit</a>
                    <a href="#" class="btn btn-sm btn-danger">Cancel</a>
                </td>
            </tr>
        </tbody>
    </table>
</div>
@endsection