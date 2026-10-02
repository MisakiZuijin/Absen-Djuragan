@extends('users.layouts.main')

@section('title', 'Tugas & Akun Divisi')

@section('contents')
@php
$userDivId = (int)($user->intern->division_id ?? 0);
$userDivName = strtolower($user->intern->division->name ?? '');

$isUserProg = ($userDivId === 4 || str_contains($userDivName, 'programmer') || str_contains($userDivName, 'program'));
$isUserUiUx = ($userDivId === 1 || str_contains($userDivName, 'ui/ux') || str_contains($userDivName, 'ui / ux') || (str_contains($userDivName, 'ui') && str_contains($userDivName, 'ux')));
$isUserSosmed = in_array($userDivId, [2, 3, 5, 6, 8, 9, 11, 12, 16]) ||
str_contains($userDivName, 'social') ||
str_contains($userDivName, 'tiktok') ||
str_contains($userDivName, 'marketing') ||
str_contains($userDivName, 'marcom') ||
str_contains($userDivName, 'content') ||
str_contains($userDivName, 'talent') ||
str_contains($userDivName, 'presenter');
$isUserGeneral = !$isUserProg && !$isUserUiUx && !$isUserSosmed;
@endphp

<div class="max-w-6xl mx-auto px-3 sm:px-6 py-4 sm:py-8 space-y-4 sm:space-y-6">

    <!-- Top Navigation / Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 sm:gap-4">
        <a href="{{ route('user.home') }}"
            class="inline-flex items-center text-xs sm:text-sm font-semibold text-gray-600 hover:text-blue-600 transition-colors w-fit">
            <svg class="w-4 h-4 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali ke Beranda</span>
        </a>

        <div class="flex items-center gap-1.5 sm:gap-2 flex-nowrap overflow-x-auto pb-0.5 max-w-full">
            <span class="inline-flex items-center gap-1 sm:gap-1.5 px-2.5 sm:px-3 py-1 rounded-full text-xs sm:text-sm font-bold bg-indigo-100 text-indigo-800 border border-indigo-200 shadow-2xs whitespace-nowrap shrink-0">
                <svg class="w-3.5 h-3.5 text-indigo-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <span>Divisi: {{ $user->intern->division->name ?? 'Umum' }}</span>
            </span>
            @if($user->intern?->school)
            <span class="inline-flex items-center gap-1 sm:gap-1.5 px-2.5 sm:px-3 py-1 rounded-full text-xs sm:text-sm font-medium bg-slate-100 text-slate-700 border border-slate-200 whitespace-nowrap shrink-0">
                <svg class="w-3.5 h-3.5 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5" />
                </svg>
                <span>{{ $user->intern->school->name }}</span>
            </span>
            @endif
        </div>
    </div>

    <!-- Banner Card -->
    <div class="bg-gradient-to-r from-gray-900 via-gray-800 to-gray-700 text-white rounded-2xl sm:rounded-3xl p-4 sm:p-6 md:p-8 shadow-xl relative overflow-hidden">
        <div class="absolute -right-12 -bottom-12 w-64 h-64 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-5 sm:gap-6">
            <div class="space-y-1.5 sm:space-y-2">
                <div class="flex items-center gap-2 text-indigo-300 text-xs font-bold uppercase tracking-wider">
                    <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                    </svg>
                    <span>Workspace Divisi</span>
                </div>
                <h1 class="text-xl sm:text-2xl md:text-3xl font-black tracking-tight text-white leading-tight">
                    Tugas & Akun Divisi
                </h1>
                <p class="text-xs md:text-sm text-slate-300 max-w-xl leading-relaxed">
                    Pantau penugasan project aktif, kredensial kerja, dan link Google Drive pengumpulan tugas divisi Anda.
                </p>
            </div>
        </div>
    </div>

    <!-- Notification Alerts -->
    @if(session('success'))
    <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center gap-3 text-emerald-800 text-xs font-semibold shadow-sm">
        <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div class="p-4 bg-red-50 border border-red-200 rounded-2xl flex items-center gap-3 text-red-800 text-xs font-semibold shadow-sm">
        <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    <!-- Card Terpadu: Kredensial & Workspace Divisi -->
    <div class="bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-6 border border-gray-200 shadow-sm space-y-4 sm:space-y-5">
        <!-- Baris 1: Informasi Utama & Tombol Aksi Cepat -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <!-- Sisi Kiri: Ikon, Judul & Subtitle -->
            <div class="flex items-start gap-3.5 sm:gap-4 min-w-0">
                <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl sm:rounded-2xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center shrink-0 shadow-2xs mt-0.5">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                    </svg>
                </div>
                <div class="space-y-1 min-w-0">
                    <h2 class="text-sm sm:text-base font-bold text-gray-900 tracking-tight">Kredensial Kerja & Workspace Divisi</h2>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Kelola kredensial akun kerja divisi dan akses folder Google Drive serta spreadsheet monitoring penugasan resmi Anda.
                    </p>
                </div>
            </div>

            <!-- Sisi Kanan: Tombol Aksi Sederhana & Elegan -->
            <div class="flex flex-wrap sm:flex-nowrap items-center gap-2 shrink-0 pt-2 lg:pt-0 border-t lg:border-t-0 border-gray-100">
                @if($internAccount?->isPlatformEnabled('gdrive') && !empty($internAccount->gdrive_url))
                <div class="flex items-center gap-1 w-full sm:w-auto">
                    <a href="{{ $internAccount->gdrive_url }}" target="_blank" rel="noopener noreferrer"
                        class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-white hover:bg-gray-50 border border-gray-300 text-gray-700 rounded-xl text-xs font-semibold transition shadow-2xs">
                        <i class="fa-brands fa-google-drive text-amber-500 text-sm"></i>
                        <span>Buka Drive</span>
                        <i class="fa-solid fa-arrow-up-right-from-square text-[10px] text-gray-400 ml-0.5"></i>
                    </a>
                    <button type="button" data-url="{{ $internAccount->gdrive_url }}" onclick="navigator.clipboard.writeText(this.dataset.url); alert('Link Google Drive berhasil disalin ke clipboard!');"
                        title="Salin Link Google Drive"
                        class="inline-flex items-center justify-center p-2 bg-white hover:bg-gray-50 border border-gray-300 text-gray-500 hover:text-gray-700 rounded-xl text-xs transition shrink-0 shadow-2xs">
                        <i class="fa-regular fa-copy text-xs"></i>
                    </button>
                </div>
                @endif

                @if($internAccount?->isPlatformEnabled('spreadsheet') && !empty($internAccount->spreadsheet_url))
                <div class="flex items-center gap-1 w-full sm:w-auto">
                    <a href="{{ $internAccount->spreadsheet_url }}" target="_blank" rel="noopener noreferrer"
                        class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-white hover:bg-gray-50 border border-gray-300 text-gray-700 rounded-xl text-xs font-semibold transition shadow-2xs">
                        <i class="fa-solid fa-file-excel text-emerald-600 text-sm"></i>
                        <span>Buka Spreadsheet</span>
                        <i class="fa-solid fa-arrow-up-right-from-square text-[10px] text-gray-400 ml-0.5"></i>
                    </a>
                    <button type="button" data-url="{{ $internAccount->spreadsheet_url }}" onclick="navigator.clipboard.writeText(this.dataset.url); alert('Link Google Spreadsheet berhasil disalin ke clipboard!');"
                        title="Salin Link Google Spreadsheet"
                        class="inline-flex items-center justify-center p-2 bg-white hover:bg-gray-50 border border-gray-300 text-gray-500 hover:text-gray-700 rounded-xl text-xs transition shrink-0 shadow-2xs">
                        <i class="fa-regular fa-copy text-xs"></i>
                    </button>
                </div>
                @endif

                @php
                $canEditCredentials = $internAccount?->isPlatformEnabled('github')
                    || $internAccount?->isPlatformEnabled('figma')
                    || $internAccount?->isPlatformEnabled('sosmed')
                    || !empty($internAccount?->notes);
                @endphp

                @if($canEditCredentials)
                <button type="button" onclick="openDivisionAccountModal()"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-3.5 py-2 bg-white hover:bg-gray-50 border border-gray-300 text-gray-700 rounded-xl text-xs font-semibold transition shadow-2xs">
                    <i class="fa-solid fa-pen-to-square text-xs text-gray-500"></i>
                    <span>Edit Kredensial</span>
                </button>
                @endif
            </div>
        </div>

        <!-- Baris 2: Badge Tautan Akun & Kredensial Terhubung (Hanya tampil jika di-checklist oleh Admin) -->
        <div class="border-t border-gray-100 pt-3 flex flex-wrap items-center gap-2 text-xs">
            @php
            $showGithub = $internAccount?->isPlatformEnabled('github') && !empty($internAccount->github_url);
            $showGmail = $internAccount?->isPlatformEnabled('github') && !empty($internAccount->gmail_account);
            $showFigma = $internAccount?->isPlatformEnabled('figma') && !empty($internAccount->figma_url);
            $showSosmed = $internAccount?->isPlatformEnabled('sosmed') && !empty($userSocialLinks) && count($userSocialLinks) > 0;
            $showNotes = !empty($internAccount?->notes);
            $hasAnyCredential = $showGithub || $showGmail || $showFigma || $showSosmed || $showNotes;
            @endphp

            @if($showGithub)
            <a href="{{ $internAccount->github_url }}" target="_blank" rel="noopener noreferrer"
                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-xl text-gray-700 font-medium transition"
                title="Buka GitHub: {{ $internAccount->github_url }}">
                <i class="fa-brands fa-github text-slate-800 text-sm"></i>
                <span>GitHub Terhubung</span>
                <i class="fa-solid fa-arrow-up-right-from-square text-[10px] text-gray-400 ml-0.5"></i>
            </a>
            @endif

            @if($showGmail)
            <div class="inline-flex items-center gap-1.5 px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-xl text-gray-700 font-medium"
                title="Akun Gmail Kantor: {{ $internAccount->gmail_account }}">
                <i class="fa-solid fa-envelope text-red-500 text-xs"></i>
                <span>{{ $internAccount->gmail_account }}</span>
            </div>
            @endif

            @if($showFigma)
            <a href="{{ $internAccount->figma_url }}" target="_blank" rel="noopener noreferrer"
                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-xl text-gray-700 font-medium transition"
                title="Buka Figma: {{ $internAccount->figma_url }}">
                <i class="fa-brands fa-figma text-purple-600 text-xs"></i>
                <span>Figma Workspace</span>
                <i class="fa-solid fa-arrow-up-right-from-square text-[10px] text-gray-400 ml-0.5"></i>
            </a>
            @endif

            @if($showSosmed)
                @foreach($userSocialLinks as $sLink)
                    @if(!empty($sLink['url']) || !empty($sLink['username']))
                    <a href="{{ !empty($sLink['url']) ? $sLink['url'] : '#' }}" target="{{ !empty($sLink['url']) ? '_blank' : '_self' }}" rel="noopener noreferrer"
                        class="inline-flex items-center gap-1.5 px-2.5 py-1.5 bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-xl text-gray-700 font-medium transition"
                        title="{{ $sLink['platform'] ?? 'Medsos' }}: {{ $sLink['username'] ?? '' }}">
                        <i class="fa-solid fa-share-nodes text-pink-500 text-xs"></i>
                        <span>{{ $sLink['platform'] ?? 'Medsos' }}: {{ $sLink['username'] ?? 'Lihat' }}</span>
                        @if(!empty($sLink['url']))
                        <i class="fa-solid fa-arrow-up-right-from-square text-[10px] text-gray-400 ml-0.5"></i>
                        @endif
                    </a>
                    @endif
                @endforeach
            @endif

            @if($showNotes)
            <div class="inline-flex items-center gap-1.5 px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-xl text-gray-700 font-medium max-w-full"
                title="{{ $internAccount->notes }}">
                <i class="fa-solid fa-note-sticky text-amber-500 text-xs shrink-0"></i>
                <span class="truncate max-w-xs sm:max-w-md">{{ $internAccount->notes }}</span>
            </div>
            @endif

            @if(!$hasAnyCredential)
            <span class="text-xs text-gray-400 italic flex items-center gap-1.5">
                <i class="fa-solid fa-circle-info text-gray-400"></i>
                @if($canEditCredentials)
                <span>Belum ada data kredensial yang ditambahkan. Klik "Edit Kredensial" untuk melengkapi akun kerja Anda.</span>
                @else
                <span>Belum ada tautan tugas atau kredensial yang ditugaskan oleh admin untuk akun Anda.</span>
                @endif
            </span>
            @endif
        </div>
    </div>

    <!-- SECTION 1: PROJECT & TUGAS AKTIF (SEDANG BERJALAN) -->
    @php
    $activeProjectsList = $activeProjects ?? collect();
    $activeMentorTasksList = $activeMentorTasks ?? collect();
    $completedProjectsList = $completedProjects ?? collect();
    $completedMentorTasksList = $completedMentorTasks ?? collect();
    $totalActive = $activeProjectsList->count() + $activeMentorTasksList->count();
    $totalCompleted = $completedProjectsList->count() + $completedMentorTasksList->count();
    @endphp

    <div class="bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-6 border border-gray-200 shadow-sm space-y-4 sm:space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 sm:gap-3 border-b border-gray-100 pb-3 sm:pb-4">
            <div>
                <h2 class="text-base md:text-lg font-black text-gray-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <span>Project & Tugas Aktif</span>
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">Daftar project dan instruksi tugas yang sedang Anda kerjakan saat ini</p>
            </div>

            @if($totalActive > 0)
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200 flex items-center gap-1.5 w-fit">
                <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                <span>{{ $totalActive }} Penugasan Aktif</span>
            </span>
            @else
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-700 border border-gray-200 flex items-center gap-1.5 w-fit">
                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>Semua Penugasan Selesai</span>
            </span>
            @endif
        </div>

        @if($totalActive > 0)
        <div class="grid grid-cols-1 gap-4">
            {{-- 1. KARTU TUGAS BARU DARI PEMBIMBING (JIKA ADA) --}}
            @foreach($activeMentorTasksList as $task)
            @php
            $mentorName = $task->resolver?->profile?->full_name ?? $task->resolver?->name ?? 'Pembimbing';
            $hasInstruction = !empty($task->admin_response);
            @endphp
            <div class="bg-gradient-to-br from-white to-purple-50/40 border-2 border-purple-200 rounded-2xl p-4 sm:p-5 shadow-sm space-y-3.5 sm:space-y-4 flex flex-col justify-between">
                <div class="space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-2 sm:gap-3">
                        <div class="space-y-1">
                            <span class="text-xs font-extrabold uppercase tracking-wider text-purple-700 bg-purple-100 px-2 py-0.5 rounded">
                                Tugas dari Pembimbing
                            </span>
                            <h3 class="text-sm sm:text-base font-bold text-gray-900 leading-snug">
                                {{ $task->project?->nameProject->name ?? 'Tugas & Arahan Kerja' }}
                            </h3>
                        </div>
                        @if($task->status === 'in_progress')
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 flex items-center gap-1.5 flex-shrink-0 self-start sm:self-auto">
                            <span class="w-2 h-2 rounded-full bg-emerald-600 animate-pulse"></span>
                            <span>Sedang Dikerjakan</span>
                        </span>
                        @else
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200 flex items-center gap-1.5 flex-shrink-0 self-start sm:self-auto">
                            <svg class="w-3 h-3 animate-spin text-amber-600" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Menunggu Arahan</span>
                        </span>
                        @endif
                    </div>

                    @if($hasInstruction)
                    <div class="p-3 sm:p-3.5 bg-purple-50/80 border border-purple-200 rounded-xl space-y-1.5">
                        <div class="text-xs font-bold text-purple-900 flex items-center gap-1.5">
                            <i class="fa-solid fa-clipboard-list text-purple-600"></i>
                            <span>Instruksi Tugas:</span>
                        </div>
                        <div class="text-xs sm:text-sm text-gray-900 font-semibold leading-relaxed whitespace-pre-line bg-white p-3 rounded-lg border border-purple-100">
                            {{ $task->admin_response }}
                        </div>
                    </div>
                    @else
                    <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800">
                        Permintaan tugas telah terkirim. Menunggu pembimbing memberikan rincian tugas.
                    </div>
                    @endif

                    @if(!empty($task->notes))
                    <div class="p-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-600">
                        <span class="font-bold text-gray-700 block mb-0.5">Catatan Pengajuan:</span>
                        <p class="whitespace-pre-line text-gray-700">{{ $task->notes }}</p>
                    </div>
                    @endif

                    {{-- WIDGET LINK PROJECT / HASIL KARYA TUGAS --}}
                    @php
                    $taskWorkUrl = $task->project?->repository_url ?? '';
                    $projId = $task->project?->id;
                    $projName = $task->project?->nameProject->name ?? 'Tugas Pembimbing';
                    $isUiMode = $isUserUiUx || str_contains(strtolower($taskWorkUrl), 'figma');
                    @endphp
                    @if($projId)
                    <div class="p-3 bg-white rounded-xl border border-purple-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 text-xs shadow-2xs">
                        <div class="flex items-center gap-2 min-w-0">
                            <div class="w-7 h-7 rounded-lg {{ $isUiMode ? 'bg-purple-600' : 'bg-gray-900' }} text-white flex items-center justify-center flex-shrink-0 text-sm">
                                <i class="{{ $isUiMode ? 'fa-brands fa-figma' : 'fa-brands fa-github' }}"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="font-bold text-gray-800 text-xs">{{ $isUiMode ? 'Project Figma Tugas:' : 'Hasil Karya / Repo Tugas:' }}</div>
                                @if(!empty($taskWorkUrl))
                                <a href="{{ $taskWorkUrl }}" target="_blank" rel="noopener noreferrer"
                                    class="{{ $isUiMode ? 'text-purple-600 hover:text-purple-800' : 'text-indigo-600 hover:text-indigo-800' }} hover:underline text-xs font-medium flex items-center gap-1 truncate max-w-full sm:max-w-xs"
                                    title="{{ $taskWorkUrl }}">
                                    <span class="truncate">{{ $taskWorkUrl }}</span>
                                    <i class="fa-solid fa-arrow-up-right-from-square text-xs flex-shrink-0"></i>
                                </a>
                                @else
                                <span class="text-gray-400 text-xs">Belum ditautkan link repo/hasil karya</span>
                                @endif
                            </div>
                        </div>
                        <button type="button"
                            data-project-id="{{ $projId }}"
                            data-project-name="{{ $projName }}"
                            data-current-url="{{ $taskWorkUrl }}"
                            data-mode="{{ $isUiMode ? 'uiux' : 'programmer' }}"
                            onclick="openGitRepoModal(this)"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-1 px-3 py-1.5 {{ $isUiMode ? 'bg-purple-50 hover:bg-purple-100 border border-purple-300 text-purple-800' : 'bg-gray-50 hover:bg-gray-100 border border-gray-300 text-gray-800' }} text-xs font-bold rounded-lg transition shadow-xs flex-shrink-0">
                            <i class="fa-solid fa-link {{ $isUiMode ? 'text-purple-600' : 'text-indigo-600' }} text-xs"></i>
                            <span>{{ !empty($taskWorkUrl) ? 'Ubah Tautan' : '+ Tautkan Hasil Karya' }}</span>
                        </button>
                    </div>
                    @endif
                </div>

                <div class="pt-3 border-t border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-1 text-xs text-gray-500">
                    <span class="text-xs">Pembimbing: <strong>{{ $mentorName }}</strong></span>
                    <span class="text-xs text-gray-400">
                        {{ $task->updated_at ? $task->updated_at->format('d M Y, H:i') : ($task->created_at ? $task->created_at->format('d M Y, H:i') : '-') }}
                    </span>
                </div>
            </div>
            @endforeach

            {{-- 2. KARTU PROJECT UTAMA --}}
            @foreach($activeProjectsList as $project)
            @php
            $review = isset($latestPresentationReviews) ? $latestPresentationReviews->get($project->id) : null;
            $hasRevision = ($review && $review->status === 'needs_revision');
            $isReady = ($review && $review->status === 'ready');
            $members = $project->members ?? collect();
            $pMentorName = $review?->resolver?->profile?->full_name ?? $review?->resolver?->name ?? 'Pembimbing';
            @endphp
            <div class="bg-gradient-to-br from-white to-gray-50/70 border-2 {{ $hasRevision ? 'border-amber-300' : 'border-gray-200' }} rounded-2xl p-4 sm:p-5 shadow-sm hover:shadow-md transition-all space-y-3.5 sm:space-y-4 flex flex-col justify-between">
                <div class="space-y-3">
                    <!-- Header Project & Status -->
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-2 sm:gap-3">
                        <div class="space-y-1">
                            <span class="text-xs font-extrabold uppercase tracking-wider text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded">
                                Tim: {{ $project->team ?? 'Divisi' }}
                            </span>
                            <h3 class="text-sm sm:text-base font-bold text-gray-900 leading-snug">
                                {{ $project->nameProject->name ?? 'Project Tanpa Judul' }}
                            </h3>
                        </div>

                        @if($hasRevision)
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-900 border border-amber-300 flex items-center gap-1.5 flex-shrink-0 self-start sm:self-auto shadow-xs">
                            <span class="w-2 h-2 rounded-full bg-amber-600 animate-pulse"></span>
                            <span>Perlu Perbaikan</span>
                        </span>
                        @elseif($isReady)
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 flex items-center gap-1.5 flex-shrink-0 self-start sm:self-auto shadow-xs">
                            <i class="fa-solid fa-circle-check text-emerald-600 text-xs"></i>
                            <span>Sudah Presentasi</span>
                        </span>
                        @else
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-200 flex items-center gap-1 flex-shrink-0 self-start sm:self-auto">
                            <svg class="w-3 h-3 animate-spin text-blue-600" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Sedang Dikerjakan</span>
                        </span>
                        @endif
                    </div>

                    <!-- Deskripsi & Arahan Awal Project -->
                    <div class="p-3 bg-white rounded-xl border border-gray-200 text-xs text-gray-700 leading-relaxed">
                        <span class="font-bold text-gray-900 block mb-1 text-xs">Deskripsi & Arahan:</span>
                        {{ $project->description ?: 'Tidak ada deskripsi detail pada penugasan ini.' }}
                    </div>

                    <!-- KOTAK CATATAN REVISI PROJECT (DIISI SENDIRI OLEH PEMAGANG) -->
                    @if($hasRevision)
                    @php
                    $internRevNotes = $review?->performance_notes;
                    @endphp
                    <div class="p-3 sm:p-3.5 bg-amber-50/40 border border-amber-200/80 rounded-xl space-y-2.5 shadow-2xs">
                        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-amber-200/60 pb-2">
                            <div class="flex items-center gap-2 text-xs text-slate-800 min-w-0">
                                <div class="w-6 h-6 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center text-xs flex-shrink-0">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                </div>
                                <div class="flex flex-wrap items-center gap-x-1.5 gap-y-0.5 min-w-0">
                                    <span class="font-bold text-slate-900">Project Memerlukan Revisi</span>
                                    <span class="text-xs text-slate-500 font-normal">· Ditinjau oleh {{ $pMentorName }}</span>
                                </div>
                            </div>
                            <span class="text-[11px] text-slate-500 font-medium bg-white/90 px-2.5 py-0.5 rounded-full border border-slate-200/80 flex-shrink-0">
                                {{ $review->resolved_at ? $review->resolved_at->format('d M Y, H:i') : ($review->updated_at ? $review->updated_at->format('d M Y, H:i') : '-') }}
                            </span>
                        </div>

                        {{-- Bagian Catatan Revisi yang Diisi oleh Pemagang --}}
                        <div class="p-2.5 sm:p-3 bg-white rounded-xl border border-amber-200/70 space-y-2 shadow-2xs">
                            <div class="flex items-center justify-between gap-2">
                                <div class="text-xs font-bold text-slate-800 flex items-center gap-1.5 min-w-0">
                                    <i class="fa-solid fa-clipboard-list text-amber-600 flex-shrink-0 text-xs"></i>
                                    <span class="truncate">Poin Revisi yang Harus Dikerjakan (Diisi Pemagang):</span>
                                </div>
                                <button type="button"
                                    data-project-id="{{ $project->id }}"
                                    data-project-name="{{ $project->nameProject->name ?? 'Project' }}"
                                    data-current-notes="{{ $internRevNotes ?? '' }}"
                                    onclick="openRevisionModal(this)"
                                    class="inline-flex items-center justify-center gap-1 px-2.5 py-1 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-300/80 rounded-lg text-xs font-semibold transition cursor-pointer flex-shrink-0">
                                    <i class="fa-solid fa-pen-to-square text-[11px]"></i>
                                    <span>{{ !empty($internRevNotes) ? 'Edit Catatan Revisi' : '+ Isi Catatan Revisi' }}</span>
                                </button>
                            </div>

                            @if(!empty($internRevNotes))
                            <div class="text-xs text-slate-700 whitespace-pre-line leading-relaxed font-medium bg-slate-50/70 p-2.5 rounded-lg border border-slate-200/80">{{ trim($internRevNotes) }}</div>
                            @else
                            <div class="p-2.5 bg-slate-50/60 rounded-lg border border-dashed border-slate-300 text-center space-y-0.5">
                                <p class="text-xs text-slate-800 font-semibold">
                                    Belum ada catatan revisi yang diisi.
                                </p>
                                <p class="text-[11px] text-slate-500 max-w-md mx-auto leading-relaxed">
                                    Silakan klik tombol <strong>"+ Isi Catatan Revisi"</strong> di atas untuk mencatat rincian perbaikan apa saja yang perlu Anda kerjakan sesuai hasil presentasi.
                                </p>
                            </div>
                            @endif
                        </div>

                        <div class="text-[11px] text-slate-500 flex items-center gap-1.5 pt-0.5">
                            <i class="fa-solid fa-circle-info text-amber-600 text-xs flex-shrink-0"></i>
                            <span>Setelah menyelesaikan poin revisi di atas, ajukan jadwal presentasi ulang melalui menu <strong>Raise Hand</strong> di Dashboard.</span>
                        </div>
                    </div>
                    @endif

                    {{-- WIDGET LINK PROJECT / HASIL KARYA TUGAS --}}
                    @php
                    $projWorkUrl = $project->repository_url ?? '';
                    $isUiModeProj = $isUserUiUx || str_contains(strtolower($projWorkUrl), 'figma');
                    @endphp
                    <div class="p-3 bg-white rounded-xl border border-gray-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 text-xs shadow-2xs">
                        <div class="flex items-center gap-2 min-w-0">
                            <div class="w-7 h-7 rounded-lg {{ $isUiModeProj ? 'bg-purple-600' : 'bg-gray-900' }} text-white flex items-center justify-center flex-shrink-0 text-sm">
                                <i class="{{ $isUiModeProj ? 'fa-brands fa-figma' : 'fa-brands fa-github' }}"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="font-bold text-gray-800 text-xs">{{ $isUiModeProj ? 'Project Figma:' : 'Hasil Karya / Git Repository:' }}</div>
                                @if(!empty($projWorkUrl))
                                <a href="{{ $projWorkUrl }}" target="_blank" rel="noopener noreferrer"
                                    class="{{ $isUiModeProj ? 'text-purple-600 hover:text-purple-800' : 'text-indigo-600 hover:text-indigo-800' }} hover:underline text-xs font-medium flex items-center gap-1 truncate max-w-full sm:max-w-xs"
                                    title="{{ $projWorkUrl }}">
                                    <span class="truncate">{{ $projWorkUrl }}</span>
                                    <i class="fa-solid fa-arrow-up-right-from-square text-xs flex-shrink-0"></i>
                                </a>
                                @else
                                <span class="text-gray-400 text-xs">Belum ditautkan link hasil karya/repo</span>
                                @endif
                            </div>
                        </div>
                        <button type="button"
                            data-project-id="{{ $project->id }}"
                            data-project-name="{{ $project->nameProject->name ?? 'Project' }}"
                            data-current-url="{{ $projWorkUrl }}"
                            data-mode="{{ $isUiModeProj ? 'uiux' : 'programmer' }}"
                            onclick="openGitRepoModal(this)"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-1 px-3 py-1.5 {{ $isUiModeProj ? 'bg-purple-50 hover:bg-purple-100 border border-purple-300 text-purple-800' : 'bg-gray-50 hover:bg-gray-100 border border-gray-300 text-gray-800' }} text-xs font-bold rounded-lg transition shadow-xs flex-shrink-0">
                            <i class="fa-solid fa-link {{ $isUiModeProj ? 'text-purple-600' : 'text-indigo-600' }} text-xs"></i>
                            <span>{{ !empty($projWorkUrl) ? 'Ubah Tautan' : '+ Tautkan Hasil Karya' }}</span>
                        </button>
                    </div>
                </div>

            </div>
            @endforeach
        </div>
        @else
        <!-- Empty State: Tidak Ada Project atau Tugas Aktif (Singkat, Bersih, Tanpa Emote) -->
        <div class="text-center py-8 px-4 bg-gray-50 rounded-2xl border border-dashed border-gray-300 space-y-1.5">
            <svg class="w-7 h-7 text-gray-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
            </svg>
            <h3 class="text-xs md:text-sm font-bold text-gray-800">Tidak Ada Project atau Tugas Aktif</h3>
            <p class="text-xs text-gray-500 max-w-sm mx-auto">
                Semua penugasan telah selesai. Gunakan fitur Raise Hand di Dashboard jika membutuhkan penugasan baru.
            </p>
        </div>
        @endif
    </div>

    <!-- SECTION 2: RIWAYAT PENUGASAN SELESAI (ARSIP PORTOFOLIO) -->
    @if($totalCompleted > 0)
    <div class="bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-6 border border-gray-200 shadow-sm space-y-4 sm:space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 sm:gap-3 border-b border-gray-100 pb-3 sm:pb-4">
            <div>
                <h2 class="text-base md:text-lg font-black text-gray-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                    </svg>
                    <span>Riwayat Project / Tugas Selesai</span>
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">Daftar pencapaian project dan tugas yang telah selesai disahkan</p>
            </div>

            <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-center gap-1.5 w-fit">
                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ $totalCompleted }} Penugasan Selesai</span>
            </span>
        </div>

        <div class="grid grid-cols-1 gap-4">
            {{-- 1. Riwayat Tugas Pembimbing yang Selesai --}}
            @foreach($completedMentorTasksList as $cTask)
            @php
            $cMentor = $cTask->resolver?->profile?->full_name ?? $cTask->resolver?->name ?? 'Pembimbing';
            @endphp
            <div class="bg-gray-50/80 border border-gray-200 rounded-2xl p-4 sm:p-5 shadow-2xs space-y-3 flex flex-col justify-between">
                <div class="space-y-2">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-2">
                        <span class="text-xs font-extrabold uppercase tracking-wider text-purple-700 bg-purple-100 px-2 py-0.5 rounded">
                            Tugas Pembimbing
                        </span>
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 flex items-center gap-1 flex-shrink-0 self-start sm:self-auto">
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Selesai (Valid)</span>
                        </span>
                    </div>
                    @if(!empty($cTask->admin_response))
                    <div class="p-3 bg-white rounded-xl border border-gray-200 text-xs text-gray-800">
                        <span class="font-bold text-gray-700 block mb-1 text-xs">Tugas yang Dikerjakan:</span>
                        <p class="whitespace-pre-line text-gray-800 font-medium leading-relaxed">{{ $cTask->admin_response }}</p>
                    </div>
                    @endif
                </div>
                <div class="pt-2 border-t border-gray-200 flex flex-col sm:flex-row sm:items-center justify-between gap-1 text-xs text-gray-500">
                    <span>Oleh: <strong>{{ $cMentor }}</strong></span>
                    <span class="text-gray-400">
                        {{ $cTask->resolved_at ? $cTask->resolved_at->format('d M Y, H:i') : ($cTask->created_at ? $cTask->created_at->format('d M Y, H:i') : '-') }}
                    </span>
                </div>
            </div>
            @endforeach

            {{-- 2. Riwayat Project Tim yang Selesai --}}
            @foreach($completedProjectsList as $project)
            @php
            $members = $project->members ?? collect();
            @endphp
            <div class="bg-gray-50/70 border border-gray-200 rounded-2xl p-4 sm:p-5 shadow-2xs space-y-3.5 sm:space-y-4 flex flex-col justify-between">
                <div class="space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-2 sm:gap-3">
                        <div class="space-y-1">
                            <span class="text-xs font-extrabold uppercase tracking-wider text-gray-600 bg-gray-200 px-2 py-0.5 rounded">
                                Tim: {{ $project->team ?? 'Divisi' }}
                            </span>
                            <h3 class="text-sm sm:text-base font-bold text-gray-900 leading-snug">
                                {{ $project->nameProject->name ?? 'Project Tanpa Judul' }}
                            </h3>
                        </div>

                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 flex items-center gap-1 flex-shrink-0 self-start sm:self-auto">
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Selesai (Valid)</span>
                        </span>
                    </div>

                    <div class="p-3 bg-white rounded-xl border border-gray-200 text-xs text-gray-700 leading-relaxed">
                        <span class="font-bold text-gray-900 block mb-1 text-xs">Deskripsi Project:</span>
                        {{ $project->description ?: 'Tidak ada deskripsi detail pada penugasan ini.' }}
                    </div>

                    @if($isUserProg && !empty($project->repository_url))
                    <div class="p-2.5 bg-white rounded-xl border border-gray-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                        <div class="flex items-center gap-2 truncate min-w-0">
                            <i class="fa-brands fa-github text-gray-800 flex-shrink-0"></i>
                            <span class="font-medium text-gray-700 truncate text-xs">{{ $project->repository_url }}</span>
                        </div>
                        <a href="{{ $project->repository_url }}" target="_blank" rel="noopener noreferrer"
                            class="w-full sm:w-auto justify-center px-2.5 py-1.5 sm:py-1 bg-gray-900 hover:bg-black text-white rounded-lg text-xs font-bold flex items-center gap-1 flex-shrink-0">
                            <span>Buka Repo</span>
                            <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                        </a>
                    </div>
                    @elseif($isUserUiUx)
                    @php
                    $completedFigma = (!empty($project->repository_url) && str_contains($project->repository_url, 'figma'))
                    ? $project->repository_url
                    : null;
                    @endphp
                    @if(!empty($completedFigma))
                    <div class="p-2.5 bg-white rounded-xl border border-purple-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                        <div class="flex items-center gap-2 truncate min-w-0">
                            <i class="fa-brands fa-figma text-purple-600 flex-shrink-0"></i>
                            <span class="font-medium text-purple-900 truncate text-xs">{{ $completedFigma }}</span>
                        </div>
                        <a href="{{ $completedFigma }}" target="_blank" rel="noopener noreferrer"
                            class="w-full sm:w-auto justify-center px-2.5 py-1.5 sm:py-1 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-xs font-bold flex items-center gap-1 flex-shrink-0">
                            <span>Buka Figma</span>
                            <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                        </a>
                    </div>
                    @endif
                    @endif
                </div>

            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>

