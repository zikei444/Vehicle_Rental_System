@extends('layouts.app')

@section('title', 'Profile')

@section('content')

<style>
    .container{
        text-align: center;
        color: #106748;
    }

    table{
        margin: auto;
    }

    td, th{
        text-align: left;
        vertical-align: middle;
        font-size: 20px;
        padding: 20px 10px;
    }

    a{
        color: #106748;
    }

    .icon{
        height: 120px;
        width: 120px;
    }

    #login_url{
        font-size: 15px
    }

    button {
        color: #106748;
        font-size: 20px;
        padding: 10px 20px;
        background: #E7EFEC;
        border: 2px solid #106748;
        border-radius: 5px;
        margin: 0px 10px;
    }

    #reservation td, th{
        border: 1px solid #106748;
        width: 150px;
        text-align: center;
    }

    .box{
        padding: 20px 0px;
    }
</style>

<div class="container">

    @if ($errors->any())
        <div class="alert alert-danger shadow-sm">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li><i class="bi bi-exclamation-circle"></i> {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success shadow-sm">
            <ul class="mb-0">
                <li><i class="bi bi-check-circle"></i> {{ session('success') }}</li>
            </ul>
        </div>
    @endif

    <div class = "box">
        <h1>Profile</h1>
        @php
            $userObj = session('user');

            if(isset($userData)) {
                $userObj = (object) $userData;
            }
        @endphp

        <!-- Update Profile Form -->
        <form action="{{ route('profile.update') }}" method="POST">
            @csrf
            @method('PUT')
            <table>
                <tr>
                    <td>Username</td>
                    <td>
                        <input type="text" name="username"
                            value="{{ old('username', $userObj->name ?? '') }}"
                            {{ request()->get('edit') !== 'true' ? 'disabled' : '' }}>
                    </td>
                </tr>
                <tr>
                    <td>Phone</td>
                    <td>
                        <input type="text" name="phone"
                            value="{{ old('phone', $userObj->phone ?? $userObj->phoneNo ?? '') }}"
                            {{ request()->get('edit') !== 'true' ? 'disabled' : '' }}>
                    </td>
                </tr>
            </table>

            <div style="text-align:center; margin-top:10px;">
                @if(request()->get('edit') !== 'true')
                    <a href="{{ route('profile.edit', ['edit' => 'true']) }}">
                        <button type="button" style="background: #FFFFBD">Edit</button>
                    </a>
                @else
                    <button type="submit" style="background: #84D6B8">Save</button>
                    <a href="{{ route('profile.edit') }}">
                        <button type="button" style="background: #F6685E">Cancel</button>
                    </a>
                @endif
            </div>
        </form>

<!-- Delete Account Form -->
<form action="{{ route('profile.destroy') }}" method="POST" style="text-align:center; margin-top:20px;" 
      onsubmit="return confirm('Are you sure you want to delete your account? After deletion, all your data cannot be restored!');">
    @csrf
    @method('DELETE')
    <button type="submit" style="background: #F6685E; color: white;">Delete Account</button>
</form>

    </div>

    <div class = "box">
        <h2>Reservation Summary</h2>
        </br>
        <table id="reservation">
            <tr>
                <th>Status</th>
                <th>Number</th>
            </tr>
            <tr>
                <td style = "color: blue">Ongoing</td>
                <td>{{ $reservationSummary['ongoing'] ?? 0 }}</td>            
            </tr>
            <tr>
                <td style = "color: green">Completed</td>
                <td>{{ $reservationSummary['completed'] ?? 0 }}</td>            
            </tr>
            <tr>
                <td style = "color: red">Cancelled</td>
                <td>{{ $reservationSummary['cancelled'] ?? 0 }}</td>                
            </tr>
        </table>
    </div>

</div>
@endsection
