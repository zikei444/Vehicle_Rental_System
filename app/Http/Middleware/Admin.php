<?php

namespace App\Http\Middleware;

use Closure;

class Admin
{
    public function handle($request, Closure $next)
    {
        $user = session('user'); // from your factory login
        if (!$user || $user->role !== 'admin') {
            return redirect()->route('customer.dashboard')->withErrors(['error' => 'You are not admin.']);
        }

        return $next($request);
    }
}
