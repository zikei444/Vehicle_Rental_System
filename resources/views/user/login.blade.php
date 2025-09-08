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
</style>

<div class = "container">

    <h1>Login</h1>
    
    <form method = "POST" action = "{{ route('login.submit')}}">
        @csrf
        <table>
            <tr>
                <td>Email</td>
                <td>:</td>
                <td><input type = "text" name = "email" required></td>
            </tr>
            <tr>
                <td>Password</td>
                <td>:</td>
                <td><input type = "password" name = "password" required></td>
            </tr>
            <tr>
                <td colspan = "3" id = "btn_submit" style = "text-align : center">
                    <button type = "submit">Login</button>
                </td>
            </tr>
        </table>
    </form>

    <p id = "register_url">
        Don't have account? <a href = "{{ route('register')}}">Click Here</a>
    </p>

</div>
@endsection