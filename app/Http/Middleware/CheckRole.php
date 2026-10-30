<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        if ($user->primary_role === 'system_admin') {
            return $next($request);
        }

        if (!$user->hasRole($roles)) {
            abort(403, 'Unauthorized. Your account does not have permission to access this institutional resource.');
        }

        return $next($request);
    }
}

