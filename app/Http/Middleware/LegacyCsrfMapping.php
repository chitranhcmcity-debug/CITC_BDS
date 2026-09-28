<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class LegacyCsrfMapping
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->has('_csrf_token') && ! $request->has('_token')) {
            $request->merge(['_token' => $request->input('_csrf_token')]);
        }

        return $next($request);
    }
}
