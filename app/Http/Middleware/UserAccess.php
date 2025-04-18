<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class UserAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $roles): Response
    {
        $user = Auth::user();

        $allowedRoles = explode('|', $roles);

        if (!$user || !in_array($user->role, $allowedRoles)) {
            return response()->json(['error' => 'Forbidden: You do not have the required role.'], 403);
        }

        return $next($request);
    }
}
