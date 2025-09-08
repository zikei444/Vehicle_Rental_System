@extends('layouts.app')

@section('title', 'Customer Management')

@section('content')

<style>
    .container {
        text-align: center;
        color: #106748;
    }

    table {
        margin: auto;
    }

    td, th {
        text-align: center;
        vertical-align: middle;
        font-size: 15px;
        padding: 20px 10px;
        border: 1px solid #106748;
    }

    th {
        font-size: 20px;
    }

    a {
        color: #106748;
    }

    .icon {
        height: 120px;
        width: 120px;
    }

    #login_url {
        font-size: 15px;
    }

    button {
        color: #0F3829;
        font-size: 15px;
        padding: 10px 20px;
        border: 2px solid #106748;
        border-radius: 5px;
        margin: 0px 10px;
        font-weight: bold;
    }
</style>

<div>
    <h1>Customer Management</h1>

    <table>
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
                        <td><input type="text" name="username" value="{{ $cust['name'] }}" required></td>
                        <td>{{ $cust['email'] }}</td>
                        <td><input type="text" name="phone" value="{{ $cust['phoneNo'] }}" required></td>
                    @else
                        <td><input type="text" value="{{ $cust['name'] }}" disabled></td>
                        <td>{{ $cust['email'] }}</td>
                        <td><input type="text" value="{{ $cust['phoneNo'] }}" disabled></td>
                    @endif

                    <td>{{ $cust['role'] }}</td>
                    <td>{{ $cust['created_at'] }}</td>
                    <td>{{ $cust['updated_at'] ?? '-' }}</td>
                    
                    <td style = "width : 250px" ">
                        @if($isEditing)
                            <button type="submit" style = "background: #84D6B8">Save</button>
                            <a href="{{ route('admin.customerManagement') }}">
                                <button type="button" style = "background: #F6685E">Cancel</button>
                            </a>
                        @else
                            <a href="{{ route('admin.customerManagement', ['edit_id' => $cust['user_id']]) }}">
                                <button type="button" style = "background: #FFFFBD">Edit</button>
                            </a>
                        @endif
                    </td>
                </form>
            </tr>
        @empty
            <tr><td colspan="8">No Customer Records</td></tr>
        @endforelse
    </table>
</div>
@endsection