<!-- ==================================================================== -->
<!-- [MODAL 1] KELOLA AKUN & PORTOFOLIO DIVISI (DIPINDAHKAN DARI DASHBOARD) -->
<!-- ==================================================================== -->
<div id="divisionAccountModal" class="fixed inset-0 bg-gray-900/70 backdrop-blur-sm flex justify-center items-center hidden z-[9999] p-3 sm:p-4" onclick="if(event.target === this) closeDivisionAccountModal();">
    <div class="bg-white rounded-2xl sm:rounded-3xl overflow-hidden shadow-2xl w-full max-w-2xl transform transition-all flex flex-col max-h-[90vh] animate-fadeIn">
        <!-- Modal Header -->
        <div class="bg-slate-900 text-white px-4 sm:px-6 py-3.5 sm:py-5 flex justify-between items-center border-b border-slate-800">
            <div class="flex items-center space-x-3">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white text-base sm:text-lg flex-shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm sm:text-base font-bold text-white">Akun & Portofolio Divisi</h3>
                    <p class="text-xs sm:text-sm text-slate-400">Divisi: <span class="text-indigo-300 font-semibold">{{ $user->intern->division->name ?? 'Pemagang' }}</span></p>
                </div>
            </div>
            <button type="button" onclick="closeDivisionAccountModal()" class="text-gray-400 hover:text-white transition p-1 focus:outline-none">
                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Modal Body (Scrollable) -->
        <div class="p-4 sm:p-6 overflow-y-auto space-y-4 sm:space-y-5 text-xs">

            <!-- FORM MANDIRI AKUN & PORTOFOLIO DIVISI -->
            <form action="{{ route('user.account.update') }}" method="POST" class="space-y-4">
                @csrf

                <div class="border border-gray-200 rounded-2xl p-3.5 sm:p-4 bg-white shadow-sm space-y-3.5 sm:space-y-4">
                    <div class="border-b pb-2">
                        <h4 class="text-xs sm:text-sm font-bold text-gray-800 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-indigo-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                            </svg>
                            <span>Akun & Kredensial Kerja Anda</span>
                        </h4>
                        <p class="text-xs text-gray-500 mt-0.5">Kelola link profil, akun kerja kantor, workspace, dan media sosial yang Anda gunakan.</p>
                    </div>

                    <!-- 1. GitHub & Gmail Kantor (Hanya jika di-checklist oleh Admin) -->
                    @if($internAccount?->isPlatformEnabled('github'))
                    <div class="p-3.5 bg-slate-50/70 border border-slate-200 rounded-xl space-y-3">
                        <div class="flex items-center gap-2 font-bold text-slate-800 text-xs border-b border-slate-200/80 pb-1.5">
                            <i class="fa-brands fa-github text-slate-900 text-sm"></i>
                            <span>GitHub & Akun Gmail Kantor</span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label for="user_github_url" class="block text-xs font-semibold text-gray-700 mb-1">
                                    Link Profil / Repository GitHub
                                </label>
                                <input type="url" id="user_github_url" name="github_url"
                                    value="{{ old('github_url', $internAccount?->github_url) }}"
                                    placeholder="https://github.com/username-anda"
                                    class="w-full p-2.5 border border-gray-300 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                            </div>
                            <div>
                                <label for="user_gmail_account" class="block text-xs font-semibold text-gray-700 mb-1">
                                    Akun Gmail Kantor
                                </label>
                                <input type="email" id="user_gmail_account" name="gmail_account"
                                    value="{{ old('gmail_account', $internAccount?->gmail_account) }}"
                                    placeholder="nama@gmail.com"
                                    class="w-full p-2.5 border border-gray-300 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                            </div>
                        </div>
                        <div>
                            <label for="user_gmail_password" class="block text-xs font-semibold text-gray-700 mb-1">
                                <i class="fa-solid fa-key text-amber-500 mr-1"></i> Password Akun Kantor (Terenkripsi Aman)
                            </label>
                            <div class="relative">
                                <input type="password" id="user_gmail_password" name="gmail_password"
                                    value="{{ old('gmail_password', $internAccount?->gmail_password) }}"
                                    placeholder="{{ $internAccount?->gmail_password ? '•••••••• (Isi untuk mengganti)' : 'Masukkan password akun kantor' }}"
                                    class="w-full p-2.5 pr-10 border border-gray-300 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white font-mono">
                                <button type="button" onclick="toggleTaskPasswordVisibility('user_gmail_password', this)"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none"
                                    title="Lihat / Sembunyikan Password">
                                    <i class="fa-regular fa-eye text-xs"></i>
                                </button>
                            </div>
                            <p class="text-[11px] text-gray-500 mt-1">
                                <i class="fa-solid fa-lock text-slate-400 mr-1"></i> Disimpan dengan enkripsi dua arah (AES-256). Dapat diisi atau disunting oleh pemagang dan admin.
                            </p>
                        </div>
                    </div>
                    @endif

                    <!-- 2. Figma Workspace (Hanya jika di-checklist oleh Admin) -->
                    @if($internAccount?->isPlatformEnabled('figma'))
                    <div class="p-3.5 bg-purple-50/40 border border-purple-200 rounded-xl space-y-2">
                        <div class="flex items-center gap-2 font-bold text-purple-950 text-xs border-b border-purple-100 pb-1.5">
                            <i class="fa-brands fa-figma text-purple-600 text-sm"></i>
                            <span>Figma Workspace & Desain</span>
                        </div>
                        <div>
                            <label for="user_figma_url" class="block text-xs font-semibold text-gray-700 mb-1">
                                Link Profil / Workspace / File Figma
                            </label>
                            <input type="url" id="user_figma_url" name="figma_url"
                                value="{{ old('figma_url', $internAccount?->figma_url) }}"
                                placeholder="https://www.figma.com/@username atau link file"
                                class="w-full p-2.5 border border-gray-300 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 bg-white">
                            <p class="text-[11px] text-gray-500 mt-1">Tautan workspace atau canvas desain yang Anda kerjakan.</p>
                        </div>
                    </div>
                    @endif

                    <!-- 3. Akun Media Sosial yang Dikelola (Hanya jika di-checklist oleh Admin) -->
                    @if($internAccount?->isPlatformEnabled('sosmed'))
                    <div class="p-3.5 bg-pink-50/40 border border-pink-200 rounded-xl space-y-2.5">
                        <div class="flex items-center justify-between border-b border-pink-100 pb-1.5">
                            <span class="font-bold text-pink-950 text-xs flex items-center gap-1.5">
                                <i class="fa-solid fa-share-nodes text-pink-500 text-sm"></i>
                                <span>Akun Media Sosial yang Dikelola</span>
                            </span>
                            <button type="button" onclick="addUserSocialRow()" class="text-xs font-semibold text-pink-600 hover:text-pink-800 bg-white hover:bg-pink-100 px-2.5 py-1 rounded-lg border border-pink-200 transition shadow-2xs">
                                <i class="fa-solid fa-plus mr-1"></i> Tambah Akun
                            </button>
                        </div>

                        <div id="user-social-container" data-initial-index="{{ count($userSocialLinks) }}" class="space-y-2">
                            @forelse($userSocialLinks as $idx => $sLink)
                            <div class="user-social-row p-2.5 bg-white border border-gray-200 rounded-xl flex flex-col sm:flex-row gap-2 items-start sm:items-center shadow-2xs">
                                <div class="w-full sm:w-1/4">
                                    <select name="social_media_links[{{ $idx }}][platform]" class="w-full p-2 text-xs border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
                                        <option value="Instagram" {{ ($sLink['platform'] ?? '') === 'Instagram' ? 'selected' : '' }}>Instagram</option>
                                        <option value="TikTok" {{ ($sLink['platform'] ?? '') === 'TikTok' ? 'selected' : '' }}>TikTok</option>
                                        <option value="LinkedIn" {{ ($sLink['platform'] ?? '') === 'LinkedIn' ? 'selected' : '' }}>LinkedIn</option>
                                        <option value="YouTube" {{ ($sLink['platform'] ?? '') === 'YouTube' ? 'selected' : '' }}>YouTube</option>
                                        <option value="Facebook" {{ ($sLink['platform'] ?? '') === 'Facebook' ? 'selected' : '' }}>Facebook</option>
                                        <option value="Twitter/X" {{ ($sLink['platform'] ?? '') === 'Twitter/X' ? 'selected' : '' }}>Twitter / X</option>
                                        <option value="Lainnya" {{ ($sLink['platform'] ?? '') === 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                                    </select>
                                </div>
                                <div class="w-full sm:w-1/3">
                                    <input type="text" name="social_media_links[{{ $idx }}][username]"
                                        value="{{ $sLink['username'] ?? '' }}"
                                        placeholder="@username"
                                        class="w-full p-2 text-xs border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
                                </div>
                                <div class="w-full sm:w-2/5 flex items-center gap-1">
                                    <input type="url" name="social_media_links[{{ $idx }}][url]"
                                        value="{{ $sLink['url'] ?? '' }}"
                                        placeholder="https://..."
                                        class="w-full p-2 text-xs border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
                                    <button type="button" onclick="removeUserSocialRow(this)" class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg shrink-0" title="Hapus">
                                        <i class="fa-regular fa-trash-can text-xs"></i>
                                    </button>
                                </div>
                            </div>
                            @empty
                            <div id="no-user-social-msg" class="text-center py-3 text-xs text-gray-400 bg-white rounded-xl border border-dashed border-gray-200">
                                Belum ada akun media sosial yang didaftarkan.
                            </div>
                            @endforelse
                        </div>
                    </div>
                    @endif

                    <!-- 4. Catatan / Catatan Portofolio Tambahan -->
                    <div class="p-3.5 bg-amber-50/40 border border-amber-200 rounded-xl space-y-2">
                        <div class="flex items-center gap-2 font-bold text-amber-950 text-xs border-b border-amber-100 pb-1.5">
                            <i class="fa-solid fa-note-sticky text-amber-500 text-sm"></i>
                            <span>Catatan Kredensial / Link Portofolio Tambahan</span>
                        </div>
                        <textarea id="user_notes" name="notes" rows="2"
                            placeholder="Tuliskan link portofolio atau catatan tambahan tools (Canva, hosting, cPanel, dll)..."
                            class="w-full p-2.5 border border-gray-300 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 bg-white">{{ old('notes', $internAccount?->notes) }}</textarea>
                    </div>

                    @if(!$canEditCredentials && empty($internAccount?->notes))
                    <div class="p-6 text-center text-gray-500 text-xs bg-gray-50 rounded-xl border border-dashed border-gray-200 space-y-1">
                        <i class="fa-solid fa-lock text-gray-400 text-lg mb-1"></i>
                        <p class="font-bold text-gray-700">Tidak ada formulir kredensial aktif</p>
                        <p class="text-gray-400">Admin belum mengaktifkan checklist platform kredensial kerja untuk akun Anda.</p>
                    </div>
                    @endif

                    <div class="pt-2 flex flex-col sm:flex-row justify-end gap-2">
                        <button type="button" onclick="closeDivisionAccountModal()" class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-xl text-xs font-semibold text-gray-700 hover:bg-gray-100 transition text-center">
                            Batal
                        </button>
                        <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-xs transition">
                            <i class="fa-solid fa-floppy-disk text-xs"></i>
                            <span>Simpan Perubahan</span>
                        </button>
                    </div>
                </div>
            </form>

        </div>

        <!-- Modal Footer -->
        <div class="px-4 sm:px-6 py-3 sm:py-4 bg-gray-50 border-t border-gray-200 flex justify-end">
            <button type="button" onclick="closeDivisionAccountModal()" class="w-full sm:w-auto px-5 py-2 bg-gray-800 hover:bg-gray-700 text-white text-xs font-semibold rounded-xl transition text-center">
                Tutup
            </button>
        </div>
    </div>
</div>

<!-- ==================================================================== -->
<!-- [MODAL 2] TAUTKAN LINK KARYA PROJECT (GIT REPO / FIGMA) -->
<!-- ==================================================================== -->
<div id="gitRepoModal" class="fixed inset-0 bg-gray-900/70 backdrop-blur-sm flex justify-center items-center hidden z-[9999] p-3 sm:p-4" onclick="if(event.target === this) closeGitRepoModal();">
    <div class="bg-white rounded-2xl sm:rounded-3xl overflow-hidden shadow-2xl w-full max-w-lg transform transition-all flex flex-col animate-fadeIn">
        <!-- Modal Header -->
        <div id="repoModalHeader" class="bg-gray-900 text-white px-4 sm:px-6 py-3.5 sm:py-5 flex justify-between items-center border-b border-gray-800">
            <div class="flex items-center space-x-3">
                <div id="repoModalIconWrapper" class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-gray-800 text-white flex items-center justify-center text-lg sm:text-xl shadow-sm border border-gray-700 flex-shrink-0">
                    <i id="repoModalIcon" class="fa-brands fa-github"></i>
                </div>
                <div>
                    <h3 id="repoModalTitle" class="text-sm sm:text-base font-bold text-white">Tautkan Git Repository</h3>
                    <p id="repoModalSubtitle" class="text-xs sm:text-sm text-gray-400 mt-0.5">Khusus Divisi Programmer & Developer</p>
                </div>
            </div>
            <button type="button" onclick="closeGitRepoModal()" class="w-8 h-8 rounded-full bg-gray-800 text-gray-400 hover:text-white hover:bg-gray-700 flex items-center justify-center transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Modal Form -->
        <form id="gitRepoForm" method="POST" action="">
            @csrf
            <div class="p-4 sm:p-6 space-y-4 text-xs">
                <div>
                    <span class="text-gray-500 font-medium block mb-1">Nama Project:</span>
                    <div id="gitRepoProjectName" class="font-bold text-gray-900 text-xs sm:text-sm bg-gray-50 p-3 rounded-xl border border-gray-200">
                        -
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label id="repoModalLabel" for="git_repository_url" class="block font-bold text-gray-800">
                        URL Repository Git <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="url" name="repository_url" id="git_repository_url" required
                            placeholder="https://github.com/username/nama-project"
                            class="w-full p-2.5 sm:p-3 pl-10 border border-gray-300 rounded-xl text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 font-medium">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <i id="repoModalInputIcon" class="fa-brands fa-github text-sm"></i>
                        </div>
                    </div>
                    <p id="repoModalHelp" class="text-xs text-gray-500 leading-relaxed pt-0.5">
                        Masukkan tautan repository GitHub, GitLab, atau Bitbucket. Pastikan repo bersifat <strong>Public</strong> atau pembimbing telah di-invite sebagai collaborator agar kode bisa direview.
                    </p>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-4 sm:px-6 py-3 sm:py-4 bg-gray-50 border-t border-gray-200 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2 sm:gap-2.5">
                <button type="button" onclick="closeGitRepoModal()" class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-xl text-xs font-semibold text-gray-700 hover:bg-gray-100 transition text-center">
                    Batal
                </button>
                <button id="repoModalSubmitBtn" type="submit" class="w-full sm:w-auto px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-sm transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-cloud-arrow-up text-xs"></i>
                    <span id="repoModalSubmitText">Simpan Repository</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ==================================================================== -->
<!-- [MODAL 3] INPUT CATATAN PENGERJAAN REVISI OLEH PEMAGANG -->
<!-- ==================================================================== -->
<div id="revisionModal" class="fixed inset-0 bg-gray-900/70 backdrop-blur-sm flex justify-center items-center hidden z-[9999] p-3 sm:p-4" onclick="if(event.target === this) closeRevisionModal();">
    <div class="bg-white rounded-2xl sm:rounded-3xl overflow-hidden shadow-2xl w-full max-w-lg transform transition-all flex flex-col animate-fadeIn">
        <!-- Modal Header -->
        <div class="bg-amber-600 text-white px-4 sm:px-6 py-3.5 sm:py-5 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-white/20 flex items-center justify-center text-white text-base sm:text-lg flex-shrink-0">
                    <i class="fa-solid fa-pen-to-square"></i>
                </div>
                <div>
                    <h3 class="text-sm sm:text-base font-bold text-white">Catatan Pengerjaan Revisi</h3>
                    <p class="text-xs sm:text-sm text-amber-100">Catat bagian yang telah Anda perbaiki sesuai arahan</p>
                </div>
            </div>
            <button type="button" onclick="closeRevisionModal()" class="text-amber-200 hover:text-white transition p-1 focus:outline-none">
                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Modal Form -->
        <form id="revisionForm" method="POST" action="">
            @csrf
            <div class="p-4 sm:p-6 space-y-4 text-xs">
                <div>
                    <span class="text-gray-500 font-medium block mb-1">Project:</span>
                    <div id="revisionProjectName" class="font-bold text-gray-900 text-xs sm:text-sm bg-gray-50 p-3 rounded-xl border border-gray-200">
                        -
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label for="intern_revision_notes" class="block font-bold text-gray-800">
                        Poin-poin Revisi yang Dikerjakan <span class="text-red-500">*</span>
                    </label>
                    <textarea name="intern_revision_notes" id="intern_revision_notes" rows="4" required
                        placeholder="Contoh:&#10;1. Memperbaiki validasi input pada form registrasi&#10;2. Mengubah layout responsif di tampilan mobile&#10;3. Menambahkan feedback error pesan sesuai arahan"
                        class="w-full p-2.5 sm:p-3 border border-gray-300 rounded-xl text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 font-medium leading-relaxed"></textarea>
                    <p class="text-xs text-gray-500 leading-relaxed pt-0.5">
                        Tuliskan rincian perbaikan yang sudah selesai dikerjakan agar pembimbing dapat meninjau progres sebelum presentasi ulang.
                    </p>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-4 sm:px-6 py-3 sm:py-4 bg-gray-50 border-t border-gray-200 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2 sm:gap-2.5">
                <button type="button" onclick="closeRevisionModal()" class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-xl text-xs font-semibold text-gray-700 hover:bg-gray-100 transition text-center">
                    Batal
                </button>
                <button type="submit" class="w-full sm:w-auto px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl shadow-sm transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-floppy-disk text-xs"></i>
                    <span>Simpan Catatan Revisi</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openDivisionAccountModal(field = null) {
        $('#divisionAccountModal').removeClass('hidden').addClass('flex');
        if (field === 'github') {
            setTimeout(() => {
                const el = document.getElementById('user_github_url');
                if (el) {
                    el.focus();
                    el.select();
                }
            }, 150);
        }
    }

    function closeDivisionAccountModal() {
        $('#divisionAccountModal').addClass('hidden').removeClass('flex');
    }

    function openGitRepoModal(projectIdOrBtn, projectName, currentUrl, mode = 'programmer') {
        let projectId, pName, cUrl, pMode;
        if (typeof projectIdOrBtn === 'object' && projectIdOrBtn !== null && projectIdOrBtn.dataset) {
            projectId = projectIdOrBtn.dataset.projectId;
            pName = projectIdOrBtn.dataset.projectName;
            cUrl = projectIdOrBtn.dataset.currentUrl;
            pMode = projectIdOrBtn.dataset.mode || 'programmer';
        } else {
            projectId = projectIdOrBtn;
            pName = projectName;
            cUrl = currentUrl;
            pMode = mode;
        }

        const form = document.getElementById('gitRepoForm');
        const nameEl = document.getElementById('gitRepoProjectName');
        const inputEl = document.getElementById('git_repository_url');

        if (form) {
            form.action = `/user/projects/${projectId}/repository`;
        }
        if (nameEl) {
            nameEl.textContent = pName;
        }
        if (inputEl) {
            inputEl.value = cUrl || '';
        }

        const isUiUx = (pMode === 'uiux');

        // Dynamic elements
        const headerEl = document.getElementById('repoModalHeader');
        const iconWrapperEl = document.getElementById('repoModalIconWrapper');
        const iconEl = document.getElementById('repoModalIcon');
        const titleEl = document.getElementById('repoModalTitle');
        const subtitleEl = document.getElementById('repoModalSubtitle');
        const labelEl = document.getElementById('repoModalLabel');
        const inputIconEl = document.getElementById('repoModalInputIcon');
        const helpEl = document.getElementById('repoModalHelp');
        const submitBtnEl = document.getElementById('repoModalSubmitBtn');
        const submitTextEl = document.getElementById('repoModalSubmitText');

        if (isUiUx) {
            if (headerEl) headerEl.className = 'bg-purple-900 text-white px-6 py-5 flex justify-between items-center border-b border-purple-800';
            if (iconWrapperEl) iconWrapperEl.className = 'w-10 h-10 rounded-xl bg-purple-800 text-white flex items-center justify-center text-xl shadow-sm border border-purple-700';
            if (iconEl) iconEl.className = 'fa-brands fa-figma';
            if (titleEl) titleEl.textContent = 'Tautkan Project Figma';
            if (subtitleEl) subtitleEl.textContent = 'Khusus Divisi UI/UX & Designer';
            if (labelEl) labelEl.innerHTML = 'URL File / Project Figma <span class="text-red-500">*</span>';
            if (inputIconEl) inputIconEl.className = 'fa-brands fa-figma text-purple-600 text-sm';
            if (inputEl) inputEl.placeholder = 'https://www.figma.com/design/... atau link prototype file';
            if (helpEl) helpEl.innerHTML = 'Masukkan tautan file atau prototype Figma. Pastikan share permission diset ke <strong>Anyone with the link can view</strong> agar pembimbing dapat meninjau desain.';
            if (submitBtnEl) submitBtnEl.className = 'px-5 py-2 bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold rounded-xl shadow-sm transition flex items-center gap-2';
            if (submitTextEl) submitTextEl.textContent = 'Simpan Link Figma';
        } else {
            if (headerEl) headerEl.className = 'bg-gray-900 text-white px-6 py-5 flex justify-between items-center border-b border-gray-800';
            if (iconWrapperEl) iconWrapperEl.className = 'w-10 h-10 rounded-xl bg-gray-800 text-white flex items-center justify-center text-xl shadow-sm border border-gray-700';
            if (iconEl) iconEl.className = 'fa-brands fa-github';
            if (titleEl) titleEl.textContent = 'Tautkan Git Repository';
            if (subtitleEl) subtitleEl.textContent = 'Khusus Divisi Programmer & Developer';
            if (labelEl) labelEl.innerHTML = 'URL Repository Git <span class="text-red-500">*</span>';
            if (inputIconEl) inputIconEl.className = 'fa-brands fa-github text-sm';
            if (inputEl) inputEl.placeholder = 'https://github.com/username/nama-project';
            if (helpEl) helpEl.innerHTML = 'Masukkan tautan repository GitHub, GitLab, atau Bitbucket. Pastikan repo bersifat <strong>Public</strong> atau pembimbing telah di-invite sebagai collaborator agar kode bisa direview.';
            if (submitBtnEl) submitBtnEl.className = 'px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-sm transition flex items-center gap-2';
            if (submitTextEl) submitTextEl.textContent = 'Simpan Repository';
        }

        $('#gitRepoModal').removeClass('hidden').addClass('flex');
        setTimeout(() => inputEl && inputEl.focus(), 100);
    }

    function closeGitRepoModal() {
        $('#gitRepoModal').addClass('hidden').removeClass('flex');
    }

    function openRevisionModal(projectIdOrBtn, projectName, currentNotes) {
        let projectId, pName, cNotes;
        if (typeof projectIdOrBtn === 'object' && projectIdOrBtn !== null && projectIdOrBtn.dataset) {
            projectId = projectIdOrBtn.dataset.projectId;
            pName = projectIdOrBtn.dataset.projectName;
            cNotes = projectIdOrBtn.dataset.currentNotes;
        } else {
            projectId = projectIdOrBtn;
            pName = projectName;
            cNotes = currentNotes;
        }

        const form = document.getElementById('revisionForm');
        const nameEl = document.getElementById('revisionProjectName');
        const inputEl = document.getElementById('intern_revision_notes');

        if (form) {
            form.action = `/user/projects/${projectId}/revision-note`;
        }
        if (nameEl) {
            nameEl.textContent = pName;
        }
        if (inputEl) {
            inputEl.value = cNotes || '';
        }

        $('#revisionModal').removeClass('hidden').addClass('flex');
        setTimeout(() => inputEl && inputEl.focus(), 100);
    }

    function closeRevisionModal() {
        $('#revisionModal').addClass('hidden').removeClass('flex');
    }

    let userSocialIndex = parseInt(document.getElementById('user-social-container')?.getAttribute('data-initial-index') || '0', 10);

    function addUserSocialRow() {
        const container = document.getElementById('user-social-container');
        const noMsg = document.getElementById('no-user-social-msg');
        if (noMsg) noMsg.style.display = 'none';

        const row = document.createElement('div');
        row.className = 'user-social-row p-2.5 bg-gray-50 border border-gray-200 rounded-xl flex flex-col sm:flex-row gap-2 items-start sm:items-center';
        row.innerHTML = `
                <div class="w-full sm:w-1/4">
                    <select name="social_media_links[\${userSocialIndex}][platform]" class="w-full p-2 text-xs border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
                        <option value="Instagram">Instagram</option>
                        <option value="TikTok">TikTok</option>
                        <option value="LinkedIn">LinkedIn</option>
                        <option value="YouTube">YouTube</option>
                        <option value="Facebook">Facebook</option>
                        <option value="Twitter/X">Twitter / X</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
                <div class="w-full sm:w-1/3">
                    <input type="text" name="social_media_links[\${userSocialIndex}][username]" placeholder="@username" class="w-full p-2 text-xs border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
                </div>
                <div class="w-full sm:w-2/5 flex items-center gap-1">
                    <input type="url" name="social_media_links[\${userSocialIndex}][url]" placeholder="https://..." class="w-full p-2 text-xs border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
                    <button type="button" onclick="removeUserSocialRow(this)" class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg shrink-0" title="Hapus">
                        <svg class="w-3.5 h-3.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>
            `;
        container.appendChild(row);
        userSocialIndex++;
    }

    function removeUserSocialRow(btn) {
        const row = btn.closest('.user-social-row');
        if (row) row.remove();
        const container = document.getElementById('user-social-container');
        if (container && container.querySelectorAll('.user-social-row').length === 0) {
            const noMsg = document.getElementById('no-user-social-msg');
            if (noMsg) noMsg.style.display = 'block';
        }
    }

    function toggleTaskPasswordVisibility(fieldId, btn) {
        const input = document.getElementById(fieldId);
        if (!input) return;
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            if (icon) {
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        } else {
            input.type = 'password';
            if (icon) {
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    }
</script>
@endsection