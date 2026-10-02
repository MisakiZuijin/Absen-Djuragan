<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Broadcast;
use App\Models\BroadcastImage;
use App\Models\Division;
use App\Models\Office;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ScheduledBroadcastController extends Controller
{
    /**
     * Menampilkan halaman utama Broadcast Terjadwal (Pesan & Pertanyaan ke Pemagang).
     */
    public function index()
    {
        $broadcastlist = Broadcast::scheduledBroadcasts()
            ->with([
                'divisions:id,name',
                'users:id,username',
                'shifts:id,name,start_time,end_time',
                'offices:id,name',
                'images:id,broadcast_id,image',
                'reports.user.profile:id,user_id,full_name'
            ])
            ->latest()
            ->paginate(5);

        $divisions = Division::select('id', 'name')->orderBy('name')->get();
        $users = User::select('id', 'username')
            ->whereHas('intern')
            ->with(['profile:id,user_id,full_name', 'intern.brand:id,name', 'intern.division:id,name'])
            ->get();
        $shifts = Shift::select('id', 'name', 'start_time', 'end_time')->orderBy('name')->get();
        $offices = Office::select('id', 'name')->orderBy('name')->get();

        /** @var User|null $authUser */
        $authUser = auth()->user();
        if ($authUser instanceof User) {
            $authUser->loadMissing('profile');
        }

        return view('admin.scheduled-broadcast.index', [
            'user' => $authUser,
            'broadcastlist' => $broadcastlist,
            'divisions' => $divisions,
            'users' => $users,
            'shifts' => $shifts,
            'offices' => $offices,
        ]);
    }

    /**
     * Menyimpan broadcast pesan/pertanyaan baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'scheduled_at' => 'required|date',
            'shifts' => 'required|array|min:1',
            'shifts.*' => 'exists:shifts,id',
            'broadcast_type' => 'required|in:all,division,specific,office,shift',
            'divisions' => 'nullable|required_if:broadcast_type,division|array',
            'users' => 'nullable|required_if:broadcast_type,specific|array',
            'offices' => 'nullable|required_if:broadcast_type,office|array',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ], [
            'scheduled_at.required' => 'Waktu jadwal pengiriman wajib diisi.',
            'scheduled_at.date' => 'Format jadwal pengiriman tidak valid.',
            'shifts.required' => 'Target shift kerja wajib dipilih.',
            'shifts.min' => 'Pilih minimal satu shift kerja.',
            'images.*.image' => 'Berkas lampiran harus berupa gambar.',
            'images.*.mimes' => 'Format gambar harus JPG, PNG, GIF, atau WebP.',
            'images.*.max' => 'Ukuran setiap gambar maksimal 2MB.',
        ]);

        DB::beginTransaction();
        try {
            // Broadcast ke pemagang selalu wajib diisi tanggapan/laporan
            $requiresReport = true;

            $broadcast = Broadcast::create([
                'category' => 'scheduled_broadcast',
                'title' => $validated['title'],
                'message' => $validated['message'],
                'broadcast_type' => $validated['broadcast_type'],
                'scheduled_at' => $validated['scheduled_at'],
                'requires_report' => $requiresReport,
                'report_question' => 'Tuliskan tanggapan / jawaban Anda:',
                'created_by' => auth()->id(),
            ]);

            // Sync shift target (selalu diisi sesuai pilihan di sebelah waktu penjadwalan)
            $broadcast->shifts()->sync($validated['shifts'] ?? []);

            $type = $validated['broadcast_type'];
            $broadcast->divisions()->sync($type === 'division' ? ($validated['divisions'] ?? []) : []);
            $broadcast->users()->sync($type === 'specific' ? ($validated['users'] ?? []) : []);
            $broadcast->offices()->sync($type === 'office' ? ($validated['offices'] ?? []) : []);

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $file) {
                    $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('broadcast-image'), $filename);
                    $broadcast->images()->create(['image' => $filename]);
                }
            }

            DB::commit();

            \App\Helper\ActivityLogger::log('CREATE', 'Broadcast', "Admin menjadwalkan broadcast pesan baru: {$validated['title']}");

            return redirect()->route('admin.scheduled-broadcasts.index')
                ->with('success', !empty($validated['scheduled_at'])
                    ? 'Broadcast pesan berhasil dijadwalkan!'
                    : 'Broadcast pesan berhasil dikirim!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Mengambil daftar laporan atau jawaban pemagang untuk modal detail.
     */
    public function showReports(Broadcast $broadcast)
    {
        $broadcast->loadMissing('shifts:id,name');

        // Jika dibuka oleh admin, otomatis tandai semua chat pemagang di broadcast ini sebagai sudah dibaca
        if (auth()->check() && auth()->user()->role_id != 2) {
            $reportIds = $broadcast->reports()->pluck('id');
            if ($reportIds->isNotEmpty()) {
                \App\Models\BroadcastReportChat::whereIn('broadcast_report_id', $reportIds)
                    ->where('is_from_admin', false)
                    ->where('is_read', false)
                    ->update(['is_read' => true]);
            }
        }

        $reports = $broadcast->reports()
            ->with(['user.profile', 'chats.user.profile'])
            ->latest()
            ->get()
            ->map(function ($report) {
                return [
                    'id' => $report->id,
                    'user_id' => $report->user_id,
                    'name' => $report->user->profile->full_name ?? $report->user->name ?? $report->user->username ?? 'N/A',
                    'report' => trim($report->report ?? ''),
                    'submitted_at' => $report->created_at->format('d/m/Y H:i'),
                    'unread_intern_chats' => $report->chats->where('is_from_admin', false)->where('is_read', false)->count(),
                    'chats' => $report->chats->map(function ($c) {
                        return [
                            'id' => $c->id,
                            'message' => trim($c->message ?? ''),
                            'is_from_admin' => (bool) $c->is_from_admin,
                            'sender_name' => $c->is_from_admin 
                                ? ($c->user?->profile?->full_name ?? $c->user?->username ?? 'Admin')
                                : ($c->user?->profile?->full_name ?? $c->user?->username ?? 'Pemagang'),
                            'time' => $c->created_at ? $c->created_at->format('d/m H:i') : '-',
                        ];
                    })->values(),
                ];
            });

        return response()->json([
            'id' => $broadcast->id,
            'title' => $broadcast->title,
            'question' => $broadcast->report_question,
            'shifts' => $broadcast->shifts->pluck('name')->implode(', '),
            'reports' => $reports,
        ]);
    }

    /**
     * Mengambil riwayat chat follow-up spesifik untuk satu laporan pemagang.
     */
    public function getReportChats(\App\Models\BroadcastReport $report): JsonResponse
    {
        // Jika diakses oleh admin/mentor, tandai pesan pemagang sebagai sudah dibaca
        if (auth()->user()->role_id != 2) {
            \App\Models\BroadcastReportChat::where('broadcast_report_id', $report->id)
                ->where('is_from_admin', false)
                ->where('is_read', false)
                ->update(['is_read' => true]);
        } else {
            // Jika diakses pemagang sendiri, tandai pesan admin sebagai dibaca
            \App\Models\BroadcastReportChat::where('broadcast_report_id', $report->id)
                ->where('is_from_admin', true)
                ->where('is_read', false)
                ->update(['is_read' => true]);
        }

        $report->loadMissing(['user.profile', 'broadcast', 'chats.user.profile']);

        $chats = $report->chats->map(function ($c) {
            return [
                'id' => $c->id,
                'message' => $c->message,
                'is_from_admin' => (bool) $c->is_from_admin,
                'sender_name' => $c->is_from_admin 
                    ? ($c->user?->profile?->full_name ?? $c->user?->username ?? 'Admin')
                    : ($c->user?->profile?->full_name ?? $c->user?->username ?? 'Pemagang'),
                'time' => $c->created_at ? $c->created_at->format('d/m H:i') : '-',
            ];
        });

        return response()->json([
            'success' => true,
            'report_id' => $report->id,
            'intern_name' => $report->user?->profile?->full_name ?? $report->user?->username ?? 'Pemagang',
            'initial_report' => $report->report,
            'broadcast_title' => $report->broadcast?->title,
            'chats' => $chats,
        ]);
    }

    /**
     * Admin mengirim pesan follow-up ke pemagang terkait jawabannya.
     */
    public function sendFollowUp(Request $request, \App\Models\BroadcastReport $report): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ], [
            'message.required' => 'Pesan follow-up tidak boleh kosong.',
            'message.max' => 'Pesan maksimal 1000 karakter.',
        ]);

        $chat = \App\Models\BroadcastReportChat::create([
            'broadcast_report_id' => $report->id,
            'user_id' => auth()->id(),
            'message' => $request->input('message'),
            'is_from_admin' => true,
            'is_read' => false,
        ]);

        // Tandai seluruh pesan pemagang di laporan ini sebagai sudah dibaca
        \App\Models\BroadcastReportChat::where('broadcast_report_id', $report->id)
            ->where('is_from_admin', false)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $adminName = auth()->user()->profile?->full_name ?? auth()->user()->username ?? 'Admin';
        $internName = $report->user?->profile?->full_name ?? $report->user?->username ?? 'Pemagang';

        \App\Helper\ActivityLogger::log(
            'CREATE',
            'Broadcast Follow-up',
            "Admin {$adminName} mengirim pertanyaan follow-up ke {$internName} terkait laporan broadcast: {$report->broadcast?->title}"
        );

        return response()->json([
            'success' => true,
            'chat' => [
                'id' => $chat->id,
                'message' => $chat->message,
                'is_from_admin' => true,
                'sender_name' => $adminName,
                'time' => $chat->created_at->format('d/m H:i'),
            ],
        ]);
    }

    /**
     * Pemagang membalas pesan follow-up dari admin.
     */
    public function replyFollowUp(Request $request, \App\Models\BroadcastReport $report): JsonResponse
    {
        $user = auth()->user();
        if ($report->user_id !== $user->id && $user->role_id == 2) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $request->validate([
            'message' => 'required|string|max:1000',
        ], [
            'message.required' => 'Balasan tidak boleh kosong.',
            'message.max' => 'Balasan maksimal 1000 karakter.',
        ]);

        $chat = \App\Models\BroadcastReportChat::create([
            'broadcast_report_id' => $report->id,
            'user_id' => $user->id,
            'message' => $request->input('message'),
            'is_from_admin' => false,
            'is_read' => false,
        ]);

        // Tandai pesan admin sebelumnya sebagai sudah dibaca
        \App\Models\BroadcastReportChat::where('broadcast_report_id', $report->id)
            ->where('is_from_admin', true)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $internName = $user->profile?->full_name ?? $user->username ?? 'Pemagang';

        return response()->json([
            'success' => true,
            'chat' => [
                'id' => $chat->id,
                'message' => $chat->message,
                'is_from_admin' => false,
                'sender_name' => $internName,
                'time' => $chat->created_at->format('d/m H:i'),
            ],
        ]);
    }

    /**
     * Menghapus broadcast terjadwal beserta gambarnya.
     */
    public function destroy(Broadcast $broadcast)
    {
        DB::beginTransaction();
        try {
            $broadcast->loadMissing('images');
            foreach ($broadcast->images as $image) {
                $imagePath = public_path('broadcast-image/' . $image->image);
                if (File::exists($imagePath)) {
                    File::delete($imagePath);
                }
            }

            $title = $broadcast->title;
            $broadcast->delete();
            DB::commit();

            \App\Helper\ActivityLogger::log('DELETE', 'Broadcast', "Admin menghapus broadcast terjadwal: {$title}");

            return redirect()->route('admin.scheduled-broadcasts.index')
                ->with('success', 'Broadcast berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus broadcast: ' . $e->getMessage());
        }
    }
}
