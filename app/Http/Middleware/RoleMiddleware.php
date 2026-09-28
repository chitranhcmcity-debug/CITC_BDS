<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $userRole = (int) Auth::user()->ma_vai_tro;

        foreach ($roles as $role) {
            if ((int) $role === $userRole) {
                return $next($request);
            }
        }

        abort(403, 'Bạn không có quyền truy cập trang này.');
    }
}
