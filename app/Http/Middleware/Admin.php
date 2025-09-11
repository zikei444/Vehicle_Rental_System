<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Admin
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user(); 

        if (!$user || $user->role !== 'admin') {
            return redirect('/')->withErrors(['error' => 'You are not admin.']);
        }

        return $next($request);
    }
}
