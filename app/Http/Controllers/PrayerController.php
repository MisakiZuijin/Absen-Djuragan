<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Prayer;
use App\Models\PrayerRequest;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PrayerController extends Controller
{
    /**
     * Start a prayer session
     */
    public function startPrayer(Request $request)
    {
        $user = Auth::user();

        // Check if user is role 3 (intern)
        if ($user->role_id !== 3) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        // Check if user already has an active prayer
        $activePrayer = Prayer::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if ($activePrayer) {
            return response()->json(['success' => false, 'message' => 'Anda sudah memiliki sesi shalat yang aktif'], 400);
        }

        // Create new prayer session
        $prayer = Prayer::create([
            'user_id' => $user->id,
            'prayer_type' => $request->input('prayer_type', 'shalat'),
            'started_at' => now(),
            'status' => 'active'
        ]);

        // Update prayer request status
        $prayerRequest = PrayerRequest::firstOrCreate(['user_id' => $user->id]);
        $prayerRequest->update([
            'is_praying' => true,
            'started_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Sesi shalat berhasil dimulai',
            'prayer_id' => $prayer->id
        ]);
    }

    /**
     * End a prayer session
     */
    public function endPrayer(Request $request)
    {
        $user = Auth::user();

        // Check if user is role 3 (intern)
        if ($user->role_id !== 3) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        // Find active prayer session
        $prayer = Prayer::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (!$prayer) {
            return response()->json(['success' => false, 'message' => 'Tidak ada sesi shalat yang aktif'], 400);
        }

        $endedAt = now();
        $duration = Carbon::parse($prayer->started_at)->diffInSeconds($endedAt);

        // Update prayer session
        $prayer->update([
            'ended_at' => $endedAt,
            'duration' => $duration,
            'status' => 'completed'
        ]);

        // Update prayer request status
        $prayerRequest = PrayerRequest::where('user_id', $user->id)->first();
        if ($prayerRequest) {
            $prayerRequest->update([
                'is_praying' => false,
                'started_at' => null
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Sesi shalat berhasil diakhiri',
            'duration' => $duration
        ]);
    }

    /**
     * Get current prayer status
     */
    public function getPrayerStatus()
    {
        $user = Auth::user();

        // Check if user is role 3 (intern)
        if ($user->role_id !== 3) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        $activePrayer = Prayer::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        $prayerRequest = PrayerRequest::where('user_id', $user->id)->first();

        return response()->json([
            'success' => true,
            'has_active_prayer' => $activePrayer ? true : false,
            'is_praying' => $prayerRequest ? $prayerRequest->is_praying : false,
            'prayer_data' => $activePrayer
        ]);
    }

    /**
     * Get prayer history for the user
     */
    public function getPrayerHistory()
    {
        $user = Auth::user();

        // Check if user is role 3 (intern)
        if ($user->role_id !== 3) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        $prayers = Prayer::where('user_id', $user->id)
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'prayers' => $prayers
        ]);
    }

    /**
     * Cancel active prayer session
     */
    public function cancelPrayer()
    {
        $user = Auth::user();

        // Check if user is role 3 (intern)
        if ($user->role_id !== 3) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        // Find active prayer session
        $prayer = Prayer::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (!$prayer) {
            return response()->json(['success' => false, 'message' => 'Tidak ada sesi shalat yang aktif'], 400);
        }

        // Cancel prayer session
        $prayer->update(['status' => 'cancelled']);

        // Update prayer request status
        $prayerRequest = PrayerRequest::where('user_id', $user->id)->first();
        if ($prayerRequest) {
            $prayerRequest->update([
                'is_praying' => false,
                'started_at' => null
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Sesi shalat berhasil dibatalkan'
        ]);
    }
}
