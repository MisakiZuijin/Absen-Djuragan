<?php

namespace App\Services;

use App\Helper\ActionResult;
use App\Http\Requests\EditInternRequest;
use App\Repositories\Interface\DetailProjectRepository;
use App\Repositories\Interface\InternRepository;
use App\Repositories\Interface\ProfileRepository;
use App\Repositories\Interface\UserRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Intern;
use App\Models\DiscountTime;
use App\Models\AdjustableAttd;
use App\Models\DetailSchedule;
use App\Models\Attendance;
use App\Models\Schedule;

use function Sentry\captureException;

class InternService
{
    protected InternRepository $internRepository;
    protected UserRepository $userRepository;
    protected ProfileRepository $profileRepository;
    protected DetailProjectRepository $detailProjectRepository;

    public function __construct(
        UserRepository $userRepository,
        InternRepository $internRepository,
        ProfileRepository $profileRepository,
        DetailProjectRepository $detailProjectRepository
    ) {
        $this->internRepository = $internRepository;
        $this->userRepository = $userRepository;
        $this->profileRepository = $profileRepository;
        $this->detailProjectRepository = $detailProjectRepository;
    }

    public function absence(array $data): ActionResult
    {
        try {
            $internId = $data['id'];
            unset($data["id"]);
            $result = $this->internRepository->update($internId, $data);
            return new ActionResult(true, "success update absence", $result);
        } catch (\Throwable $th) {
            captureException($th);
            return new ActionResult(false, "failed update", null);
        }
    }

    public function getAll()
    {
        try {
            $result = $this->internRepository->getAll();
            return new ActionResult(true, "success success get all intern", $result);
        } catch (\Throwable $th) {
            captureException($th);
            return new ActionResult(false, "failed update", null);
        }
    }

    public function internTotal(): ActionResult
    {
        try {
            $result = $this->internRepository->countByInternRole();

            if (empty($result)) {
                return new ActionResult(false, "data empty", null);
            }

            $data = $result[0]["count"] ?? 0;
            return new ActionResult(true, "success retrieve data", $data);
        } catch (\Throwable $th) {
            captureException($th);
            return new ActionResult(false, "something weird", null);
        }
    }

