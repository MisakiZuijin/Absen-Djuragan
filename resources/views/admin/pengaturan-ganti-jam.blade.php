@extends('layouts.main')

@section('title', 'Pengaturan Ganti Jam')

@section('contents')
    @include('layouts.sidebar-pengaturan')

    <!-- Main Content -->
    <main class="ml-0 lg:ml-[32rem] mt-2 lg:mt-20 p-3 sm:p-6 min-w-0 max-w-full overflow-x-hidden">
        <div class="w-full max-w-full min-w-0 space-y-6">

            <!-- Header Section -->
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-xs">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center shrink-0 border border-orange-100 shadow-2xs">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 mb-0.5">Pengaturan Sesi Ganti Jam</h1>
                        <p class="text-gray-500 text-xs sm:text-sm">
                            Atur kebijakan hari, shift kerja, dan lokasi kantor yang disediakan untuk pemagang yang melakukan presensi ganti jam.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Notification Alert -->
            @if (session('success'))
                <div class="bg-emerald-50 border border-emerald-300 text-emerald-800 p-4 rounded-xl shadow-xs flex items-center gap-2.5 text-xs sm:text-sm" role="alert">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <span class="font-bold">Sukses:</span> {{ session('success') }}
                    </div>
                </div>
            @endif

            <!-- Form Pengaturan Ganti Jam -->
            <div class="bg-white rounded-2xl shadow-xs border border-gray-100 overflow-hidden">
                <form action="{{ route('admin.pengaturan.ganti-jam.update') }}" method="POST">
                    @csrf
                    <div class="p-5 sm:p-6 space-y-6">

                        <!-- SECTION 1: PEMBATASAN HARI KERJA / LIBUR -->
                        <div class="p-5 rounded-2xl border border-gray-200/80 bg-gray-50/50 space-y-3.5">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                        </svg>
                                    </div>
                                    <h3 class="text-sm sm:text-base font-bold text-gray-900">Jadwal Hari Ganti Jam</h3>
                                </div>
                                <p class="text-xs text-gray-500 max-w-xl leading-relaxed">
                                    Atur waktu akses tombol dan formulir ganti jam bagi pemagang.
                                </p>
                            </div>

                            <!-- Pilihan Visibilitas Hari (Jelas & Tegas) -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                                <!-- Opsi 1: Setiap Hari -->
                                <label class="schedule-visibility-btn flex items-center gap-3 p-3.5 rounded-xl border bg-white cursor-pointer transition select-none {{ !$setting->restrict_to_holidays ? 'border-blue-600 ring-1 ring-blue-600' : 'border-gray-200 hover:border-gray-300' }}">
                                    <input type="radio" name="restrict_to_holidays" value="0" class="text-blue-600 focus:ring-blue-500 h-4 w-4 shrink-0" {{ !$setting->restrict_to_holidays ? 'checked' : '' }} onchange="updateVisibilityCards(this)">
                                    <div class="min-w-0">
                                        <div class="font-bold text-xs sm:text-sm text-gray-900">Setiap Hari</div>
                                        <p class="text-[11px] text-gray-500 mt-0.5">Dapat diakses kapan saja.</p>
                                    </div>
                                </label>

                                <!-- Opsi 2: Hanya Minggu & Tanggal Merah -->
                                <label class="schedule-visibility-btn flex items-center gap-3 p-3.5 rounded-xl border bg-white cursor-pointer transition select-none {{ $setting->restrict_to_holidays ? 'border-blue-600 ring-1 ring-blue-600' : 'border-gray-200 hover:border-gray-300' }}">
                                    <input type="radio" name="restrict_to_holidays" value="1" class="text-blue-600 focus:ring-blue-500 h-4 w-4 shrink-0" {{ $setting->restrict_to_holidays ? 'checked' : '' }} onchange="updateVisibilityCards(this)">
                                    <div class="min-w-0">
                                        <div class="font-bold text-xs sm:text-sm text-gray-900">Hanya Hari Libur</div>
                                        <p class="text-[11px] text-gray-500 mt-0.5">Hanya muncul pada hari Minggu atau tanggal merah.</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- SECTION 2: CHECKLIST SHIFT YANG DISEDIAKAN -->
                        <div class="space-y-3">
                            <div class="border-b border-gray-100 pb-2">
                                <h3 class="text-sm sm:text-base font-bold text-gray-900 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Pilihan Shift untuk Ganti Jam</span>
                                </h3>
                                <p class="text-xs text-gray-500 mt-0.5">Centang shift kerja mana saja yang boleh dipilih oleh pemagang saat mengganti jam.</p>
                            </div>

                            @php
                                $allowedShifts = $setting->allowed_shift_ids ?? [];
                            @endphp

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                @forelse($shifts as $shift)
                                    @php
                                        $isChecked = empty($allowedShifts) || in_array($shift->id, $allowedShifts);
                                    @endphp
                                    <label class="relative p-4 rounded-xl border {{ $isChecked ? 'border-orange-300 bg-orange-50/30' : 'border-gray-200 bg-white' }} flex items-start gap-3 cursor-pointer hover:border-orange-400 transition shadow-2xs">
                                        <input type="checkbox" name="allowed_shift_ids[]" value="{{ $shift->id }}" class="mt-1 w-4 h-4 text-orange-600 rounded border-gray-300 focus:ring-orange-500" {{ $isChecked ? 'checked' : '' }}>
                                        <div class="min-w-0 flex-1">
                                            <div class="font-bold text-xs sm:text-sm text-gray-900 truncate">{{ $shift->name }}</div>
                                            <div class="text-xs text-gray-500 font-mono mt-0.5">
                                                {{ substr($shift->start_time, 0, 5) }} - {{ substr($shift->end_time, 0, 5) }}
                                            </div>
                                            <div class="text-[11px] text-gray-400 mt-1">
                                                Durasi: {{ floor(($shift->total_time_in_minute ?? 0) / 60) }} Jam {{ ($shift->total_time_in_minute ?? 0) % 60 }} Menit
                                            </div>
                                        </div>
                                    </label>
                                @empty
                                    <div class="col-span-full p-4 text-center text-xs text-gray-400 bg-gray-50 rounded-xl border border-dashed">
                                        Belum ada data shift kerja. Tambahkan shift di menu Manage Shift terlebih dahulu.
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <!-- SECTION 3: CHECKLIST KANTOR YANG DISEDIAKAN -->
                        <div class="space-y-3 pt-3 border-t border-gray-100">
                            <div class="border-b border-gray-100 pb-2">
                                <h3 class="text-sm sm:text-base font-bold text-gray-900 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.75a1.5 1.5 0 011.5-1.5h1.5a1.5 1.5 0 011.5 1.5V21m6-9h.75m-.75 3h.75m-.75 3h.75" />
                                    </svg>
                                    <span>Pilihan Kantor / Lokasi Ganti Jam</span>
                                </h3>
                                <p class="text-xs text-gray-500 mt-0.5">Centang kantor mana saja yang dibuka untuk sesi ganti jam pemagang.</p>
                            </div>

                            @php
                                $allowedOffices = $setting->allowed_office_ids ?? [];
                                $defaultOfficeId = $setting->default_office_id ?? 1;
                            @endphp

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                @forelse($offices as $office)
                                    @php
                                        $isOfficeChecked = empty($allowedOffices) || in_array($office->id, $allowedOffices);
                                        $isDefaultOffice = $defaultOfficeId == $office->id;
                                    @endphp
                                    <div class="p-4 rounded-xl border {{ $isOfficeChecked ? 'border-blue-300 bg-blue-50/30' : 'border-gray-200 bg-white' }} space-y-2 shadow-2xs">
                                        <label class="flex items-center gap-2.5 cursor-pointer">
                                            <input type="checkbox" name="allowed_office_ids[]" value="{{ $office->id }}" class="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500" {{ $isOfficeChecked ? 'checked' : '' }}>
                                            <span class="font-bold text-xs sm:text-sm text-gray-900 capitalize">{{ $office->name }}</span>
                                        </label>
                                        <div class="pt-2 border-t border-gray-200/60 flex items-center justify-between text-xs">
                                            <label class="flex items-center gap-1.5 cursor-pointer text-gray-600 hover:text-blue-700">
                                                <input type="radio" name="default_office_id" value="{{ $office->id }}" class="text-blue-600 border-gray-300 focus:ring-blue-500" {{ $isDefaultOffice ? 'checked' : '' }}>
                                                <span class="text-[11px] font-semibold">Jadikan Default</span>
                                            </label>
                                            @if($isDefaultOffice)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800">Default Utama</span>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-span-full p-4 text-center text-xs text-gray-400 bg-gray-50 rounded-xl border border-dashed">
                                        Belum ada data kantor. Tambahkan kantor di menu Manage Kantor terlebih dahulu.
                                    </div>
                                @endforelse
                            </div>
                        <!-- SECTION 4: CATATAN / PENGUMUMAN OTOMATIS UNTUK PEMAGANG -->
                        <div class="space-y-3 pt-3 border-t border-gray-100">
                            <div class="border-b border-gray-100 pb-2">
                                <h3 class="text-sm sm:text-base font-bold text-gray-900 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" />
                                    </svg>
                                    <span>Catatan & Panduan untuk Pemagang</span>
                                </h3>
                                <p class="text-xs text-gray-500 mt-0.5">Teks catatan / instruksi yang akan otomatis tampil pada kartu ganti jam dan form pendaftaran pemagang.</p>
                            </div>

                            <div class="space-y-2">
                                <textarea name="intern_notice_text" rows="4" 
                                    placeholder="Contoh: Pastikan konfirmasi kedatangan ke admin, kenakan seragam rapi, dan lakukan presensi masuk tepat waktu."
                                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 text-xs sm:text-sm text-gray-800 placeholder-gray-400 resize-y transition">{{ old('intern_notice_text', $setting->intern_notice_text) }}</textarea>
                                <p class="text-[11px] text-gray-400">Kosongkan jika tidak ada catatan khusus yang ingin disampaikan.</p>
                            </div>
                        </div>

                    </div>

                    <!-- Submit Button Footer -->
                    <div class="px-5 sm:px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
                        <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs sm:text-sm font-bold shadow transition flex items-center gap-2 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 3.75V6.75A2.25 2.25 0 0114.25 9H9.75A2.25 2.25 0 017.5 6.75V3.75m9 0H7.5m9 0A2.25 2.25 0 0118.75 6v12A2.25 2.25 0 0116.5 20.25H7.5A2.25 2.25 0 015.25 18V6A2.25 2.25 0 017.5 3.75" />
                            </svg>
                            <span>Simpan Pengaturan Ganti Jam</span>
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </main>

    <script>
        function updateVisibilityCards(radio) {
            document.querySelectorAll('.schedule-visibility-btn').forEach(function(el) {
                el.classList.remove('border-blue-600', 'ring-1', 'ring-blue-600');
                el.classList.add('border-gray-200');
            });
            const parent = radio.closest('.schedule-visibility-btn');
            if (parent) {
                parent.classList.remove('border-gray-200');
                parent.classList.add('border-blue-600', 'ring-1', 'ring-blue-600');
            }
        }
    </script>
@endsection
