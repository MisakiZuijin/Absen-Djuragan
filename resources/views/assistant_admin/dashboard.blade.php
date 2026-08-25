@extends('layouts.main')

@section('title', 'Dashboard Asisten Admin')

@section('contents')
    {{-- Memuat sidebar --}}
    @include($sidebarView)

    {{-- [PERBAIKAN] Wrapper untuk konten utama (Navbar + Main) --}}
    <div class="md:ml-64">
        
        {{-- Navbar --}}
        @include('layouts.navbar', ['user' => $user])

        {{-- [PERBAIKAN] Tag <main> sekarang berada di dalam wrapper dan tidak lagi memiliki class ml-64 --}}
        <main class="mt-24 p-6 bg-slate-50 min-h-screen">
            <!-- Header Section -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-6">
                        <div class="p-4 bg-white rounded-2xl shadow-sm border border-slate-200">
                            <i class="fa-solid fa-user-shield text-blue-600 text-2xl"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-slate-900">Dashboard Asisten Admin</h1>
                            <p class="text-slate-600 mt-1">Kelola siswa magang dan persetujuan aktivitas</p>
                        </div>
                    </div>
                    <div class="bg-white px-6 py-3 rounded-2xl shadow-sm border border-slate-200">
                        <div class="flex items-center gap-3">
                            <div class="w-3 h-3 bg-emerald-500 rounded-full animate-pulse"></div>
                            <span class="text-sm font-medium text-slate-700">Online</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alert Messages -->
            @if(session('success'))
                <div class="mb-8 bg-emerald-50 border border-emerald-200 rounded-2xl p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-emerald-100 rounded-full flex items-center justify-center">
                            <i class="fa-solid fa-check text-emerald-600"></i>
                        </div>
                        <p class="text-emerald-800 font-medium">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <!-- Siswa Menunggu Bantuan (Raise Hand) -->
                <a href="{{ route('assistant.raisehand.list') }}" class="block bg-white rounded-2xl shadow-sm border border-slate-200 hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-14 h-14 bg-amber-100 rounded-2xl flex items-center justify-center">
                                <i class="fa-solid fa-hand-paper text-amber-600 text-xl"></i>
                            </div>
                            <div class="text-right">
                                <div class="text-3xl font-bold text-slate-900">{{ isset($waitingStudents) ? $waitingStudents->count() : '0' }}</div>
                                <div class="text-sm text-slate-600">Siswa</div>
                            </div>
                        </div>
                        <h3 class="font-semibold text-slate-900 mb-2">Menunggu Bantuan</h3>
                        <div class="flex items-center gap-2 text-amber-600">
                            <i class="fa-solid fa-exclamation-triangle text-sm"></i>
                            <span class="text-sm font-medium">Perlu perhatian</span>
                        </div>
                    </div>
                </a>

                <!-- Total Log Menunggu Review -->
                <a href="{{ route('assistant.logactivity') }}" class="block bg-white rounded-2xl shadow-sm border border-slate-200 hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-14 h-14 bg-blue-100 rounded-2xl flex items-center justify-center">
                                <i class="fa-solid fa-clipboard-list text-blue-600 text-xl"></i>
                            </div>
                            <div class="text-right">
                                <div class="text-3xl font-bold text-slate-900">{{ $pendingLogsCount ?? '0' }}</div>
                                <div class="text-sm text-slate-600">Log</div>
                            </div>
                        </div>
                        <h3 class="font-semibold text-slate-900 mb-2">Menunggu Review Log</h3>
                        <div class="flex items-center gap-2 text-blue-600">
                            <i class="fa-solid fa-clock text-sm"></i>
                            <span class="text-sm font-medium">Dalam antrian</span>
                        </div>
                    </div>
                </a>
                
                <!-- Izin Keluar -->
                <a href="{{ route('assistant.izin.leave.index') }}" class="block bg-white rounded-2xl shadow-sm border border-slate-200 hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-14 h-14 bg-sky-100 rounded-2xl flex items-center justify-center">
                                <i class="fa-solid fa-door-open text-sky-600 text-xl"></i>
                            </div>
                            <div class="text-right">
                                <div class="text-3xl font-bold text-slate-900">{{ $pendingIzinKeluarCount ?? '0' }}</div>
                                <div class="text-sm text-slate-600">Siswa</div>
                            </div>
                        </div>
                        <h3 class="font-semibold text-slate-900 mb-2">Izin Keluar</h3>
                        <div class="flex items-center gap-2 text-sky-600">
                            <i class="fa-solid fa-person-running text-sm"></i>
                            <span class="text-sm font-medium">Sedang Berlangsung</span>
                        </div>
                    </div>
                </a>

                <!-- Izin Shalat -->
                <a href="{{ route('assistant.izin.prayer.index') }}" class="block bg-white rounded-2xl shadow-sm border border-slate-200 hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-14 h-14 bg-teal-100 rounded-2xl flex items-center justify-center">
                                <i class="fa-solid fa-mosque text-teal-600 text-xl"></i>
                            </div>
                            <div class="text-right">
                                <div class="text-3xl font-bold text-slate-900">{{ $pendingIzinShalatCount ?? '0' }}</div>
                                <div class="text-sm text-slate-600">Siswa</div>
                            </div>
                        </div>
                        <h3 class="font-semibold text-slate-900 mb-2">Izin Shalat</h3>
                        <div class="flex items-center gap-2 text-teal-600">
                             <i class="fa-solid fa-person-praying text-sm"></i>
                            <span class="text-sm font-medium">Sedang Berlangsung</span>
                        </div>
                    </div>
                </a>

                <!-- Izin Toilet -->
                <a href="{{ route('assistant.izin.toilet.index') }}" class="block bg-white rounded-2xl shadow-sm border border-slate-200 hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-14 h-14 bg-indigo-100 rounded-2xl flex items-center justify-center">
                                <i class="fa-solid fa-restroom text-indigo-600 text-xl"></i>
                            </div>
                            <div class="text-right">
                                <div class="text-3xl font-bold text-slate-900">{{ $pendingIzinToiletCount ?? '0' }}</div>
                                <div class="text-sm text-slate-600">Siswa</div>
                            </div>
                        </div>
                        <h3 class="font-semibold text-slate-900 mb-2">Izin Toilet</h3>
                        <div class="flex items-center gap-2 text-indigo-600">
                            <i class="fa-solid fa-clock text-sm"></i>
                            <span class="text-sm font-medium">Sedang Berlangsung</span>
                        </div>
                    </div>
                </a>
                
                <!-- Log Disetujui Hari Ini -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-14 h-14 bg-emerald-100 rounded-2xl flex items-center justify-center">
                                <i class="fa-solid fa-check-circle text-emerald-600 text-xl"></i>
                            </div>
                            <div class="text-right">
                                <div class="text-3xl font-bold text-slate-900">{{ $approvedLogsTodayCount ?? '0' }}</div>
                                <div class="text-sm text-slate-600">Log</div>
                            </div>
                        </div>
                        <h3 class="font-semibold text-slate-900 mb-2">Log Disetujui Hari Ini</h3>
                        <div class="flex items-center gap-2 text-emerald-600">
                            <i class="fa-solid fa-check text-sm"></i>
                            <span class="text-sm font-medium">Target tercapai</span>
                        </div>
                    </div>
                </div>
            </div>

        {{-- Sisa kode di bawah ini tidak diubah dan sudah benar --}}
        <!-- Main Content Cards -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Raise Hand Management Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden hover:shadow-lg transition-all duration-300">
                <div class="p-6 border-b border-slate-100">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center">
                            <i class="fa-solid fa-users-gear text-amber-600 text-xl"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-slate-900">Manajemen Raise Hand</h2>
                            <p class="text-slate-600 text-sm">Berikan tugas kepada siswa yang siap</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="mb-6 bg-amber-50 border border-amber-100 rounded-xl p-4">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-users text-amber-600"></i>
                            <p class="text-slate-700">
                                <span class="font-bold text-xl text-amber-600">{{ isset($waitingStudents) ? $waitingStudents->count() : '0' }}</span>
                                <span class="ml-1">siswa mengangkat tangan</span>
                            </p>
                        </div>
                    </div>
                    <p class="text-slate-600 mb-6 leading-relaxed">
                        Siswa yang mengangkat tangan siap menerima tugas atau proyek baru. Berikan mereka aktivitas yang sesuai dengan kemampuan.
                    </p>
                    <a href="{{ route('assistant.raisehand.list') }}"
                       class="w-full bg-amber-600 hover:bg-amber-700 text-white font-semibold py-4 px-6 rounded-xl shadow-sm transition-all duration-200 hover:shadow-md flex items-center justify-center group">
                        <i class="fa-solid fa-eye mr-3 group-hover:scale-110 transition-transform"></i>
                        Lihat Daftar Siswa
                    </a>
                </div>
            </div>
            <!-- Log Activity Approval Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden hover:shadow-lg transition-all duration-300">
                <div class="p-6 border-b border-slate-100">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                            <i class="fa-solid fa-clipboard-check text-blue-600 text-xl"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-slate-900">Persetujuan Log Aktivitas</h2>
                            <p class="text-slate-600 text-sm">Kelola laporan aktivitas siswa</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="mb-6 bg-blue-50 border border-blue-100 rounded-xl p-4">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-file-lines text-blue-600"></i>
                            <p class="text-slate-700">
                                <span class="font-bold text-xl text-blue-600">{{ $pendingLogsCount ?? '0' }}</span>
                                <span class="ml-1">laporan menunggu persetujuan</span>
                            </p>
                        </div>
                    </div>
                    <p class="text-slate-600 mb-6 leading-relaxed">
                        Tinjau dan setujui laporan aktivitas harian dari para siswa magang untuk memastikan kualitas dan kesesuaian standar.
                    </p>
                    <a href="{{ route('assistant.logactivity') }}"
                       class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-4 px-6 rounded-xl shadow-sm transition-all duration-200 hover:shadow-md flex items-center justify-center group">
                        <i class="fa-solid fa-clipboard-list mr-3 group-hover:scale-110 transition-transform"></i>
                        Buka Halaman Log Aktivitas
                    </a>
                </div>
            </div>
        </div>

        {{-- Sisa kode di bawah ini tidak perlu diubah --}}
        <!-- Recent Activity Section -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-8">
            <div class="p-6 border-b border-slate-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-slate-100 rounded-xl flex items-center justify-center">
                            <i class="fa-solid fa-clock-rotate-left text-slate-600 text-xl"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-slate-900">Aktivitas Terbaru</h2>
                            <p class="text-slate-600 text-sm">5 log aktivitas terakhir yang masuk</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 text-slate-500 text-sm">
                        <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
                        <span>Real-time</span>
                    </div>
                </div>
            </div>

            <div class="p-6">
                @if(isset($recentActivities) && $recentActivities->count() > 0)
                    <div class="space-y-4">
                        @foreach($recentActivities as $activity)
                            <div class="flex items-center gap-4 p-4 bg-slate-50 rounded-xl hover:bg-slate-100 transition-colors duration-200 border border-slate-100">
                                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                                    <span class="text-blue-600 font-bold text-sm">
                                        {{ strtoupper(substr($activity->user->name ?? 'U', 0, 2)) }}
                                    </span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-semibold text-slate-900 mb-1">{{ $activity->user->name ?? 'User' }}</div>
                                    <div class="text-sm text-slate-600 truncate">{{ Str::limit($activity->description, 80) }}</div>
                                </div>
                                <div class="flex flex-col items-end gap-2">
                                    <div class="text-xs text-slate-500">
                                        {{ \Carbon\Carbon::parse($activity->date)->diffForHumans() }}
                                    </div>
                                    <span class="px-3 py-1 bg-amber-100 text-amber-700 text-xs rounded-full font-medium">
                                        Pending
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fa-solid fa-inbox text-slate-400 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900 mb-2">Belum Ada Aktivitas</h3>
                        <p class="text-slate-600 max-w-sm mx-auto">
                            Aktivitas terbaru dari siswa akan muncul di sini ketika mereka mengirimkan laporan.
                        </p>
                    </div>
                @endif
            </div>
        </div>

        <!-- List Siswa Raise Hand -->
        @if(isset($waitingStudents) && $waitingStudents->count() > 0)
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-6 border-b border-slate-100">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-hand-paper text-amber-600 text-lg"></i>
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900">Siswa Menunggu Bantuan</h3>
                                <p class="text-sm text-slate-600">5 siswa terakhir yang mengangkat tangan</p>
                            </div>
                        </div>
                        <a href="{{ route('assistant.raisehand.list') }}" 
                           class="text-amber-600 hover:text-amber-700 text-sm font-semibold flex items-center gap-2 hover:gap-3 transition-all">
                            Lihat Semua
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="text-left py-4 px-6 text-xs font-semibold text-slate-600 uppercase tracking-wider">Nama Siswa</th>
                                <th class="text-left py-4 px-6 text-xs font-semibold text-slate-600 uppercase tracking-wider">Sekolah</th>
                                <th class="text-left py-4 px-6 text-xs font-semibold text-slate-600 uppercase tracking-wider">Project</th>
                                <th class="text-left py-4 px-6 text-xs font-semibold text-slate-600 uppercase tracking-wider">Waktu</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($waitingStudents->take(5) as $handRaise)
                                <tr class="hover:bg-slate-50 transition-colors duration-200">
                                    <td class="py-4 px-6 font-medium text-slate-900">
                                        {{ $handRaise->user->profile->full_name ?? $handRaise->user->username ?? '-' }}
                                    </td>
                                    <td class="py-4 px-6 text-slate-600">
                                        {{ $handRaise->user->intern->school->name ?? '-' }}
                                    </td>
                                    <td class="py-4 px-6 text-slate-600">
                                        {{ $handRaise->user->intern->detailProject->last()?->project->nameProject->name ?? '-' }}
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="text-sm text-slate-900">
                                            {{ $handRaise->updated_at ? $handRaise->updated_at->format('d M Y, H:i') : '-' }}
                                        </div>
                                        <div class="text-xs text-slate-500">
                                            {{ $handRaise->updated_at ? $handRaise->updated_at->diffForHumans() : '' }}
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </main>

    <style>
        /* Modern scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #f8fafc;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Smooth transitions */
        .transition-all {
            transition-property: all;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
            transition-duration: 300ms;
        }

        /* Hover effects */
        .hover\:shadow-lg:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        /* Animation utilities */
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .animate-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .ml-64 {
                margin-left: 0;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Refresh halaman setiap 5 menit untuk update data terbaru
            setTimeout(function() {
                location.reload();
            }, 300000);

            // Smooth hover effects untuk cards
            document.querySelectorAll('.hover\\:shadow-lg').forEach(element => {
                element.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-2px)';
                });

                element.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
        });

        // Toast notification function
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `fixed top-4 right-4 px-6 py-3 rounded-xl shadow-lg z-50 transition-all duration-300 ${
                type === 'success' ? 'bg-emerald-600 text-white' : 'bg-red-600 text-white'
            }`;
            toast.textContent = message;

            document.body.appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100%)';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
    </script>
@endsection