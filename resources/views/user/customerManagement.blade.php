@extends('layouts.app')

@section('title', 'Customer Management')

@section('content')

<div class="container py-5">

    <h2 class="text-center mb-4">Customer Management</h2>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle text-center">
            <thead class="table-success">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone No</th>
                    <th>Role</th>
                    <th>Joined Date</th>
                    <th>Updated At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $cust)
                    @php
                        $editingId = request('edit_id');
                        $isEditing = $editingId == $cust['user_id'];
                    @endphp
                    <tr>
                        <form action="{{ $isEditing ? route('admin.customer.update', $cust['user_id']) : route('admin.customerManagement') }}" method="POST">
                            @csrf
                            @if($isEditing)
                                @method('PUT')
                            @endif
                            <td>{{ $cust['customer_id'] }}</td>

                            @if($isEditing)
                                <td><input type="text" name="username" class="form-control form-control-sm" value="{{ $cust['name'] }}" required></td>
                                <td>{{ $cust['email'] }}</td>
                                <td><input type="text" name="phone" class="form-control form-control-sm" value="{{ $cust['phoneNo'] }}" required></td>
                            @else
                                <td><input type="text" class="form-control form-control-sm" value="{{ $cust['name'] }}" disabled></td>
                                <td>{{ $cust['email'] }}</td>
                                <td><input type="text" class="form-control form-control-sm" value="{{ $cust['phoneNo'] }}" disabled></td>
                            @endif

                            <td>{{ $cust['role'] }}</td>
                            <td>{{ $cust['created_at'] }}</td>
                            <td>{{ $cust['updated_at'] ?? '-' }}</td>
                            
                            <td class="d-flex justify-content-center gap-2">
                                @if($isEditing)
                                    <button type="submit" class="btn btn-success btn-sm">Save</button>
                                    <a href="{{ route('admin.customerManagement') }}" class="btn btn-danger btn-sm">Cancel</a>
                                @else
                                    <a href="{{ route('admin.customerManagement', ['edit_id' => $cust['user_id']]) }}" class="btn btn-warning btn-sm">Edit</a>
                                @endif
                            </td>
                        </form>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">No Customer Records</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection
