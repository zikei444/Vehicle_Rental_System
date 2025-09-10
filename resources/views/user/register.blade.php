@extends('layouts.app')

@section('title', 'Register')

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

    #btn_submit button {
        color: #106748;
        font-size: 20px;
        padding: 10px 20px;
        background: #E7EFEC;
        border: 2px solid #106748;
        border-radius: 5px;
    }
</style>

<div class="container">

    {{-- Display success message --}}
    @if (session('success'))
        <div class="alert alert-success" role="alert">
            <ul class="mb-0" style="list-style:none; padding:0;">
                <li>{{ session('success') }}</li>
            </ul>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0" style="list-style:none; padding:0;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <h1>Registration</h1>
    
    <form method="POST" action="{{ route('register.submit') }}">
        @csrf
        <table>
            <tr>
                <td>Username</td>
                <td>:</td>
                <td>
                    <input type="text" name="username" value="{{ old('username') }}" required>
                    @error('username')
                        <div style="color:red; font-size:14px;">{{ $message }}</div>
                    @enderror
                </td>
            </tr>
            <tr>
                <td>Email</td>
                <td>:</td>
                <td>
                    <input type="text" name="email" value="{{ old('email') }}" required>
                    @error('email')
                        <div style="color:red; font-size:14px;">{{ $message }}</div>
                    @enderror
                </td>
            </tr>
            <tr>
                <td>Phone Number</td>
                <td>:</td>
                <td>
                    <input type="text" name="phone" value="{{ old('phone') }}" required>
                    @error('phone')
                        <div style="color:red; font-size:14px;">{{ $message }}</div>
                    @enderror
                </td>
            </tr>
            <tr>
                <td>Password</td>
                <td>:</td>
                <td>
                    <input type="password" name="password" required>
                    @error('password')
                        <div style="color:red; font-size:14px;">{{ $message }}</div>
                    @enderror
                </td>
            </tr>
            <tr>
                <td>Confirm Password</td>
                <td>:</td>
                <td>
                    <input type="password" name="password_confirmation" required>
                </td>
            </tr>
            <tr>
                <td colspan="3" id="btn_submit" style="text-align:center">
                    <button type="submit">Register</button>
                </td>
            </tr>
        </table>
    </form>

    <br>
    <p id="login_url">
        Already have an account? <a href="{{ route('login') }}">Click Here</a>
    </p>

</div>

@endsection