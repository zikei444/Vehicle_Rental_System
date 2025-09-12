<?php
// STUDENT NAME: LIEW ZI KEI 
// STUDENT ID: 23WMR14570

namespace App\Http\Middleware;


use Closure;
use Illuminate\Http\Request;

class FrameGuard
{
    /**
     * Prevent clickjacking by setting X-Frame-Options header
     */
    public function handle(Request $request, Closure $next)
    {
        return $next($request)->header('X-Frame-Options', 'SAMEORIGIN');
    }
}