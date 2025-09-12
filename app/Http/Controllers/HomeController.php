<?php

// STUDENT NAME: LIEW ZI KEI 
// STUDENT ID: 23WMR14570

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        return view('home');
    }
}
