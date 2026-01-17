<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class LogPageVisits
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only log authenticated users, GET requests, and successful responses
        if (Auth::check() && $request->isMethod('get') && $response->getStatusCode() < 400) {

            // Exclude API, Assets, Debugbar, etc.
            if ($request->is('api/*', '_debugbar/*', 'sanctum/*', 'livewire/*')) {
                return $response;
            }

            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'page_visit',
                'method' => 'GET',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'details' => ['url' => $request->fullUrl()],
            ]);
        }

        return $response;
    }
}
