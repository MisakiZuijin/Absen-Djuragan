<?php

namespace App\Services;

use App\Repositories\Interface\ProfileRepository;
use App\Repositories\Interface\UserRepository;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Support\Facades\DB;
use App\Helper\ActionResult;
use App\Helper\LogConsole;
use App\Http\Requests\StorePermitPresenceRequest;
use App\Mail\ResetPassMailable;
use App\Repositories\Interface\InternRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Jenssegers\Agent\Agent;
use App\Models\PermitReason;
use App\Models\DetailSchedule;
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;
use Psy\Readline\Hoa\Console;
use Symfony\Component\Console\Logger\ConsoleLogger;

use function Sentry\captureException;

class UserService {
    protected $userRepository;
    protected $profileRepository;
    protected $internRepository;

    public function __construct(UserRepository $userRepo, ProfileRepository $profileRepo, InternRepository $internRepo) {
        $this->userRepository = $userRepo;
        $this->profileRepository = $profileRepo;
        $this->internRepository = $internRepo;
    }

    public function getUserLoggedData() {

        $user = $this->userRepository->getAuthenticatedUser();
        if (is_null($user)) {
            return null;
        }
        return $user;
    }
    public function getUserById($id) {
        try {
            $internData = $this->userRepository->findById($id);

            $date = new \DateTime($internData["created_at"]);
            $internData["tgl_masuk"] = $date->format('Y-m-d');

            return new ActionResult(true, "success get User by id", $internData);
        } catch (\Throwable $th) {
            captureException($th);
            return new ActionResult(false, "something weird, failed to get User by id", null);
        }
    }

    public function createUser($data): ActionResult {
        $userDataKeys = ['username', 'email', 'password', 'is_gps_available'];
        $schoolId = $data["school_origin_id"];
        $nim = $data['nim'];
        unset($data["school_origin_id"]);
        unset($data["nim"]);


        $userData = array_intersect_key($data, array_flip($userDataKeys));
        $profileData = array_diff_assoc($data, $userData);

        // Pastikan gender ikut ke profileData
        if (isset($data['gender'])) {
            $profileData['gender'] = $data['gender'];
        }

        $agent = new Agent();
        $userData["role_id"] = 3;
        $userData["os"] = $agent->platform();
        $userData["browser"] = $agent->browser();


        $device_uid = bin2hex(random_bytes(8));
        $cookieLifetime = 10 * 365 * 24 * 60;
        $cookie = cookie('device_token', $device_uid, $cookieLifetime);
        $userData["device"] = $device_uid;

        $userData["is_gps_support"] = $userData["is_gps_available"];
        $userData['is_gps_activate'] = $userData["is_gps_support"];
        unset($userData["is_gps_available"]);


        try {


            $userEmailExist = $this->userRepository->findByEmail($data['email']);
            $usernameExist = $this->userRepository->findByUsername($data['username']);

            if ($userEmailExist) return new ActionResult(false, "email sudah digunakan", null);
            if ($usernameExist) return new ActionResult(false, "username sudah digunakan", null);


            DB::beginTransaction();

            $userResult = $this->userRepository->store($userData);
            $profileData['user_id'] = $userResult->id;
            $profileResult = $this->profileRepository->store($profileData);

            $internData = [
                "user_id" => $userResult->id,
                "school_id" => $schoolId,
                "NIM" => $nim,
            ];

            $internResult = $this->internRepository->store($internData);

            DB::commit();

            unset($userResult['password']);

            $result = [
                "id" => $userResult->id,
                "username" => $userResult->username,
                "email" => $userResult->email,
                "full_name" => $profileResult->full_name,
                "address" => $profileResult->phone,
                "date_of_birth" => $profileResult->date_of_birth,
                "birth_place" => $profileResult->birth_place,
                "intern" => $internResult,
                "cookie" => $cookie
            ];

            return new ActionResult(true, "register success", $result);
        } catch (\Exception $e) {
            DB::rollback();
            captureException($e);
            return new ActionResult(false, "register failed", null);
        }
    }

    public function login($reqData, $deviceToken): ActionResult {
    try {
        $credentials = ['email' => $reqData['username'], 'password' => $reqData['password']];
        $usernameOrEmail = $credentials['email'];
        $password = $credentials['password'];

        if (filter_var($usernameOrEmail, FILTER_VALIDATE_EMAIL)) {
            $user = $this->userRepository->findByEmail($usernameOrEmail);
        } else {
            $user = $this->userRepository->findByUsername($usernameOrEmail);
        }

        // Cek apakah user ada dan password benar
        if (!$user || !Hash::check($password, $user->password)) {
            return new ActionResult(false, "Cek kembali username/email dan password anda", null);
        }

        // Periksa status aktif dan konfirmasi untuk semua role kecuali admin (role_id 1)
        if ($user->role_id != 1) {
            if (!$user->is_active) {
                return new ActionResult(false, "Akun Anda belum aktif. Silakan hubungi administrator.", null);
            }

            if (!$user->is_confirm) {
                return new ActionResult(false, "Akun Anda belum dikonfirmasi. Silakan hubungi administrator.", null);
            }
        }

        // Validasi device khusus untuk role magang (role_id 3)
        if ($user->role_id == 3) {
            $agent = new Agent();

            if ($user->is_reset_token) {
                $device_uid = bin2hex(random_bytes(8));
                $cookieLifetime = 10 * 365 * 24 * 60;
                $cookie = cookie('device_token', $device_uid, $cookieLifetime);
                $this->userRepository->update($user->id, ['device' => $device_uid, 'is_reset_token' => false]);
            }

            if ($deviceToken != $user->device && !$user->is_reset_token) {
                return new ActionResult(false, "Anda tidak bisa login di device yang berbeda");
            }

            if (!$agent->is($user->os)) {
                return new ActionResult(false, "Anda tidak bisa login di OS yang berbeda");
            }

            if (!$agent->is($user->browser)) {
                return new ActionResult(false, "Anda tidak bisa login di browser yang berbeda");
            }
        }

        $this->userRepository->attemptLogin($user);

        $profile = $this->profileRepository->findByUserId($user->id);

        $result = [
            "id" => $user->id,
            "user_id" => $profile->user_id ?? $user->id,
            "username" => $user->username,
            "email" => $user->email,
            "role_id" => $user->role_id,
            "full_name" => $profile->full_name ?? "",
            "address" => $profile->phone ?? "",
            "date_of_birth" => $profile->date_of_birth ?? "",
            "birth_place" => $profile->birth_place ?? "",
            "cookie" => $cookie ?? null
        ];

        return new ActionResult(true, "Login berhasil", $result);
    } catch (\Exception $e) {
        captureException($e);
        return new ActionResult(false, "Terjadi kesalahan saat login. Silakan coba lagi nanti.", null);
    }
}