    public function update(EditInternRequest $editInternRequest): ActionResult
    {
        try {
            DB::beginTransaction();
            $data = $editInternRequest->validated();
            $userId = $data["user_id"];


            $userData = [
                "email" => $data["email"],
                "username" => $data["username"],
                "os" => $data["os"],
                "browser" => $data["browser"],
                "is_active" => $data["account_status"],
                "is_confirm" => $data["email_confirm"],
                "is_reset_token" => $data["is_reset_device_token"] ?? false,
                "is_gps_activate" => isset($data["is_gps_active"]) ? (int) $data["is_gps_active"] : 1,
                "is_gps_support" => isset($data["is_gps_active"]) ? (int) $data["is_gps_active"] : 1
            ];


            if (!empty($data["password"])) {
                if (strlen($data["password"] <= 8)) {
                    return new ActionResult(false, "password harus lebih dari 8 digit", null);
                }
                if ($data["password"] != $data["confirm_password"]) {
                    return new ActionResult(false, "password tidak sama dengan validasi", null);
                }
                $userData["password"] = $data["password"];
            }


            $userResult = $this->userRepository->update($userId, $userData);


            $profileData = [
                "full_name" => $data['full_name'],
                "birth_place" => $data['birth_place'],
                "date_of_birth" => $data["birth_date"],
                "NIP" => $data["nip"],
                "phone" => $data["phone"],
                "gender" => $data["gender"] ?? null
            ];

            $this->profileRepository->update($userResult->profile->id, $profileData);

            $internData = [
                "school_id" => $data["school_id"],
                "division_id" => $data["division_id"],
                "brand_id" => $data["brand_id"] ?? null,
                "start_date" => $data["in_date"],
                "end_date" => $data["out_date"],
                "nim" => $data["nim"]
            ];


            $internId = $userResult->intern->id;


            $intern = $this->internRepository->update($internId, $internData);

            $nomorTujuan = $data["parent_whatsapp_number"] ?? null;

            if ($intern) {
                if (!empty($nomorTujuan)) {
                    $intern->whatsappNumber()->updateOrCreate(
                        ['intern_id' => $intern->id],
                        ['phone_number' => $nomorTujuan]
                    );
                } else {
                    // Jika input nomor WA kosong, hapus data yang ada
                    $intern->whatsappNumber()->delete();
                }

                // Update / create intern_accounts credentials
                $accountData = [
                    'gdrive_url' => $data['gdrive_url'] ?? null,
                    'github_url' => $data['github_url'] ?? null,
                    'gmail_account' => $data['gmail_account'] ?? null,
                    'figma_url' => $data['figma_url'] ?? null,
                    'notes' => $data['notes'] ?? null,
                ];

                if (!empty($data['gmail_password'])) {
                    $accountData['gmail_password'] = $data['gmail_password'];
                }

                if (isset($data['social_media_links']) && is_array($data['social_media_links'])) {
                    $filteredLinks = array_values(array_filter($data['social_media_links'], function ($item) {
                        return !empty($item['platform']) || !empty($item['username']) || !empty($item['url']);
                    }));
                    $accountData['social_media_links'] = !empty($filteredLinks) ? $filteredLinks : null;
                } else {
                    $accountData['social_media_links'] = null;
                }

                $hasAccountData = !empty($accountData['gdrive_url'])
                    || !empty($accountData['github_url'])
                    || !empty($accountData['gmail_account'])
                    || !empty($accountData['gmail_password'])
                    || !empty($accountData['figma_url'])
                    || !empty($accountData['social_media_links'])
                    || !empty($accountData['notes'])
                    || $intern->account()->exists();

                if ($hasAccountData) {
                    $intern->account()->updateOrCreate(
                        ['intern_id' => $intern->id],
                        $accountData
                    );
                }
            }

            if (!empty($data["project_id"])) {
                $this->detailProjectRepository->create(["intern_id" => $internId, "project_id" => $data["project_id"]]);
            }

            DB::commit();

            return new ActionResult(true, "success update intern data", $data);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('updateIntern error: ' . $th->getMessage(), ['trace' => $th->getTraceAsString()]);
            captureException($th);
            return new ActionResult(false, "failed to update data intern", null);
        }
    }

    public function deleteInternDivision(int $internId)
    {
        try {
            DB::transaction(function () use ($internId) {
                $intern = Intern::findOrFail($internId);

                // Hapus detail project
                $intern->detailProject()->delete();

                // Ambil semua ID schedule
                $scheduleIds = $intern->schedules()->pluck('id')->toArray();

                if (!empty($scheduleIds)) {
                    // Ambil semua ID detail_schedule & attendance_id terkait
                    $detailSchedules = DetailSchedule::whereIn('schedule_id', $scheduleIds)->get(['id', 'attendance_id']);
                    $detailIds = $detailSchedules->pluck('id')->toArray();
                    $attendanceIds = $detailSchedules->pluck('attendance_id')->filter()->unique()->toArray();

                    if (!empty($detailIds)) {
                        // Bulk delete AdjustableAttd
                        AdjustableAttd::whereIn('detail_schedule_id', $detailIds)->delete();

                        // Bulk delete DetailSchedule
                        DetailSchedule::whereIn('id', $detailIds)->delete();
                    }

                    if (!empty($attendanceIds)) {
                        // Bulk delete Attendance
                        Attendance::whereIn('id', $attendanceIds)->delete();
                    }

                    // Bulk delete DiscountTime & Schedule
                    DiscountTime::whereIn('schedule_id', $scheduleIds)->delete();
                    Schedule::whereIn('id', $scheduleIds)->delete();
                }

                // Hapus data user & profile terkait
                $user = $intern->user;
                $intern->delete();

                if ($user) {
                    if ($user->profile) {
                        $user->profile->delete();
                    }
                    $user->delete();
                }
            });

            return redirect()->back()->with('success', 'Data anggota sukses dihapus!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Data anggota gagal dihapus! ' . $e->getMessage());
        }
    }
}
