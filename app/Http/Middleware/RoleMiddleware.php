<?php

namespace App\Http\Middleware;

use App\Services\UserService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware {
    protected $userService;

    public function __construct(UserService $userService) {
        $this->userService = $userService;
    }

    public function handle(Request $request, Closure $next, int $roleId): Response {
        $user = $this->userService->getUserLoggedData();

        if (is_null($user)) {
            return redirect('/')->with('error', 'Anda harus login terlebih dahulu.');
        }

        if ((int) $user->role_id !== $roleId) {
            return redirect('/')->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }
        return $next($request);
    }
}
