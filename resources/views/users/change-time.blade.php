@extends('users.layouts.main')

@section('title', 'Data Hari Mengganti Jam')

@section('contents')
    <!-- Main Content -->
    <div class="min-h-screen bg-gray-50 py-6" x-data="{ showModal: false, selectedSchedule: null }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header Section -->
            <div class="mb-8">
                <div class="flex items-center mb-4">
                    <a href="{{ route('user.home') }}" class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-white shadow-sm border border-gray-200 text-gray-600 hover:text-gray-900 hover:bg-gray-50 transition-colors duration-200">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <div class="ml-4">
                        <h1 class="text-2xl font-bold text-gray-900">Data Hari Mengganti Jam</h1>
                        <p class="text-sm text-gray-600 mt-1">Kelola jadwal penggantian jam kerja Anda</p>
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
                                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-16">No</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal & Hari</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Status</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-40">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($schedules as $schedule)
                                <tr class="hover:bg-gray-50 transition-colors duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $loop->iteration }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ \Carbon\Carbon::parse($schedule->date)->locale('id')->isoFormat('dddd') }}</div>
                                        <div class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($schedule->date)->locale('id')->isoFormat('D MMMM YYYY') }}</div>
                                    </td>
                                    <td class="px-6 py-4"><div class="text-sm text-gray-900 max-w-xs">{{ $schedule->keterangan }}</div></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 border-red-200">
                                            <div class="w-1.5 h-1.5 rounded-full mr-1.5 bg-red-400"></div>
                                            {{ $schedule->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <button @click="selectedSchedule = {{ json_encode($schedule) }}; showModal = true" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            <i class="fas fa-eye mr-2"></i>
                                            Lihat Bukti
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-6 py-16 text-center text-gray-500">Tidak ada data kekurangan jam.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- MODAL -->
        <div x-show="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" style="display: none;">
            <div @click.away="showModal = false" class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4 transform transition-all" x-show="showModal" x-transition>
                <div class="px-6 py-4 border-b">
                    <h2 class="text-xl font-bold text-gray-800">Bukti Kekurangan Jam Kerja</h2>
                    <p class="text-sm text-gray-500" x-show="selectedSchedule" x-text="`Untuk tanggal: ${new Date(selectedSchedule.date).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })}`"></p>
                </div>
                
                <div class="p-6" x-show="selectedSchedule">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-gray-50 p-4 rounded-lg border">
                            <h3 class="font-semibold text-gray-700 mb-3 border-b pb-2">Jadwal Seharusnya</h3>
                            <div class="space-y-2 text-sm">
                                <p><strong>Shift:</strong> <span x-text="selectedSchedule.shift ? selectedSchedule.shift.name : '-'"></span></p>
                                <p><strong>Masuk:</strong> <span x-text="selectedSchedule.shift ? (selectedSchedule.shift.start_time ? selectedSchedule.shift.start_time.substring(0, 5) : '-') : '-'"></span></p>
                                <p><strong>Pulang:</strong> <span x-text="selectedSchedule.shift ? (selectedSchedule.shift.end_time ? selectedSchedule.shift.end_time.substring(0, 5) : '-') : '-'"></span></p>
                            </div>
                        </div>

                        <div class="bg-red-50 p-4 rounded-lg border border-red-200">
                            <h3 class="font-semibold text-red-700 mb-3 border-b border-red-200 pb-2">Absensi Aktual</h3>
                            <div class="space-y-2 text-sm" x-show="selectedSchedule.attendance">
                                <p><strong>Masuk:</strong> <span class="font-mono" x-text="selectedSchedule.attendance.start_time ? selectedSchedule.attendance.start_time.substring(0, 8) : 'Tidak Absen Masuk'"></span></p>
                                <p><strong>Pulang:</strong> <span class="font-mono" x-text="selectedSchedule.attendance.end_time ? selectedSchedule.attendance.end_time.substring(0, 8) : 'Belum Absen Pulang'"></span></p>
                                <hr class="my-2 border-red-200">
                                <p><strong>Total Jam Kerja:</strong> <span x-text="`${Math.floor((selectedSchedule.attendance.total_min || 0) / 60)} Jam ${ (selectedSchedule.attendance.total_min || 0) % 60} Menit`"></span></p>
                            </div>
                            <div class="text-sm text-red-600" x-show="!selectedSchedule.attendance">
                                Data absensi tidak ditemukan.
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
                        <p class="text-sm font-medium text-yellow-800">Kesimpulan</p>
                        <p class="text-lg font-bold text-yellow-900" x-text="selectedSchedule.keterangan"></p>
                    </div>
                </div>

                <div class="px-6 py-4 bg-gray-50 border-t flex justify-end">
                    <button type="button" @click="showModal = false" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection