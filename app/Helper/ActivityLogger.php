<?php

namespace App\Helper;

use App\Models\SystemActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ActivityLogger
{
    /**
     * Catat log aktivitas sistem ke database.
     *
     * @param string $action Jenis aksi (LOGIN, LOGOUT, CREATE, UPDATE, DELETE, APPROVE, REJECT, PENALTY, EXPORT, dll)
     * @param string $module Modul terkait (Auth, Presensi, Izin, Mentoring, Master, User Management, Shift, Setting, dll)
     * @param string $description Deskripsi jelas aktivitas
     * @param array|null $properties Data tambahan (opsional)
     * @param User|null $user User pelaku (jika null, otomatis mengambil Auth::user())
     * @return SystemActivityLog|null
     */
    public static function log(
        string $action,
        string $module,
        string $description,
        ?array $properties = null,
        ?User $user = null
    ): ?SystemActivityLog {
        try {
            $user = $user ?? Auth::user();

            $userId = $user?->id;
            $userName = $user ? ($user->profile?->full_name ?? $user->username ?? "User #{$user->id}") : 'Sistem / Tamu';
            
            $userRole = 'Tamu';
            if ($user) {
                $userRole = match ((int) $user->role_id) {
                    7 => 'Super Admin',
                    1 => 'Admin',
                    6 => 'Asisten Admin',
                    5 => 'Outsider',
                    3 => 'Magang',
                    default => $user->role?->name ?? 'User',
                };
            }

            $ipAddress = request()?->ip() ?? '127.0.0.1';
            $userAgent = request()?->userAgent();

            return SystemActivityLog::create([
                'user_id' => $userId,
                'user_name' => $userName,
                'user_role' => $userRole,
                'action' => strtoupper(trim($action)),
                'module' => trim($module),
                'description' => trim($description),
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'properties' => $properties,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Jangan gagalkan transaksi utama jika log gagal dicatat
            Log::error("Gagal mencatat SystemActivityLog: " . $e->getMessage(), [
                'action' => $action,
                'module' => $module,
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }
}
