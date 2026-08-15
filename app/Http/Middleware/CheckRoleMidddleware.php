<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class CheckRoleMidddleware
{
    /**
     * Handle an incoming request.
     * Usage: middleware('check.role:owner,admin')
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): HttpResponse
    {
        if (!Auth::check() || !Auth::user()->hasAnyRole($roles)) {
            return Response::errorResponse('Unauthorized. Insufficient role.', [], 403);
        }

        return $next($request);
    }
}

