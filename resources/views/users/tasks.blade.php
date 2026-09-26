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
            <span class="inline-flex items-center gap-1 sm:gap-1.5 px-2.5 sm:px-3 py-1 rounded-full text-[11px] sm:text-xs font-bold bg-indigo-100 text-indigo-800 border border-indigo-200 shadow-2xs whitespace-nowrap shrink-0">
                <svg class="w-3.5 h-3.5 text-indigo-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <span>Divisi: {{ $user->intern->division->name ?? 'Umum' }}</span>
            </span>
            @if($user->intern?->school)
            <span class="inline-flex items-center gap-1 sm:gap-1.5 px-2.5 sm:px-3 py-1 rounded-full text-[11px] sm:text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200 whitespace-nowrap shrink-0">
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

    <!-- Card Terpadu: Kredensial & Folder Google Drive Kerja Divisi -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-gray-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4 sm:gap-5">
        <div class="space-y-3">
            <div class="flex items-start sm:items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center flex-shrink-0 mt-0.5 sm:mt-0">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                    </svg>
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h3 class="text-xs sm:text-sm font-bold text-gray-900">Kredensial Kerja & Workspace Divisi</h3>
                        @if(!empty($internAccount?->gdrive_url))
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                            Drive Aktif
                        </span>
                        @else
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                            Drive Belum Ditautkan
                        </span>
                        @endif
                    </div>
                    <p class="text-[11px] text-gray-500 mt-0.5">
                        Kelola kredensial akun kerja divisi dan akses folder Google Drive penugasan resmi
                    </p>
                </div>
            </div>

            <!-- Status Kredensial Ringkas -->
            <div class="flex flex-wrap items-center gap-2 text-xs">
                @if($isUserProg)
                <div class="flex items-center gap-1.5 px-2.5 py-1 bg-gray-50 border border-gray-200 rounded-lg text-gray-700">
                    <svg class="w-3.5 h-3.5 fill-current text-gray-900" viewBox="0 0 24 24">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.53 1.032 1.53 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" />
                    </svg>
                    <span>{{ !empty($internAccount?->github_url) ? 'GitHub Terhubung' : 'GitHub Belum Ada' }}</span>
                </div>
                <div class="flex items-center gap-1.5 px-2.5 py-1 bg-gray-50 border border-gray-200 rounded-lg text-gray-700">
                    <svg class="w-3.5 h-3.5 fill-current text-red-500" viewBox="0 0 24 24">
                        <path d="M12.24 10.285V14.4h6.806c-.275 1.765-2.056 5.174-6.806 5.174-4.095 0-7.439-3.389-7.439-7.574s3.344-7.574 7.439-7.574c2.33 0 3.891.989 4.785 1.849l3.254-3.138C18.189 1.186 15.479 0 12.24 0c-6.635 0-12 5.365-12 12s5.365 12 12 12c6.926 0 11.52-4.869 11.52-11.726 0-.788-.085-1.39-.189-1.989H12.24z" />
                    </svg>
                    <span>{{ !empty($internAccount?->gmail_account) ? $internAccount->gmail_account : 'Gmail Belum Ada' }}</span>
                </div>
                @elseif($isUserUiUx)
                <div class="flex items-center gap-1.5 px-2.5 py-1 bg-gray-50 border border-gray-200 rounded-lg text-gray-700">
                    <svg class="w-3.5 h-3.5 fill-current text-purple-600" viewBox="0 0 24 24">
                        <path d="M15.85 0H8.15C5.86 0 4 1.86 4 4.15c0 2.29 1.86 4.15 4.15 4.15h3.55v3.4H8.15C5.86 11.7 4 13.56 4 15.85 4 18.14 5.86 20 8.15 20c2.29 0 4.15-1.86 4.15-4.15v-4.15h3.55c2.29 0 4.15-1.86 4.15-4.15C20 1.86 18.14 0 15.85 0zM8.15 5.85c-.94 0-1.7-.76-1.7-1.7s.76-1.7 1.7-1.7h3.55v3.4H8.15zm0 11.7c-.94 0-1.7-.76-1.7-1.7s.76-1.7 1.7-1.7h3.55v1.7c0 .94-.76 1.7-1.7 1.7zm3.55-7.55H8.15c-.94 0-1.7-.76-1.7-1.7s.76-1.7 1.7-1.7h3.55v3.4zm4.15-1.7c-.94 0-1.7-.76-1.7-1.7s.76-1.7 1.7-1.7c.94 0 1.7.76 1.7 1.7s-.76 1.7-1.7 1.7zm0-5.85c-.94 0-1.7-.76-1.7-1.7s.76-1.7 1.7-1.7c.94 0 1.7.76 1.7 1.7s-.76 1.7-1.7 1.7z" />
                    </svg>
                    <span>{{ !empty($internAccount?->figma_url) ? 'Figma Terhubung' : 'Figma Belum Ada' }}</span>
                </div>
                @elseif($isUserSosmed)
                <div class="flex items-center gap-1.5 px-2.5 py-1 bg-gray-50 border border-gray-200 rounded-lg text-gray-700">
                    <svg class="w-3.5 h-3.5 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                    </svg>
                    <span>{{ count($userSocialLinks) }} Akun Medsos Terdaftar</span>
                </div>
                @endif

                @if(!empty($internAccount?->notes))
                <div class="flex items-center gap-1.5 px-2.5 py-1 bg-amber-50 border border-amber-200 rounded-lg text-amber-900 text-[11px]">
                    <svg class="w-3.5 h-3.5 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span class="truncate max-w-xs">{{ Str::limit($internAccount->notes, 35) }}</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Tombol Aksi: Menuju Edit & Ke Drive -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full md:w-auto md:flex-shrink-0 pt-3 md:pt-0 border-t md:border-t-0 border-gray-100">
            @if(!empty($internAccount?->gdrive_url))
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <a href="{{ $internAccount->gdrive_url }}" target="_blank" rel="noopener noreferrer"
                    class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition shadow-sm">
                    <svg class="w-4 h-4 fill-current text-amber-300 shrink-0" viewBox="0 0 24 24">
                        <path d="M7.71 3.5L1.15 15l3.43 6 6.55-11.5M9.73 15L6.3 21h13.12l3.43-6M22.85 15l-6.57-11.5H9.71l6.57 11.5" />
                    </svg>
                    <span>Buka Google Drive</span>
                    <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                </a>
                <button type="button" onclick="navigator.clipboard.writeText('{{ $internAccount->gdrive_url }}'); alert('Link Google Drive berhasil disalin ke clipboard!');"
                    title="Salin Link Google Drive"
                    class="inline-flex items-center justify-center p-2.5 bg-gray-50 hover:bg-gray-100 border border-gray-300 text-gray-700 rounded-xl text-xs transition shrink-0">
                    <svg class="w-3.5 h-3.5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
                    </svg>
                </button>
            </div>
            @endif

            <button type="button" onclick="openDivisionAccountModal()"
                class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-3.5 py-2 bg-white hover:bg-gray-50 border border-gray-300 text-gray-800 rounded-xl text-xs font-bold transition shadow-sm">
                <svg class="w-3.5 h-3.5 text-indigo-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                <span>Edit Kredensial</span>
            </button>
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
                            <span class="text-[10px] font-extrabold uppercase tracking-wider text-purple-700 bg-purple-100 px-2 py-0.5 rounded">
                                Tugas dari Pembimbing
                            </span>
                            <h3 class="text-sm sm:text-base font-bold text-gray-900 leading-snug">
                                {{ $task->project?->nameProject->name ?? 'Tugas & Arahan Kerja' }}
                            </h3>
                        </div>
                        @if($task->status === 'in_progress')
                        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 flex items-center gap-1.5 flex-shrink-0 self-start sm:self-auto">
                            <span class="w-2 h-2 rounded-full bg-emerald-600 animate-pulse"></span>
                            <span>Sedang Dikerjakan</span>
                        </span>
                        @else
                        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-200 flex items-center gap-1.5 flex-shrink-0 self-start sm:self-auto">
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
                        <div class="text-[11px] font-bold text-purple-900 flex items-center gap-1.5">
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
                    <div class="p-2.5 bg-gray-50 border border-gray-200 rounded-xl text-[11px] text-gray-600">
                        <span class="font-bold text-gray-700 block mb-0.5">Catatan Pengajuan:</span>
                        <p class="whitespace-pre-line text-gray-700">{{ $task->notes }}</p>
                    </div>
                    @endif

                    {{-- WIDGET LINK PROJECT SESUAI DIVISI (GIT HANYA UNTUK PROGRAMMER, FIGMA HANYA UNTUK UI/UX) --}}
                    @if($isUserProg)
                    @php
                    $taskGitRepo = $task->project?->repository_url;
                    $projId = $task->project?->id;
                    $projName = $task->project?->nameProject->name ?? 'Tugas Pembimbing';
                    @endphp
                    <div class="p-3 bg-white rounded-xl border border-purple-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 text-xs shadow-2xs">
                        <div class="flex items-center gap-2 min-w-0">
                            <div class="w-7 h-7 rounded-lg bg-gray-900 text-white flex items-center justify-center flex-shrink-0 text-sm">
                                <i class="fa-brands fa-github"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="font-bold text-gray-800 text-[11px]">Git Repository Tugas:</div>
                                @if(!empty($taskGitRepo))
                                <a href="{{ $taskGitRepo }}" target="_blank" rel="noopener noreferrer"
                                    class="text-indigo-600 hover:text-indigo-800 hover:underline text-[11px] font-medium flex items-center gap-1 truncate max-w-full sm:max-w-xs"
                                    title="{{ $taskGitRepo }}">
                                    <span class="truncate">{{ $taskGitRepo }}</span>
                                    <i class="fa-solid fa-arrow-up-right-from-square text-[9px] flex-shrink-0"></i>
                                </a>
                                @else
                                <span class="text-gray-400 text-[11px]">Belum ditautkan link repo</span>
                                @endif
                            </div>
                        </div>
                        @if($projId)
                        <button type="button"
                            onclick="openGitRepoModal('{{ $projId }}', '{{ addslashes($projName) }}', '{{ addslashes($taskGitRepo ?? '') }}', 'programmer')"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-1 px-3 py-1.5 bg-gray-50 hover:bg-gray-100 border border-gray-300 text-gray-800 text-[11px] font-bold rounded-lg transition shadow-xs flex-shrink-0">
                            <i class="fa-solid fa-link text-indigo-600 text-[10px]"></i>
                            <span>{{ !empty($taskGitRepo) ? 'Ubah Link Repo' : '+ Tautkan Repo' }}</span>
                        </button>
                        @endif
                    </div>
                    @elseif($isUserUiUx)
                    @php
                    $taskFigmaLink = $task->project?->repository_url ?? '';
                    $projId = $task->project?->id;
                    $projName = $task->project?->nameProject->name ?? 'Tugas Pembimbing';
                    @endphp
                    <div class="p-3 bg-white rounded-xl border border-purple-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 text-xs shadow-2xs">
                        <div class="flex items-center gap-2 min-w-0">
                            <div class="w-7 h-7 rounded-lg bg-purple-600 text-white flex items-center justify-center flex-shrink-0 text-sm">
                                <i class="fa-brands fa-figma"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="font-bold text-gray-800 text-[11px]">Project Figma Tugas:</div>
                                @if(!empty($taskFigmaLink))
                                <a href="{{ $taskFigmaLink }}" target="_blank" rel="noopener noreferrer"
                                    class="text-purple-600 hover:text-purple-800 hover:underline text-[11px] font-medium flex items-center gap-1 truncate max-w-full sm:max-w-xs"
                                    title="{{ $taskFigmaLink }}">
                                    <span class="truncate">{{ $taskFigmaLink }}</span>
                                    <i class="fa-solid fa-arrow-up-right-from-square text-[9px] flex-shrink-0"></i>
                                </a>
                                @else
                                <span class="text-gray-400 text-[11px]">Belum ditautkan link figma</span>
                                @endif
                            </div>
                        </div>
                        @if($projId)
                        <button type="button"
                            onclick="openGitRepoModal('{{ $projId }}', '{{ addslashes($projName) }}', '{{ addslashes($taskFigmaLink ?? '') }}', 'uiux')"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-1 px-3 py-1.5 bg-purple-50 hover:bg-purple-100 border border-purple-300 text-purple-800 text-[11px] font-bold rounded-lg transition shadow-xs flex-shrink-0">
                            <i class="fa-solid fa-link text-purple-600 text-[10px]"></i>
                            <span>{{ !empty($taskFigmaLink) ? 'Ubah Link Figma' : '+ Tautkan Figma' }}</span>
                        </button>
                        @endif
                    </div>
                    @endif
                </div>

                <div class="pt-3 border-t border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-1 text-xs text-gray-500">
                    <span class="text-[11px]">Pembimbing: <strong>{{ $mentorName }}</strong></span>
                    <span class="text-[10px] text-gray-400">
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
                            <span class="text-[10px] font-extrabold uppercase tracking-wider text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded">
                                Tim: {{ $project->team ?? 'Divisi' }}
                            </span>
                            <h3 class="text-sm sm:text-base font-bold text-gray-900 leading-snug">
                                {{ $project->nameProject->name ?? 'Project Tanpa Judul' }}
                            </h3>
                        </div>

                        @if($hasRevision)
                        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-900 border border-amber-300 flex items-center gap-1.5 flex-shrink-0 self-start sm:self-auto shadow-xs">
                            <span class="w-2 h-2 rounded-full bg-amber-600 animate-pulse"></span>
                            <span>Perlu Perbaikan</span>
                        </span>
                        @elseif($isReady)
                        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 flex items-center gap-1.5 flex-shrink-0 self-start sm:self-auto shadow-xs">
                            <i class="fa-solid fa-circle-check text-emerald-600 text-[10px]"></i>
                            <span>Sudah Presentasi</span>
                        </span>
                        @else
                        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-100 text-blue-800 border border-blue-200 flex items-center gap-1 flex-shrink-0 self-start sm:self-auto">
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
                        <span class="font-bold text-gray-900 block mb-1 text-[11px]">Deskripsi & Arahan:</span>
                        {{ $project->description ?: 'Tidak ada deskripsi detail pada penugasan ini.' }}
                    </div>

                    <!-- KOTAK CATATAN REVISI PROJECT (DIISI SENDIRI OLEH PEMAGANG) -->
                    @if($hasRevision)
                    @php
                    $internRevNotes = $review?->performance_notes;
                    @endphp
                    <div class="p-3.5 sm:p-4 bg-amber-50/95 border-2 border-amber-300 rounded-2xl space-y-3 shadow-xs">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-amber-200 pb-2">
                            <div class="flex items-center gap-2 text-xs font-bold text-amber-950">
                                <div class="w-7 h-7 rounded-lg bg-amber-200 text-amber-800 flex items-center justify-center text-sm flex-shrink-0">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                </div>
                                <div>
                                    <span class="block leading-none">Project Memerlukan Revisi</span>
                                    <span class="text-[10px] font-medium text-amber-800">Ditinjau oleh {{ $pMentorName }}</span>
                                </div>
                            </div>
                            <span class="text-[10px] text-amber-800 font-semibold bg-amber-100 px-2.5 py-0.5 rounded-full border border-amber-200 self-start sm:self-auto">
                                {{ $review->resolved_at ? $review->resolved_at->format('d M Y, H:i') : ($review->updated_at ? $review->updated_at->format('d M Y, H:i') : '-') }}
                            </span>
                        </div>

                        {{-- Bagian Catatan Revisi yang Diisi oleh Pemagang --}}
                        <div class="p-3 sm:p-3.5 bg-white rounded-xl border border-amber-200 space-y-2">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <div class="text-xs font-bold text-gray-900 flex items-center gap-1.5">
                                    <i class="fa-solid fa-clipboard-list text-amber-600 flex-shrink-0"></i>
                                    <span>Poin Revisi yang Harus Dikerjakan (Diisi Pemagang):</span>
                                </div>
                                <button type="button"
                                    onclick="openRevisionModal('{{ $project->id }}', '{{ addslashes($project->nameProject->name ?? 'Project') }}', '{{ addslashes($internRevNotes ?? '') }}')"
                                    class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer">
                                    <i class="fa-solid fa-pen-to-square text-[11px]"></i>
                                    <span>{{ !empty($internRevNotes) ? 'Edit Catatan Revisi' : '+ Isi Catatan Revisi' }}</span>
                                </button>
                            </div>

                            @if(!empty($internRevNotes))
                            <div class="text-xs text-gray-800 whitespace-pre-line leading-relaxed font-medium bg-amber-50/50 p-3 rounded-lg border border-amber-100">
                                {{ $internRevNotes }}
                            </div>
                            @else
                            <div class="p-3 sm:p-3.5 bg-amber-50/60 rounded-xl border border-dashed border-amber-300 text-center space-y-1.5">
                                <p class="text-xs text-amber-900 font-bold">
                                    Belum ada catatan revisi yang diisi.
                                </p>
                                <p class="text-[11px] text-amber-700 max-w-md mx-auto leading-relaxed">
                                    Silakan klik tombol <strong>"+ Isi Catatan Revisi"</strong> di atas untuk mencatat rincian perbaikan apa saja yang perlu Anda kerjakan sesuai hasil presentasi.
                                </p>
                            </div>
                            @endif
                        </div>

                        <div class="text-[11px] text-amber-800 flex items-center justify-between gap-1 pt-0.5">
                            <span class="flex items-center gap-1">
                                <i class="fa-solid fa-circle-info text-amber-600 text-[10px] flex-shrink-0"></i>
                                <span>Setelah menyelesaikan poin revisi di atas, ajukan jadwal presentasi ulang melalui menu <strong>Raise Hand</strong> di Dashboard.</span>
                            </span>
                        </div>
                    </div>
                    @endif

                    {{-- WIDGET LINK PROJECT SESUAI DIVISI (GIT HANYA UNTUK PROGRAMMER, FIGMA HANYA UNTUK UI/UX) --}}
                    @if($isUserProg)
                    @php
                    $projGitUrl = $project->repository_url;
                    @endphp
                    <div class="p-3 bg-white rounded-xl border border-gray-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 text-xs shadow-2xs">
                        <div class="flex items-center gap-2 min-w-0">
                            <div class="w-7 h-7 rounded-lg bg-gray-900 text-white flex items-center justify-center flex-shrink-0 text-sm">
                                <i class="fa-brands fa-github"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="font-bold text-gray-800 text-[11px]">Git Repository:</div>
                                @if(!empty($projGitUrl))
                                <a href="{{ $projGitUrl }}" target="_blank" rel="noopener noreferrer"
                                    class="text-indigo-600 hover:text-indigo-800 hover:underline text-[11px] font-medium flex items-center gap-1 truncate max-w-full sm:max-w-xs"
                                    title="{{ $projGitUrl }}">
                                    <span class="truncate">{{ $projGitUrl }}</span>
                                    <i class="fa-solid fa-arrow-up-right-from-square text-[9px] flex-shrink-0"></i>
                                </a>
                                @else
                                <span class="text-gray-400 text-[11px]">Belum ditautkan link repo</span>
                                @endif
                            </div>
                        </div>
                        <button type="button"
                            onclick="openGitRepoModal('{{ $project->id }}', '{{ addslashes($project->nameProject->name ?? 'Project') }}', '{{ addslashes($projGitUrl ?? '') }}', 'programmer')"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-1 px-3 py-1.5 bg-gray-50 hover:bg-gray-100 border border-gray-300 text-gray-800 text-[11px] font-bold rounded-lg transition shadow-xs flex-shrink-0">
                            <i class="fa-solid fa-link text-indigo-600 text-[10px]"></i>
                            <span>{{ !empty($projGitUrl) ? 'Ubah Link Repo' : '+ Tautkan Repo' }}</span>
                        </button>
                    </div>
                    @elseif($isUserUiUx)
                    @php
                    $projFigmaUrl = $project->repository_url ?? '';
                    @endphp
                    <div class="p-3 bg-white rounded-xl border border-purple-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 text-xs shadow-2xs">
                        <div class="flex items-center gap-2 min-w-0">
                            <div class="w-7 h-7 rounded-lg bg-purple-600 text-white flex items-center justify-center flex-shrink-0 text-sm">
                                <i class="fa-brands fa-figma"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="font-bold text-gray-800 text-[11px]">Project Figma:</div>
                                @if(!empty($projFigmaUrl))
                                <a href="{{ $projFigmaUrl }}" target="_blank" rel="noopener noreferrer"
                                    class="text-purple-600 hover:text-purple-800 hover:underline text-[11px] font-medium flex items-center gap-1 truncate max-w-full sm:max-w-xs"
                                    title="{{ $projFigmaUrl }}">
                                    <span class="truncate">{{ $projFigmaUrl }}</span>
                                    <i class="fa-solid fa-arrow-up-right-from-square text-[9px] flex-shrink-0"></i>
                                </a>
                                @else
                                <span class="text-gray-400 text-[11px]">Belum ditautkan link figma</span>
                                @endif
                            </div>
                        </div>
                        <button type="button"
                            onclick="openGitRepoModal('{{ $project->id }}', '{{ addslashes($project->nameProject->name ?? 'Project') }}', '{{ addslashes($projFigmaUrl ?? '') }}', 'uiux')"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-1 px-3 py-1.5 bg-purple-50 hover:bg-purple-100 border border-purple-300 text-purple-800 text-[11px] font-bold rounded-lg transition shadow-xs flex-shrink-0">
                            <i class="fa-solid fa-link text-purple-600 text-[10px]"></i>
                            <span>{{ !empty($projFigmaUrl) ? 'Ubah Link Figma' : '+ Tautkan Figma' }}</span>
                        </button>
                    </div>
                    @endif
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
            <p class="text-[11px] text-gray-500 max-w-sm mx-auto">
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
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-purple-700 bg-purple-100 px-2 py-0.5 rounded">
                            Tugas Pembimbing
                        </span>
                        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 flex items-center gap-1 flex-shrink-0 self-start sm:self-auto">
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Selesai (Valid)</span>
                        </span>
                    </div>
                    @if(!empty($cTask->admin_response))
                    <div class="p-3 bg-white rounded-xl border border-gray-200 text-xs text-gray-800">
                        <span class="font-bold text-gray-700 block mb-1 text-[11px]">Tugas yang Dikerjakan:</span>
                        <p class="whitespace-pre-line text-gray-800 font-medium leading-relaxed">{{ $cTask->admin_response }}</p>
                    </div>
                    @endif
                </div>
                <div class="pt-2 border-t border-gray-200 flex flex-col sm:flex-row sm:items-center justify-between gap-1 text-[11px] text-gray-500">
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
                            <span class="text-[10px] font-extrabold uppercase tracking-wider text-gray-600 bg-gray-200 px-2 py-0.5 rounded">
                                Tim: {{ $project->team ?? 'Divisi' }}
                            </span>
                            <h3 class="text-sm sm:text-base font-bold text-gray-900 leading-snug">
                                {{ $project->nameProject->name ?? 'Project Tanpa Judul' }}
                            </h3>
                        </div>

                        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 flex items-center gap-1 flex-shrink-0 self-start sm:self-auto">
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Selesai (Valid)</span>
                        </span>
                    </div>

                    <div class="p-3 bg-white rounded-xl border border-gray-200 text-xs text-gray-700 leading-relaxed">
                        <span class="font-bold text-gray-900 block mb-1 text-[11px]">Deskripsi Project:</span>
                        {{ $project->description ?: 'Tidak ada deskripsi detail pada penugasan ini.' }}
                    </div>

                    @if($isUserProg && !empty($project->repository_url))
                    <div class="p-2.5 bg-white rounded-xl border border-gray-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                        <div class="flex items-center gap-2 truncate min-w-0">
                            <i class="fa-brands fa-github text-gray-800 flex-shrink-0"></i>
                            <span class="font-medium text-gray-700 truncate text-[11px]">{{ $project->repository_url }}</span>
                        </div>
                        <a href="{{ $project->repository_url }}" target="_blank" rel="noopener noreferrer"
                            class="w-full sm:w-auto justify-center px-2.5 py-1.5 sm:py-1 bg-gray-900 hover:bg-black text-white rounded-lg text-[10px] font-bold flex items-center gap-1 flex-shrink-0">
                            <span>Buka Repo</span>
                            <i class="fa-solid fa-arrow-up-right-from-square text-[8px]"></i>
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
                            <span class="font-medium text-purple-900 truncate text-[11px]">{{ $completedFigma }}</span>
                        </div>
                        <a href="{{ $completedFigma }}" target="_blank" rel="noopener noreferrer"
                            class="w-full sm:w-auto justify-center px-2.5 py-1.5 sm:py-1 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-[10px] font-bold flex items-center gap-1 flex-shrink-0">
                            <span>Buka Figma</span>
                            <i class="fa-solid fa-arrow-up-right-from-square text-[8px]"></i>
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
                    <p class="text-[11px] sm:text-xs text-slate-400">Divisi: <span class="text-indigo-300 font-semibold">{{ $user->intern->division->name ?? 'Pemagang' }}</span></p>
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
                            <span>Akun & Kredensial Divisi Anda</span>
                        </h4>
                        <p class="text-[11px] sm:text-xs text-gray-500 mt-0.5">Perbarui link profil atau akun kerja yang Anda gunakan selama magang.</p>
                    </div>

                    {{-- Form Khusus Programmer --}}
                    @if($isUserProg)
                    <div class="space-y-3">
                        <div>
                            <label for="user_github_url" class="block text-xs font-semibold text-gray-700 mb-1">
                                <svg class="w-3.5 h-3.5 inline mr-1 fill-current text-gray-900" viewBox="0 0 24 24">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.53 1.032 1.53 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" />
                                </svg>
                                <span>Link Profil / Repository GitHub</span>
                            </label>
                            <input type="url" id="user_github_url" name="github_url"
                                value="{{ old('github_url', $internAccount?->github_url) }}"
                                placeholder="https://github.com/username-anda"
                                class="w-full p-2.5 border border-gray-300 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label for="user_gmail_account" class="block text-xs font-semibold text-gray-700 mb-1">
                                <svg class="w-3.5 h-3.5 inline mr-1 fill-current text-red-500" viewBox="0 0 24 24">
                                    <path d="M12.24 10.285V14.4h6.806c-.275 1.765-2.056 5.174-6.806 5.174-4.095 0-7.439-3.389-7.439-7.574s3.344-7.574 7.439-7.574c2.33 0 3.891.989 4.785 1.849l3.254-3.138C18.189 1.186 15.479 0 12.24 0c-6.635 0-12 5.365-12 12s5.365 12 12 12c6.926 0 11.52-4.869 11.52-11.726 0-.788-.085-1.39-.189-1.989H12.24z" />
                                </svg>
                                <span>Akun Gmail Kantor</span>
                            </label>
                            <input type="email" id="user_gmail_account" name="gmail_account"
                                value="{{ old('gmail_account', $internAccount?->gmail_account) }}"
                                placeholder="nama@gmail.com"
                                class="w-full p-2.5 border border-gray-300 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        @if(!empty($internAccount?->gmail_password))
                        <div class="p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-gray-600 flex flex-col sm:flex-row sm:items-center justify-between gap-1.5">
                            <span class="flex items-center">
                                <svg class="w-3.5 h-3.5 text-slate-500 mr-1.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                <span>Password Akun Kantor:</span>
                            </span>
                            <span class="font-mono bg-white px-2 py-0.5 border rounded text-indigo-700 font-semibold text-center sm:text-left">Tersimpan Aman di Kantor</span>
                        </div>
                        @endif
                    </div>
                    @endif

                    {{-- Form Khusus UI/UX Designer --}}
                    @if($isUserUiUx)
                    <div>
                        <label for="user_figma_url" class="block text-xs font-semibold text-gray-700 mb-1">
                            <svg class="w-3.5 h-3.5 inline mr-1 fill-current text-purple-600" viewBox="0 0 24 24">
                                <path d="M15.85 0H8.15C5.86 0 4 1.86 4 4.15c0 2.29 1.86 4.15 4.15 4.15h3.55v3.4H8.15C5.86 11.7 4 13.56 4 15.85 4 18.14 5.86 20 8.15 20c2.29 0 4.15-1.86 4.15-4.15v-4.15h3.55c2.29 0 4.15-1.86 4.15-4.15C20 1.86 18.14 0 15.85 0zM8.15 5.85c-.94 0-1.7-.76-1.7-1.7s.76-1.7 1.7-1.7h3.55v3.4H8.15zm0 11.7c-.94 0-1.7-.76-1.7-1.7s.76-1.7 1.7-1.7h3.55v1.7c0 .94-.76 1.7-1.7 1.7zm3.55-7.55H8.15c-.94 0-1.7-.76-1.7-1.7s.76-1.7 1.7-1.7h3.55v3.4zm4.15-1.7c-.94 0-1.7-.76-1.7-1.7s.76-1.7 1.7-1.7c.94 0 1.7.76 1.7 1.7s-.76 1.7-1.7 1.7zm0-5.85c-.94 0-1.7-.76-1.7-1.7s.76-1.7 1.7-1.7c.94 0 1.7.76 1.7 1.7s-.76 1.7-1.7 1.7z" />
                            </svg>
                            <span>Link Profil / Workspace / File Figma</span>
                        </label>
                        <input type="url" id="user_figma_url" name="figma_url"
                            value="{{ old('figma_url', $internAccount?->figma_url) }}"
                            placeholder="https://www.figma.com/@username atau link file"
                            class="w-full p-2.5 border border-gray-300 rounded-xl text-xs focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        <p class="text-[11px] text-gray-400 mt-1">Tautan workspace atau file canvas desain yang Anda kerjakan.</p>
                    </div>
                    @endif

                    {{-- Form Khusus Social Media / TikTok / Marketing --}}
                    @if($isUserSosmed)
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-xs font-semibold text-gray-700">
                                <svg class="w-3.5 h-3.5 inline mr-1 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                                </svg>
                                <span>Akun Media Sosial yang Dikelola</span>
                            </label>
                            <button type="button" onclick="addUserSocialRow()" class="text-xs font-semibold text-pink-600 hover:text-pink-800 bg-pink-50 hover:bg-pink-100 px-2.5 py-1 rounded-lg border border-pink-200 transition">
                                + Tambah Akun
                            </button>
                        </div>

                        <div id="user-social-container" data-initial-index="{{ count($userSocialLinks) }}" class="space-y-2 mb-2">
                            @forelse($userSocialLinks as $idx => $sLink)
                            <div class="user-social-row p-2.5 bg-gray-50 border border-gray-200 rounded-xl flex flex-col sm:flex-row gap-2 items-start sm:items-center">
                                <div class="w-full sm:w-1/4">
                                    <select name="social_media_links[{{ $idx }}][platform]" class="w-full p-2 text-xs border border-gray-300 rounded-lg bg-white">
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
                                        class="w-full p-2 text-xs border border-gray-300 rounded-lg">
                                </div>
                                <div class="w-full sm:w-2/5 flex items-center gap-1">
                                    <input type="url" name="social_media_links[{{ $idx }}][url]"
                                        value="{{ $sLink['url'] ?? '' }}"
                                        placeholder="https://..."
                                        class="w-full p-2 text-xs border border-gray-300 rounded-lg">
                                    <button type="button" onclick="removeUserSocialRow(this)" class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg shrink-0" title="Hapus">
                                        <svg class="w-3.5 h-3.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            @empty
                            <div id="no-user-social-msg" class="text-center py-3 text-xs text-gray-400 bg-gray-50 rounded-xl border border-dashed border-gray-200">
                                Belum ada akun yang didaftarkan. Klik tombol <strong>+ Tambah Akun</strong> di atas.
                            </div>
                            @endforelse
                        </div>
                    </div>
                    @endif

                    {{-- Form Umum (Desain Grafis, Videografer, Las, PM, dll) --}}
                    @if($isUserGeneral)
                    <div class="p-3.5 bg-indigo-50/50 rounded-xl border border-indigo-100 text-xs text-indigo-900">
                        <p class="font-bold mb-1 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                            </svg>
                            <span>Informasi Divisi:</span>
                        </p>
                        <p class="text-gray-600 text-[11px] leading-relaxed">
                            Anda dapat menyematkan link portofolio, akun kerja, atau keterangan tambahan pada kolom catatan di bawah ini.
                        </p>
                    </div>
                    @endif

                    {{-- Catatan / Catatan Portofolio Tambahan --}}
                    <div>
                        <label for="user_notes" class="block text-xs font-semibold text-gray-700 mb-1">
                            <svg class="w-3.5 h-3.5 inline mr-1 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>Catatan / Link Portofolio Tambahan (Canva, Drive, dll)</span>
                        </label>
                        <textarea id="user_notes" name="notes" rows="2"
                            placeholder="Tuliskan link portofolio atau catatan tambahan untuk mentor/admin..."
                            class="w-full p-2.5 border border-gray-300 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('notes', $internAccount?->notes) }}</textarea>
                    </div>

                    <div class="pt-2 flex flex-col sm:flex-row justify-end">
                        <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow transition">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                            </svg>
                            <span>Simpan Perubahan Akun</span>
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
@if($isUserProg || $isUserUiUx)
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
                    <p id="repoModalSubtitle" class="text-[11px] sm:text-xs text-gray-400 mt-0.5">Khusus Divisi Programmer & Developer</p>
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
                            class="w-full p-2.5 sm:p-3 pl-10 border border-gray-300 rounded-xl text-xs text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 font-medium">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <i id="repoModalInputIcon" class="fa-brands fa-github text-sm"></i>
                        </div>
                    </div>
                    <p id="repoModalHelp" class="text-[11px] text-gray-500 leading-relaxed pt-0.5">
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
@endif

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
                    <p class="text-[11px] sm:text-xs text-amber-100">Catat bagian yang telah Anda perbaiki sesuai arahan</p>
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
                        class="w-full p-2.5 sm:p-3 border border-gray-300 rounded-xl text-xs text-gray-900 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 font-medium leading-relaxed"></textarea>
                    <p class="text-[11px] text-gray-500 leading-relaxed pt-0.5">
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

    function openGitRepoModal(projectId, projectName, currentUrl, mode = 'programmer') {
        const form = document.getElementById('gitRepoForm');
        const nameEl = document.getElementById('gitRepoProjectName');
        const inputEl = document.getElementById('git_repository_url');

        if (form) {
            form.action = `/user/projects/${projectId}/repository`;
        }
        if (nameEl) {
            nameEl.textContent = projectName;
        }
        if (inputEl) {
            inputEl.value = currentUrl || '';
        }

        const isUiUx = (mode === 'uiux');

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

    function openRevisionModal(projectId, projectName, currentNotes) {
        const form = document.getElementById('revisionForm');
        const nameEl = document.getElementById('revisionProjectName');
        const inputEl = document.getElementById('intern_revision_notes');

        if (form) {
            form.action = `/user/projects/${projectId}/revision-note`;
        }
        if (nameEl) {
            nameEl.textContent = projectName;
        }
        if (inputEl) {
            inputEl.value = currentNotes || '';
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
                    <select name="social_media_links[\${userSocialIndex}][platform]" class="w-full p-2 text-xs border border-gray-300 rounded-lg bg-white">
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
                    <input type="text" name="social_media_links[\${userSocialIndex}][username]" placeholder="@username" class="w-full p-2 text-xs border border-gray-300 rounded-lg">
                </div>
                <div class="w-full sm:w-2/5 flex items-center gap-1">
                    <input type="url" name="social_media_links[\${userSocialIndex}][url]" placeholder="https://..." class="w-full p-2 text-xs border border-gray-300 rounded-lg">
                    <button type="button" onclick="removeUserSocialRow(this)" class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg" title="Hapus">
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
</script>
@endsection