    public function logout(): ActionResult {
        try {
            $this->userRepository->deleteAuthenticatedUser();
            return new ActionResult(true, "success logout", null);
        } catch (\Exception $e) {
            captureException($e);
            return new ActionResult(false, "something wrong, try again in several minute", null);
        }
    }

    public function updateProfile(UpdateProfileRequest $updateProfileRequest, $id) {
        try {
            DB::beginTransaction();
            $data = $updateProfileRequest->validated();

            $data['full_name'] = $data['nama'];
            $data['phone'] = $data['nohp'];
            $data['address'] = $data['alamat'];

            $result = $this->profileRepository->update($id, $data);

            if ($data["password"]) $this->userRepository->update($result->user_id, ["password" =>  $data["password"]]);

            DB::commit();
            return new ActionResult(true, "Successfully added data into profile", $result);
        } catch (\Throwable $th) {
            DB::rollBack();
            return new ActionResult(false, "Failed to update, something went wrong", null);
        }
    }


    public function getUserDataByInternId($interId): ActionResult {
        try {
            $result = $this->internRepository->getById($interId);


            return new ActionResult(true, "success retrive user by intern id", $result->user->profile);
        } catch (\Throwable $th) {
            captureException($th);
            return new ActionResult(false, "failed retrive user by intern id", null);
        }
    }

    public function forgetPassRequest(Request $request): ActionResult {
        try {
            $data = $request->validate([
                'email' => 'required|email'
            ]);

            $user = $this->userRepository->findByEmail($data['email']);

            if (!$user) {
                return new ActionResult(false, "Email not found", null);
            }

            $payload = [
                'email' => $user->email,
                'exp' => time() + 3600
            ];

            $secretKey = env('JWT_SECRET_KEY', 'ular sanca baik hati');
            $jwt = JWT::encode($payload, $secretKey, 'HS256');

            $resetUrl = route('change-password.view', ['jwt' => $jwt]);

            $details = [
                'body' => 'We have received a request to reset your password. Below is your reset link:',
                'url' => $resetUrl
            ];

            Mail::to($data['email'])->send(new ResetPassMailable($details));

            return new ActionResult(true, "Successfully sent password reset link, check your email", null);
        } catch (\Throwable $th) {
            captureException($th);
            LogConsole::info($th);
            return new ActionResult(false, "Failed to send password reset link", null);
        }
    }
    public function changePassword(Request $request, $jwt) {
        try {
            $data = $request->validate([
                "password" => 'required|min:8',
            ]);
            $secretKey = env('JWT_SECRET_KEY', 'ular sanca baik hati');
            $decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));

            $email = $decoded->email;

            $user = $this->userRepository->findByEmail($email);
            if (!$user) {
                return response()->json(['error' => 'Invalid token'], 400);
            }

            $this->userRepository->update($user->id, ["password" => $data['password']]);

            return new ActionResult(true, "success change password", null);
        } catch (\Exception $e) {
            captureException($e);
            return new ActionResult(false, "failed change password", null);
        }
    }

    public function createPermitPresence(StorePermitPresenceRequest $storePermitPresenceRequest): ActionResult {
        try {
            $validated = $storePermitPresenceRequest->validated();

            $detailSchedule = DetailSchedule::find($validated['id']);
            if (!$detailSchedule) {
                return new ActionResult(false, "DetailSchedule not found", null);
            }

            if ($detailSchedule->permit_reason_id != 0) {
                $permitReason = PermitReason::find($detailSchedule->permit_reason_id);
                if ($permitReason) {
                    $permitReason->update([
                        'description' => $validated['keterangan'],
                        'proof_url' => $validated['link-google-drive'],
                        'permit_category_id' => $validated['kategori-izin'],
                    ]);
                } else {
                    return new ActionResult(false, "PermitReason not found", null);
                }
            } else {
                // Jika permit_reason_id adalah 0, create PermitReason baru
                $permitReason = PermitReason::create([
                    'description' => $validated['keterangan'],
                    'proof_url' => $validated['link-google-drive'],
                    'permit_category_id' => $validated['kategori-izin'],
                ]);

                $detailSchedule->permit_reason_id = $permitReason->id;
            }

            // Update detail jadwal
            $detailSchedule->attd_status_id = 3;
            $detailSchedule->isChangeSchedule = isset($validated['jam-option']) ? $validated['jam-option'] : 0;
            $detailSchedule->save();

            $result = $this->userRepository->createPermitPresence($validated);

            return new ActionResult(true, "Permit presence  created successfully", $result);
        } catch (\Throwable $th) {
            LogConsole::info($th);
            captureException($th); // Melacak error
            return new ActionResult(false, "Failed to create permit presence, something went wrong", null);
        }
    }
}
