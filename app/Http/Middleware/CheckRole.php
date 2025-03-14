<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Add debugging
        \Illuminate\Support\Facades\Log::info('Role check', [
            'user' => $request->user() ? $request->user()->id : 'unauthenticated',
            'user_role' => $request->user() ? $request->user()->role : 'none',
            'required_roles' => $roles
        ]);

        if (!$request->user() || !in_array($request->user()->role, $roles)) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
