@extends('layouts.app')

@section('title', 'Dashboard - Admin')

@section('content')

<style>
    .container{
        text-align: center;
        color: #106748;
    }

    table{
        margin: auto;
    }

    td{
        text-align: left;
        vertical-align: middle;
        font-size: 20px;
        padding: 30px 10px;
    }

    a{
        text-decoration: none;
        color: #106748;
    }

    .icon{
        height: 120px;
        width: 120px;
    }
</style>

<div class = "container">

    <h1>Admin Dashboard</h1>

    <table>
        <tr>
            <td><a href = "{{ route('admin.customerManagement')}}"><img src="{{ asset('images/dashboard/acc_icon.png') }}" alt="Customer Account Management" class = "icon"></a></td>
            <td><a href = "{{ route('admin.customerManagement')}}">Customer Account Management</a></td>
        </tr>
        <tr>
            <td><a href = "{{ route('admin.vehicles.index')}}"><img src="{{ asset('images/dashboard/vehicle_icon.png') }}" alt="Vehicle Management" class = "icon"></a></td>
            <td><a href = "{{ route('admin.vehicles.index')}}">Vehicle Management</a></td>
        </tr>
        <tr>
            <td><a href = "{{ route('admin.reservations.index')}}"><img src="{{ asset('images/dashboard/reservation_icon.png') }}" alt="Reservation Management" class = "icon"></a></td>
            <td><a href = "{{ route('admin.reservations.index')}}">Reservation Management</a></td>
        </tr>
        <tr>
            <td><a href = "{{ route('ratings_admin.index')}}"><img src="{{ asset('images/dashboard/rating_icon.png') }}" alt="Rating & Feedback Management" class = "icon"></a></td>
            <td><a href = "{{ route('ratings_admin.index')}}">Rating & Feedback Management</a></td>
        </tr>
        <tr>
            <td><a href = "{{ route('maintenance.index')}}"><img src="{{ asset('images/dashboard/maintenance_icon.png') }}" alt="Maintenance Management" class = "icon"></a></td>
            <td><a href = "{{ route('maintenance.index')}}">Maintenance Management</a></td>
        </tr>
    </table>
</div>
@endsection