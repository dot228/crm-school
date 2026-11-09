<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, int ...$roles): mixed
    {
        if (!$request->user() || !in_array($request->user()->role_id, $roles)) {
            abort(403, 'Доступ заборонено.');
        }

        return $next($request);
    }
}
