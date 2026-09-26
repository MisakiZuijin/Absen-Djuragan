@extends('users.layouts.main')

@section('title', 'Data Hari Mengganti Jam')

@section('contents')
    <!-- Main Content -->
    <div class="min-h-screen bg-gray-50 py-4 sm:py-6" x-data="{ showModal: false, selectedSchedule: null }">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
            <!-- Header Section -->
            <div class="mb-6 sm:mb-8">
                <div class="flex items-center mb-4">
                    <a href="{{ route('user.home') }}" class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-white shadow-2xs border border-gray-200 text-gray-600 hover:text-gray-900 hover:bg-gray-50 transition">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <div class="ml-3 sm:ml-4 min-w-0">
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-900 truncate">Data Hari Mengganti Jam</h1>
                        <p class="text-xs sm:text-sm text-gray-600 mt-0.5 truncate">Kelola jadwal penggantian jam kerja Anda</p>
                    </div>
                </div>
            </div>

            <!-- Table Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900">Daftar Jadwal Penggantian</h2>
                        <div class="text-sm text-gray-500">Total: {{ $schedules->count() }} jadwal</div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-12">No</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal & Hari</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori Ganti Jam</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Status</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-36">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($schedules as $schedule)
                                <tr class="hover:bg-gray-50 transition-colors duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $loop->iteration }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ \Carbon\Carbon::parse($schedule->date)->locale('id')->isoFormat('dddd') }}</div>
                                        <div class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($schedule->date)->locale('id')->isoFormat('D MMMM YYYY') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold border {{ $schedule->kategori_badge ?? 'bg-gray-100 text-gray-800 border-gray-200' }}">
                                            {{ $schedule->kategori ?? 'Kekurangan Jam' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $schedule->keterangan }}</div>
                                        @if($schedule->permitReason && !empty($schedule->permitReason->description))
                                            <div class="text-xs text-gray-500 mt-1 flex items-center gap-1">
                                                <i class="fa-solid fa-note-sticky text-amber-500 text-[10px]"></i>
                                                <span>Alasan: "{{ $schedule->permitReason->description }}"</span>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 border-red-200">
                                            <div class="w-1.5 h-1.5 rounded-full mr-1.5 bg-red-400"></div>
                                            {{ $schedule->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <button @click="selectedSchedule = {{ json_encode($schedule) }}; showModal = true" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs leading-4 font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm transition">
                                            <i class="fas fa-eye mr-1.5"></i>
                                            Lihat Bukti
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-6 py-16 text-center text-gray-500">Tidak ada data kekurangan jam kerja.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- MODAL -->
        <div x-show="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-3 sm:p-4" style="display: none;">
            <div @click.away="showModal = false" class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl mx-auto overflow-hidden transform transition-all border border-gray-100 max-h-[90vh] flex flex-col" x-show="showModal" x-transition>
                <template x-if="selectedSchedule">
                    <div class="flex flex-col flex-1 overflow-hidden">
                        <div class="px-4 sm:px-6 py-3.5 sm:py-4 border-b flex justify-between items-center bg-gray-50 shrink-0">
                            <div class="min-w-0 pr-2">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h2 class="text-base sm:text-lg font-bold text-gray-800">Bukti Kekurangan Jam Kerja</h2>
                                    <template x-if="selectedSchedule?.kategori">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold border"
                                              :class="selectedSchedule?.kategori_badge || 'bg-gray-100 text-gray-800 border-gray-200'"
                                              x-text="selectedSchedule?.kategori"></span>
                                    </template>
                                </div>
                                <p class="text-xs text-gray-500 mt-0.5 truncate" x-text="selectedSchedule?.date ? `Untuk tanggal: ${new Date(selectedSchedule.date).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })}` : ''"></p>
                            </div>
                            <button type="button" @click="showModal = false" class="text-gray-400 hover:text-gray-600 text-2xl font-bold leading-none">&times;</button>
                        </div>
                        
                        <div class="p-4 sm:p-6 overflow-y-auto flex-1">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                                <div class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                                    <h3 class="font-semibold text-gray-700 mb-3 border-b border-gray-200 pb-2 flex items-center gap-1.5 text-xs sm:text-sm">
                                        <i class="fa-solid fa-calendar-check text-blue-600"></i> Jadwal Seharusnya
                                    </h3>
                                    <div class="space-y-2 text-xs sm:text-sm">
                                        <p><strong>Shift:</strong> <span x-text="selectedSchedule?.shift?.name || '-'"></span></p>
                                        <p><strong>Masuk:</strong> <span x-text="selectedSchedule?.shift?.start_time ? selectedSchedule.shift.start_time.substring(0, 5) : '-'"></span></p>
                                        <p><strong>Pulang:</strong> <span x-text="selectedSchedule?.shift?.end_time ? selectedSchedule.shift.end_time.substring(0, 5) : '-'"></span></p>
                                        <p><strong>Durasi Shift:</strong> <span x-text="selectedSchedule?.shift ? `${Math.floor((selectedSchedule.shift.total_time_in_minute || 0) / 60)} Jam ${(selectedSchedule.shift.total_time_in_minute || 0) % 60} Menit` : '-'"></span></p>
                                    </div>
                                </div>

                                <div class="bg-red-50 p-4 rounded-xl border border-red-200">
                                    <h3 class="font-semibold text-red-700 mb-3 border-b border-red-200 pb-2 flex items-center gap-1.5 text-xs sm:text-sm">
                                        <i class="fa-solid fa-clock text-rose-600"></i> Absensi Aktual / Izin
                                    </h3>
                                    <template x-if="selectedSchedule?.attendance?.start_time">
                                        <div class="space-y-2 text-xs sm:text-sm">
                                            <p><strong>Masuk:</strong> <span class="font-mono" x-text="selectedSchedule.attendance.start_time.substring(0, 8)"></span></p>
                                            <p><strong>Pulang:</strong> <span class="font-mono" x-text="selectedSchedule.attendance.end_time ? selectedSchedule.attendance.end_time.substring(0, 8) : 'Belum Absen Pulang'"></span></p>
                                            <hr class="my-2 border-red-200">
                                            <p><strong>Total Jam Kerja:</strong> <span x-text="`${Math.floor((selectedSchedule.attendance.total_min || 0) / 60)} Jam ${ (selectedSchedule.attendance.total_min || 0) % 60} Menit`"></span></p>
                                        </div>
                                    </template>
                                    <template x-if="!selectedSchedule?.attendance?.start_time">
                                        <div class="space-y-2 text-xs sm:text-sm text-slate-700">
                                            <template x-if="selectedSchedule?.permit_reason">
                                                <div class="space-y-1.5">
                                                    <p class="text-amber-700 font-semibold"><i class="fa-solid fa-clipboard-question mr-1"></i> Pengajuan Izin</p>
                                                    <p><strong>Alasan:</strong> <span x-text="selectedSchedule.permit_reason.description || '-'"></span></p>
                                                    <template x-if="selectedSchedule.permit_reason.proof_url">
                                                        <div>
                                                            <p><a :href="selectedSchedule.permit_reason.proof_url" target="_blank" class="text-blue-600 underline font-medium inline-flex items-center gap-1"><i class="fa-solid fa-paperclip"></i> Lihat Lampiran Bukti</a></p>
                                                            <template x-if="selectedSchedule.isChangeSchedule == 2">
                                                                <p class="text-xs text-amber-700 mt-1.5 bg-amber-50 p-2 rounded-md border border-amber-200 leading-relaxed">
                                                                    <i class="fa-solid fa-circle-exclamation mr-1"></i> <strong>Catatan:</strong> Lampiran bukti dinyatakan tidak valid/kosong oleh admin, sehingga ditetapkan sebagai <strong>Wajib Ganti Jam Tanpa Bukti Surat</strong>.
                                                                </p>
                                                            </template>
                                                        </div>
                                                    </template>
                                                    <template x-if="!selectedSchedule.permit_reason.proof_url">
                                                        <p class="text-xs text-rose-600 italic"><i class="fa-solid fa-circle-exclamation mr-1"></i> Tanpa surat dokter / bukti lampiran</p>
                                                    </template>
                                                </div>
                                            </template>
                                            <template x-if="!selectedSchedule?.permit_reason">
                                                <p class="text-rose-600 font-medium"><i class="fa-solid fa-circle-xmark mr-1"></i> Tidak ada data kehadiran (Alpha / tidak hadir).</p>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            
                            <div class="mt-4 sm:mt-6 bg-yellow-50 border border-yellow-200 rounded-xl p-3.5 sm:p-4 text-center">
                                <p class="text-[11px] font-semibold text-yellow-800 uppercase tracking-wider">Kesimpulan Status</p>
                                <p class="text-sm sm:text-base font-bold text-yellow-900 mt-1" x-text="selectedSchedule?.keterangan || ''"></p>
                            </div>
                        </div>

                        <div class="px-4 sm:px-6 py-3 bg-gray-50 border-t flex justify-end shrink-0">
                            <button type="button" @click="showModal = false" class="px-4 py-2 bg-gray-800 text-white rounded-xl hover:bg-gray-700 text-xs font-semibold transition">Tutup</button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
@endsection