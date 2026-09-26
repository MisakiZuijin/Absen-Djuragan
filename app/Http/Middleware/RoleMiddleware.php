<?php

namespace App\Http\Middleware;

use App\Services\UserService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function handle(Request $request, Closure $next, ...$roleIds): Response
    {
        $user = $this->userService->getUserLoggedData();

        if (is_null($user)) {
            return redirect('/')->with('error', 'Anda harus login terlebih dahulu.');
        }

        $allowedRoles = [];
        foreach ($roleIds as $r) {
            foreach (explode(',', (string) $r) as $subRole) {
                if (trim($subRole) !== '') {
                    $allowedRoles[] = (int) trim($subRole);
                }
            }
        }

        $userRoleId = (int) $user->role_id;

        // Super Admin (7) memiliki hak akses penuh ke seluruh rute Admin (1)
        if ($userRoleId === 7 && (in_array(1, $allowedRoles, true) || in_array(7, $allowedRoles, true))) {
            return $next($request);
        }

        if (!in_array($userRoleId, $allowedRoles, true)) {
            return redirect('/')->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }
        return $next($request);
    }
}
