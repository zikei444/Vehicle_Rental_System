@extends('layouts.app')

@section('title', 'Login')

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

    /* Optional fade-out animation for alerts */
    .fade-out {
        animation: fadeOut 3s forwards;
        animation-delay: 3s;
    }
    @keyframes fadeOut {
        to {
            opacity: 0;
            visibility: hidden;
        }
    }
</style>

<div class="container">

    <h1>Login</h1>

    @if(session('success'))
        <div class="alert alert-success fade-out" role="alert">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0" style="list-style:none; padding:0;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
    @endif


    <form method="POST" action="{{ route('login.submit')}}">
        @csrf
        <table>
            <tr>
                <td>Email</td>
                <td>:</td>
                <td><input type="text" name="email" value="{{ old('email') }}" required></td>
            </tr>
            <tr>
                <td>Password</td>
                <td>:</td>
                <td><input type="password" name="password" required></td>
            </tr>
            <tr>
                <td colspan="3" id="btn_submit" style="text-align : center">
                    <button type="submit">Login</button>
                </td>
            </tr>
        </table>
    </form>

    <p id="register_url">
        Don't have account? <a href="{{ route('register')}}">Click Here</a>
    </p>

</div>
@endsection
