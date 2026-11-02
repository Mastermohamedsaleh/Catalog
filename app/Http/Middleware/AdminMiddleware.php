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
        // التحقق إن المستخدم مسجل دخول وهل هو الأدمن بالكود السري بتاعنا
        if (! $request->user() || $request->user()->role !== 'catalog_master_root') {
            return redirect('/')->with('error', 'Unauthorized access.');
        }

        return $next($request);
    }
}