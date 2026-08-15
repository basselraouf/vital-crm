<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class CheckAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): HttpResponse
    {
        if (auth()->check() && auth()->user()->hasRole(['owner', 'admin'])) {
            return $next($request);
        }

        return Response::errorResponse('Unauthorized. Admin access required.', [], 403);
    }
}

