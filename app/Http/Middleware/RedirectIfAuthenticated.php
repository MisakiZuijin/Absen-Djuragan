<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();
                return redirect($this->redirectPathForRole($user->role_id ?? null));
            }
        }

        return $next($request);
    }

    /**
     * Tentukan redirect path berdasarkan role_id user.
     */
    private function redirectPathForRole(?int $roleId): string
    {
        return match ($roleId) {
            7, 1 => '/admin/home',
            3 => '/user/home',
            5 => '/outsider',
            6 => '/assistant/dashboard',
            default => '/',
        };
    }
}
