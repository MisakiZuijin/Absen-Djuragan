<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Division;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class SettingMeetController extends Controller
{
    /**
     * Tampilkan halaman pengelolaan link Google Meet presentasi per divisi.
     */
    public function index(): View
    {
        $divisions = Division::orderBy('name', 'asc')->get();

        return view('admin.pengaturan-meet', compact('divisions'));
    }

    /**
     * Terapkan link Google Meet ke divisi terpilih (bisa satu atau banyak sekaligus via checklist).
     */
    public function assignMeetLink(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'meet_url' => 'required|url|max:500',
            'division_ids' => 'required|array|min:1',
            'division_ids.*' => 'exists:divisions,id',
        ], [
            'meet_url.required' => 'Link Google Meet wajib diisi.',
            'meet_url.url' => 'Format link Google Meet tidak valid (harus diawali http:// atau https://).',
            'division_ids.required' => 'Pilih minimal satu divisi melalui checklist.',
            'division_ids.min' => 'Pilih minimal satu divisi melalui checklist.',
            'division_ids.*.exists' => 'Divisi yang dipilih tidak valid.',
        ]);

        $meetUrl = trim($validated['meet_url']);
        $divisionIds = $validated['division_ids'];

        Division::whereIn('id', $divisionIds)->update([
            'meet_url' => $meetUrl,
        ]);

        return redirect()->route('admin.pengaturan.meet')->with('success', 'Link Google Meet berhasil diperbarui untuk ' . count($divisionIds) . ' divisi terpilih.');
    }

    /**
     * Hapus / kosongkan link Google Meet dari suatu divisi tertentu.
     */
    public function clearDivisionMeet(int $id): RedirectResponse
    {
        $division = Division::findOrFail($id);
        $division->update([
            'meet_url' => null,
        ]);

        return redirect()->route('admin.pengaturan.meet')->with('success', 'Link Google Meet untuk divisi "' . $division->name . '" berhasil dihapus.');
    }
}
