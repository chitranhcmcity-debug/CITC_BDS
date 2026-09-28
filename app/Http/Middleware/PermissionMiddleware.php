<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        if ((int) $user->ma_vai_tro === 1) {
            return $next($request);
        }

        // Check if role has the permission code
        $hasPermission = DB::table('vai_tro_quyen_han')
            ->join('quyen_han', 'quyen_han.id', '=', 'vai_tro_quyen_han.permission_id')
            ->where('vai_tro_quyen_han.role_id', $user->ma_vai_tro)
            ->where('quyen_han.code', $permission)
            ->exists();

        if (! $hasPermission) {
            abort(403, 'Bạn không có quyền thực hiện thao tác này.');
        }

        return $next($request);
    }
}
