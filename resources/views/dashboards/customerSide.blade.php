@extends('layouts.app')

@section('title', 'Dashboard')

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

    <h1>Welcome to Beng Beng Vehicle</h1>

    <table>
        <tr>
            <td><a href = "{{ route('profile.edit')}}"><img src="{{ asset('images/dashboard/acc_icon.png') }}" alt="Edit Profile" class = "icon"></a></td>
            <td><a href = "{{ route('profile.edit')}}">Edit Profile</a></td>
        </tr>
        <tr>
            <td><a href = "{{ route('profile.edit')}}"><img src="{{ asset('images/dashboard/vehicle_icon.png') }}" alt="Vehicle List" class = "icon"></a></td>
            <td><a href = "{{ route('profile.edit')}}">Vehicle List</a></td>
        </tr>
        <tr>
            <td><a href = "{{ route('profile.edit')}}"><img src="{{ asset('images/dashboard/reservation_icon.png') }}" alt="Make a Reservation" class = "icon"></a></td>
            <td><a href = "{{ route('profile.edit')}}">Make a Reservation</a></td>
        </tr>
        <tr>
            <td><a href = "{{ route('profile.edit')}}"><img src="{{ asset('images/dashboard/rating_icon.png') }}" alt="Give us ratings and feedbacks" class = "icon"></a></td>
            <td><a href = "{{ route('profile.edit')}}">Give Us Ratings and Feedbacks</a></td>
        </tr>
        <tr>
            <td><a href = "{{ route('profile.edit')}}"><img src="{{ asset('images/dashboard/maintenance_icon.png') }}" alt="Maintenance" class = "icon"></a></td>
            <td><a href = "{{ route('profile.edit')}}">Maintenance</a></td>
        </tr>
    </table>

</div>

@endsection