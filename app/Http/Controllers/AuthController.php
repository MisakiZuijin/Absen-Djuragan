<?php

namespace App\Http\Controllers;

use App\Models\Broadcast;
use App\Helper\LogConsole;
use Illuminate\Http\Request;
use App\Services\UserService;
use App\Services\SchoolService;
use App\Http\Requests\UserRequest;
use App\Http\Requests\LoginRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    protected UserService $userService;
    protected SchoolService $schoolService;

    public function __construct(UserService $userService, SchoolService $schoolService)
    {
        $this->userService = $userService;
        $this->schoolService = $schoolService;
    }
    public function registerView(): View
    {
        $listSchool = $this->schoolService->getAllSchool();

        $data = [];
        if ($listSchool->isSuccess()) {
            $data['schoolList'] = $listSchool->getData();
        }

        return view("register")->with($data);
    }



    public function insertUser(UserRequest $request)
    {
        $userData = $request->validated();

        $result =  $this->userService->createUser($userData);
        if ($result->isSuccess()) {
            session()->flash('success', 'Pendaftaran berhasil! Silakan menunggu aktivasi.');

            return response()
                ->view('login')
                ->cookie($result->getData()['cookie']);
        }

        $failMessage = $result->getMessage() ?: 'Pendaftaran gagal.';
        $fieldErrors = [];
        if (str_contains(strtolower($failMessage), 'email')) {
            $fieldErrors['email'] = 'Email ini sudah terdaftar. Silakan gunakan email lain.';
        } elseif (str_contains(strtolower($failMessage), 'username')) {
            $fieldErrors['username'] = 'Username ini sudah terdaftar. Silakan gunakan username lain.';
        } else {
            $fieldErrors['general'] = $failMessage;
        }

        return redirect()->back()
            ->withInput()
            ->withErrors($fieldErrors)
            ->with('failed', $fieldErrors['email'] ?? $fieldErrors['username'] ?? $failMessage);
    }

    public function loginView(): View|\Illuminate\Http\RedirectResponse
    {
        if (Auth::check()) {
            $roleId = (int) Auth::user()->role_id;
            return match ($roleId) {
                7, 1 => redirect()->route('admin.home'),
                3 => redirect()->route('user.home'),
                5 => redirect()->route('outsider.dashboard'),
                6 => redirect()->route('assistant.dashboard'),
                default => view("login"),
            };
        }

        return view("login");
    }

    public function loginAction(LoginRequest $request)
    {
        $reqData = $request->validated();

        $deviceToken = $request->cookie('device_token');
        $user = $this->userService->login($reqData, $deviceToken);

        if ($user->isSuccess()) {
            $request->session()->regenerate();
            $data = $user->getData();

            // Catat log login
            \App\Helper\ActivityLogger::log(
                'LOGIN',
                'Auth',
                "Pengguna {$data['full_name']} berhasil masuk ke sistem."
            );

            switch ((int) $data["role_id"]) {
                case 7: // Super Admin
                case 1: // Admin
                    if ($data["cookie"]) {
                        return redirect()->route('admin.home')
                            ->with('success', 'Login berhasil, Selamat Datang ' . $data["full_name"])
                            ->cookie($data["cookie"]);
                    }
                    return redirect()->route('admin.home')->with('success', 'Login berhasil, Selamat Datang ' . $data["full_name"]);

                case 3:
                    $firstBroadcast = Broadcast::orderBy('created_at', 'desc')->first();
                    if ($data["cookie"]) {
                        return redirect()->route("user.home")
                            ->with('success', 'Login berhasil, Selamat Datang ' . $data["full_name"])
                            ->cookie($data["cookie"])
                            ->with('firstBroadcast', $firstBroadcast);
                    }
                    return redirect()->route("user.home")
                        ->with('success', 'Login berhasil, Selamat Datang ' . $data["full_name"])
                        ->with('firstBroadcast', $firstBroadcast);

                case 5:
                    if ($data["cookie"]) {
                        return redirect()->route('outsider.dashboard')
                            ->with('success', 'Login berhasil, Selamat Datang ' . $data["full_name"])
                            ->cookie($data["cookie"]);
                    }
                    return redirect()->route('outsider.dashboard')
                        ->with('success', 'Login berhasil, Selamat Datang ' . $data["full_name"]);

                case 6:
                    if ($data["cookie"]) {
                        return redirect()->route('assistant.dashboard')
                            ->with('success', 'Login berhasil, Selamat Datang ' . $data["full_name"])
                            ->cookie($data["cookie"]);
                    }
                    return redirect()->route('assistant.dashboard')
                        ->with('success', 'Login berhasil, Selamat Datang ' . $data["full_name"]);

                default:
                    return redirect()->route('login.view');
            }
        } else {
            \App\Helper\ActivityLogger::log(
                'LOGIN_FAILED',
                'Auth',
                "Percobaan login gagal untuk akun: " . ($reqData['email'] ?? 'Unknown')
            );

            return redirect()->route('login.view')
                ->withErrors(['login_failed' => $user->getMessage()]);
        }
    }

    public function logoutAction(Request $request)
    {
        $currentUser = Auth::user();
        if ($currentUser) {
            \App\Helper\ActivityLogger::log(
                'LOGOUT',
                'Auth',
                "Pengguna " . ($currentUser->profile?->full_name ?? $currentUser->username ?? 'User') . " berhasil keluar dari sistem.",
                null,
                $currentUser
            );
        }

        $this->userService->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route("login.view")->with('success', 'Anda berhasil keluar halaman.');
    }

    public function forgetPasswordView()
    {

        return view("forgot_password");
    }

    public function forgetPasswordAction(Request $request)
    {
        $result = $this->userService->forgetPassRequest($request);
        if ($result->isSuccess()) {
            return redirect()->route("notif.success.view");
        }
        return redirect()->back()->withInput()->with('error', $result->getMessage());
    }

    public function validationOptView()
    {
        return view('verif');
    }

    public function changePasswordView(string $jwt)
    {
        $secretKey = config('jwt.secret_key', 'default_absen_djuragan_secret_key_2026');
        try {
            $decoded = \Firebase\JWT\JWT::decode($jwt, new \Firebase\JWT\Key($secretKey, 'HS256'));
            $user = User::where('email', $decoded->email ?? null)->first();
            if (!$user) {
                return redirect()->route('forget-password.view')->with('error', 'Pengguna tidak ditemukan.');
            }
            if (isset($decoded->updated_at) && (string) $user->updated_at !== (string) $decoded->updated_at) {
                return redirect()->route('forget-password.view')->with('error', 'Link reset password ini sudah pernah digunakan. Silakan ajukan permintaan baru.');
            }
        } catch (\Firebase\JWT\ExpiredException $e) {
            return redirect()->route('forget-password.view')->with('error', 'Link reset password sudah kedaluwarsa. Silakan ajukan permintaan baru.');
        } catch (\Throwable $e) {
            return redirect()->route('forget-password.view')->with('error', 'Link reset password tidak valid atau rusak.');
        }

        return view("reset-pass-page")->with(["key" => $jwt]);
    }

    public function changePasswordAction(Request $request, string $jwt)
    {
        $result = $this->userService->changePassword($request, $jwt);
        if ($result->isSuccess()) {
            return redirect()->route('login.view')->with('success', 'Password Anda berhasil diperbarui. Silakan login dengan password baru.');
        }
        return redirect()->back()->with('error', $result->getMessage());
    }

    public function successView()
    {
        return view("success-page");
    }
    protected function redirectTo()
    {
        $role = Auth::user()->role->name;

        return match ($role) {
            'Admin' => '/admin/home',
            'Magang' => '/user/home',
            'Outsider' => '/outsider',
            default => '/home',
        };
    }
}
