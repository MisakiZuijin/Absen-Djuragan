<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ExternalApiAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $expectedKey = config('services.external.api_key') ?: env('EXTERNAL_API_KEY');

        if (empty($expectedKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Layanan API eksternal belum dikonfigurasi pada server.',
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        // Cek header X-API-KEY atau Authorization Bearer
        $providedKey = $request->header('X-API-KEY') ?: $request->bearerToken();

        if (empty($providedKey) || !hash_equals((string) $expectedKey, (string) $providedKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Kunci API (X-API-KEY) tidak valid atau tidak disertakan.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
