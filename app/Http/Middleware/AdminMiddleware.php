<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // السماح لو اليوزر أدمن أو معاة صلاحية الماستر أو السوبر
        $user = $request->user();
        
        if (! $user || ! ($user->role === 'admin' || $user->role === 'catalog_master_root' || $user->is_super)) {
            return redirect('/')->with('error', 'Unauthorized access.');
        }

        return $next($request);
    }
}