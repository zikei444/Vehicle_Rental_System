<?php

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