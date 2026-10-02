<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Sentry\Laravel\Integration;
use Throwable;

class Handler extends ExceptionHandler {
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void {
        $this->reportable(function (Throwable $e) {
            Integration::captureUnhandledException($e);
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $e
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $e)
    {
        // Penanganan Livewire jika entitas/model di database dibatalkan atau dihapus saat Livewire sedang aktif
        if (($request->is('livewire/*') || $request->hasHeader('X-Livewire')) && ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException || ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException && str_contains($e->getMessage(), 'No query results for model')))) {
            return response()->json([
                'components' => [],
            ], 200);
        }

        // Penanganan otomatis jika token CSRF / Sesi kedaluwarsa (HTTP 419)
        if ($e instanceof TokenMismatchException || ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException && $e->getStatusCode() === 419)) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Sesi keamanan Anda telah kedaluwarsa dan diperbarui otomatis. Silakan coba kembali.',
                    'csrf_token' => csrf_token(),
                ], 419);
            }

            // Jika error terjadi pada aksi autentikasi / login / form publik
            if ($request->is('loginAction') || $request->is('login') || $request->is('user/action/*') || $request->is('forgot-password')) {
                return redirect()->route('login.view')
                    ->withInput($request->except(['password', 'password_confirmation', '_token']))
                    ->with('warning', 'Sesi Anda telah kedaluwarsa dan diperbarui secara otomatis. Silakan klik Login kembali.');
            }

            // Jika pengguna belum login
            if (!auth()->check()) {
                return redirect()->route('login.view')
                    ->with('warning', 'Sesi telah berakhir. Silakan login kembali.');
            }

            // Jika pengguna sudah login, kembalikan ke halaman sebelumnya dengan token segar
            return redirect()->back()
                ->withInput($request->except(['password', 'password_confirmation', '_token']))
                ->with('warning', 'Sesi Anda sempat terhenti sejenak. Halaman telah disegarkan, silakan ulangi tindakan Anda.');
        }

        return parent::render($request, $e);
    }
}
