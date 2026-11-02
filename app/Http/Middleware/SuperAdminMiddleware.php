<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->is_super) {
            return $next($request);
        }

        return redirect()->route('admin.dashboard')->with('error', 'Unauthorized access! Only Super Admin can manage administrators.');
    }
}