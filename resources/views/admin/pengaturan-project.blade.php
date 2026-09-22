@extends('layouts.main')

@section('title', 'Pengaturan Project')

@section('contents')
@include('layouts.sidebar-pengaturan')

<!-- Main Content -->
<main class="ml-[32rem] mt-24 p-6">
    <!-- Header & Action Button -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2.5">
                <i class="fa-solid fa-folder-kanban text-blue-600"></i>
                Manage Project
            </h1>
            <p class="text-sm text-gray-600 mt-1">Kelola daftar project, penugasan tim/divisi, dan anggota magang.</p>
        </div>
        <!-- Button Tambah Project di Kanan Atas -->
        <button type="button" onclick="openAddProjectModal()"
            class="px-5 py-2.5 bg-gray-900 hover:bg-gray-800 text-white rounded-xl text-xs font-bold transition flex items-center gap-2 shadow-sm hover:shadow self-start sm:self-auto shrink-0">
            <i class="fa-solid fa-plus"></i>
            <span>Tambah Project</span>
        </button>
    </div>

    @if (session('success'))
    <div id="success-message"
        class="mb-5 bg-emerald-50 border border-emerald-400 text-emerald-800 px-4 py-3 rounded-xl relative flex items-center justify-between shadow-xs transition-opacity duration-300"
        role="alert">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
            <span class="font-medium text-sm">{{ session('success') }}</span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800">
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>
    </div>
    @endif

    @if ($errors->any())
    <div class="mb-5 bg-red-50 border border-red-400 text-red-800 px-4 py-3 rounded-xl shadow-xs" role="alert">
        <div class="font-bold text-sm mb-1 flex items-center gap-2">
            <i class="fa-solid fa-circle-exclamation text-red-600"></i>
            Terjadi Kesalahan:
        </div>
        <ul class="list-disc list-inside text-xs space-y-1">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Filter & Search Toolbar -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 mb-6 flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-3 w-full md:w-auto flex-1">
            <!-- Search Input -->
            <div class="relative w-full max-w-md">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </div>
                <input type="text" id="searchInput" placeholder="Cari nama project, tim, anggota, atau deskripsi..."
                    onkeyup="filterProjects()"
                    class="w-full text-xs pl-9 pr-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
            </div>

            <!-- Status Filter Pills -->
            <div class="hidden sm:flex items-center gap-1.5 p-1 bg-gray-100 rounded-xl text-xs font-semibold">
                <button type="button" onclick="filterStatus('all')" id="filter-status-all"
                    class="status-filter-btn px-3 py-1.5 rounded-lg transition bg-white text-gray-800 shadow-xs">
                    Semua
                </button>
                <button type="button" onclick="filterStatus('progress')" id="filter-status-progress"
                    class="status-filter-btn px-3 py-1.5 rounded-lg transition text-gray-600 hover:text-gray-900">
                    Dikerjakan
                </button>
                <button type="button" onclick="filterStatus('done')" id="filter-status-done"
                    class="status-filter-btn px-3 py-1.5 rounded-lg transition text-gray-600 hover:text-gray-900">
                    Selesai
                </button>
            </div>
        </div>

        <!-- Total Info Badge -->
        <div class="text-xs text-gray-500 font-medium shrink-0" id="projectCountInfo">
            Total: <strong class="text-gray-800" id="totalFilteredProjects">{{ count($projects) }}</strong> Project
        </div>
    </div>

    <!-- Project Items (Grid Layout: Mengubah dari Flex ke Grid) -->
    <div id="projectsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 mb-6">
        @forelse ($projects as $index => $project)
        @php
        $memberNames = $project->members->map(function($m) {
        return $m->user?->profile?->full_name ?? '';
        })->filter()->implode(', ');
        $memberSchools = $project->members->map(function($m) {
        return $m->school?->name ?? '';
        })->filter()->implode(', ');
        $projectNameText = $project->nameProject->name ?? 'Project #' . $project->id;
        @endphp
        <div class="project-card bg-white rounded-2xl border border-gray-200 shadow-xs hover:shadow-md transition-all duration-200 flex flex-col justify-between overflow-hidden p-5"
            data-id="{{ $project->id }}"
            data-name="{{ strtolower($projectNameText) }}"
            data-team="{{ strtolower($project->team) }}"
            data-desc="{{ strtolower($project->description ?? '') }}"
            data-members="{{ strtolower($memberNames . ' ' . $memberSchools) }}"
            data-status="{{ $project->status == 'done' ? 'done' : 'progress' }}">

            <!-- Card Top: Checkbox & Status & Action Buttons -->
            <div>
                <div class="flex items-center justify-between gap-2 pb-3 mb-3 border-b border-gray-100">
                    <!-- Toggle Status (Checkbox + Badge) -->
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox"
                            class="project-status-checkbox w-4 h-4 text-emerald-600 rounded border-gray-300 focus:ring-emerald-500 transition cursor-pointer"
                            data-id="{{ $project->id }}"
                            onchange="updateProjectStatus({{ $project->id }}, this.checked)"
                            {{ $project->status == 'done' ? 'checked' : '' }}>
                        @if ($project->status == 'done')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                            <i class="fa-solid fa-check text-[10px]"></i> Selesai
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-800 border border-amber-200">
                            <i class="fa-regular fa-hourglass-half text-[10px]"></i> Dikerjakan
                        </span>
                        @endif
                    </label>

                    <!-- Actions (Edit & Delete) -->
                    <div class="flex items-center gap-1">
                        <button type="button"
                            class="w-7 h-7 rounded-lg text-gray-500 hover:text-blue-600 hover:bg-blue-50 flex items-center justify-center transition"
                            title="Edit Project"
                            data-project="{{ json_encode([
                                        'id' => $project->id,
                                        'name_project_id' => $project->name_project_id,
                                        'team' => $project->team,
                                        'description' => $project->description,
                                        'member_ids' => $project->members->pluck('id')->toArray()
                                    ]) }}"
                            onclick="editProject(event, JSON.parse(this.dataset.project))">
                            <i class="fa-solid fa-pen-to-square text-xs"></i>
                        </button>
                        <button type="button"
                            class="w-7 h-7 rounded-lg text-gray-500 hover:text-red-600 hover:bg-red-50 flex items-center justify-center transition"
                            title="Hapus Project"
                            onclick="openDeleteModal({{ $project->id }}, event)">
                            <i class="fa-solid fa-trash-can text-xs"></i>
                        </button>
                    </div>
                </div>

                <!-- Team / Divisi Badge -->
                <div class="mb-2">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                        <i class="fa-solid fa-users text-[10px]"></i>
                        {{ $project->team }}
                    </span>
                </div>

                <!-- Project Title -->
                <h3 class="text-sm font-bold text-gray-900 leading-snug mb-2 line-clamp-2" title="{{ $projectNameText }}">
                    {{ $projectNameText }}
                </h3>

                <!-- Description -->
                <p class="text-xs text-gray-600 leading-relaxed mb-4 line-clamp-3">
                    {{ $project->description ?: 'Tidak ada deskripsi proyek.' }}
                </p>

                @if(!empty($project->repository_url))
                <div class="mb-3">
                    <a href="{{ $project->repository_url }}" target="_blank" rel="noopener noreferrer"
                        class="inline-flex items-center gap-1.5 text-[11px] text-gray-600 hover:text-gray-900 bg-gray-100 hover:bg-gray-200 px-2.5 py-1 rounded-lg transition font-mono">
                        <i class="fa-brands fa-github text-xs"></i>
                        <span>Repository Git</span>
                        <i class="fa-solid fa-arrow-up-right-from-square text-[9px] text-gray-400 ml-0.5"></i>
                    </a>
                </div>
                @endif
            </div>

            <!-- Card Bottom: Anggota Tim -->
            <div class="pt-3 border-t border-gray-100 mt-auto">
                <div class="flex items-center justify-between text-[11px] font-semibold text-gray-500 mb-1.5">
                    <span>Anggota Tim:</span>
                    <span class="bg-gray-100 text-gray-700 px-1.5 py-0.5 rounded text-[10px]">
                        {{ $project->members->count() }} Orang
                    </span>
                </div>
                <div class="flex flex-wrap gap-1.5">
                    @forelse ($project->members as $member)
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-[11px] bg-gray-50 text-gray-700 border border-gray-200"
                        title="{{ $member->user?->profile?->full_name ?? 'Peserta' }} ({{ $member->school->name ?? 'Sekolah/Kampus' }})">
                        <i class="fa-regular fa-user text-[10px] text-gray-400"></i>
                        <span class="truncate max-w-[120px]">{{ $member->user?->profile?->full_name ?? 'Peserta' }}</span>
                    </span>
                    @empty
                    <span class="text-xs text-gray-400 italic">Belum ada anggota yang ditugaskan</span>
                    @endforelse
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full py-12 text-center text-gray-400 bg-white rounded-2xl border border-gray-200">
            <i class="fa-solid fa-folder-open text-4xl mb-3 block text-gray-300"></i>
            <p class="text-sm font-semibold text-gray-600">Belum ada project yang dibuat.</p>
            <p class="text-xs text-gray-400 mt-1">Klik tombol "+ Tambah Project" di pojok kanan atas untuk membuat project baru.</p>
        </div>
        @endforelse
    </div>

    <!-- Empty Filter State (Hidden by default) -->
    <div id="noResultsState" class="hidden py-12 text-center text-gray-400 bg-white rounded-2xl border border-gray-200 mb-6">
        <i class="fa-solid fa-magnifying-glass text-3xl mb-3 block text-gray-300"></i>
        <p class="text-sm font-semibold text-gray-600">Tidak ada project yang sesuai dengan pencarian / filter.</p>
        <button type="button" onclick="resetSearchAndFilter()" class="mt-3 px-4 py-1.5 bg-blue-50 text-blue-600 rounded-lg text-xs font-semibold hover:bg-blue-100 transition">
            Reset Pencarian
        </button>
    </div>

    <!-- Pagination (Dibatasi 5 item per halaman) -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 flex flex-col sm:flex-row items-center justify-between gap-3">
        <div class="text-xs text-gray-500" id="pagination-info">
            Menampilkan <strong class="text-gray-800" id="page-start">1</strong> - <strong class="text-gray-800" id="page-end">5</strong> dari <strong class="text-gray-800" id="page-total">0</strong> project
        </div>

        <div class="flex items-center space-x-1.5">
            <button id="prev-page" onclick="changePage('prev')"
                class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed transition flex items-center gap-1">
                <i class="fa-solid fa-chevron-left text-[10px]"></i> Sebelumnya
            </button>

            <div id="page-numbers" class="flex items-center space-x-1"></div>

            <button id="next-page" onclick="changePage('next')"
                class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed transition flex items-center gap-1">
                Selanjutnya <i class="fa-solid fa-chevron-right text-[10px]"></i>
            </button>
        </div>
    </div>
</main>

<!-- Modal Popup Form Tambah / Edit Project -->
<div id="projectModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl w-full max-w-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh] animate-fadeIn border border-gray-100">
        <!-- Modal Header -->
        <div class="flex justify-between items-center px-6 py-4 border-b bg-gray-900 text-white">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-blue-500/20 text-blue-400 flex items-center justify-center font-bold">
                    <i class="fa-solid fa-folder-plus text-sm" id="modalIcon"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold" id="modalProjectTitle">Tambah Project Baru</h2>
                    <p class="text-xs text-gray-300">Tentukan nama project, tim/divisi pelaksana, dan anggota magang</p>
                </div>
            </div>
            <button type="button" onclick="closeProjectModal()" class="text-gray-300 hover:text-white text-2xl font-bold leading-none">&times;</button>
        </div>

        <!-- Modal Form -->
        <form id="projectForm" action="{{ route('projects.store') }}" method="POST" class="flex flex-col flex-1 overflow-y-auto">
            @csrf
            <div id="formMethodContainer"></div>
            <input type="hidden" id="project-id" name="project_id" value="">

            <div class="p-6 space-y-5 flex-1">
                <!-- Field 1: Nama Project -->
                <div>
                    <label for="project-name" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Nama Project <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-center gap-2">
                        <select id="project-name" name="project_name"
                            class="w-full text-xs p-3 bg-white border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-medium" required>
                            <option value="" disabled selected>-- Pilih nama project --</option>
                            @foreach ($nameProject as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                        <!-- Tombol + Tambah Nama Project Baru -->
                        <button type="button" onclick="openNameProjectModal()"
                            class="px-3.5 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shrink-0 transition shadow-sm"
                            title="Buat Kategori Nama Project Baru">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>

                <!-- Field 2: Nama Tim / Divisi -->
                <div>
                    <label for="team-name" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Nama Tim / Divisi <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="text" id="team-name" name="team_name" list="team-division-list"
                            placeholder="Pilih divisi atau ketik nama tim..."
                            class="w-full text-xs p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-medium" required>
                        <datalist id="team-division-list">
                            @if(isset($divisions))
                            @foreach ($divisions as $div)
                            <option value="{{ $div->name }}">{{ $div->name }} (Divisi)</option>
                            @endforeach
                            @endif
                        </datalist>
                    </div>
                    <p class="text-[10px] text-gray-500 mt-1.5 flex items-center gap-1">
                        <i class="fa-solid fa-circle-info text-blue-500"></i>
                        Nama tim dapat disesuaikan dengan nama divisi pemagang (misal: Programmer / UI/UX Designer) atau nama kelompok kerja spesifik.
                    </p>
                </div>

                <!-- Field 3: Anggota (Peserta Magang) -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Anggota Tim (Anak Magang)
                        </label>
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="toggleAllVisibleMembers(true)"
                                class="text-[11px] text-blue-600 hover:text-blue-800 font-semibold">
                                Pilih Semua Terlihat
                            </button>
                            <span class="text-gray-300">•</span>
                            <button type="button" onclick="toggleAllVisibleMembers(false)"
                                class="text-[11px] text-gray-500 hover:text-gray-700 font-semibold">
                                Batal Semua
                            </button>
                        </div>
                    </div>

                    <!-- Search Member Input -->
                    <div class="relative mb-2">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <i class="fa-solid fa-magnifying-glass text-xs"></i>
                        </div>
                        <input type="text" id="memberSearchInput" onkeyup="filterMemberChecklist()"
                            placeholder="Cari nama peserta magang atau asal kampus..."
                            class="w-full text-xs pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <!-- Checkbox Container -->
                    <div class="border border-gray-200 rounded-xl max-h-52 overflow-y-auto divide-y divide-gray-100 bg-gray-50/50 p-2" id="membersListContainer">
                        @if(isset($intern) && $intern)
                        @foreach ($intern as $int)
                        <label class="member-item flex items-center justify-between p-2 rounded-lg hover:bg-white cursor-pointer transition"
                            data-name="{{ strtolower($int->user?->profile?->full_name ?? '') }}"
                            data-school="{{ strtolower($int->school?->name ?? '') }}">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <input type="checkbox" name="members[]" value="{{ $int->id }}"
                                    class="member-checkbox w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                                <div class="min-w-0">
                                    <span class="text-xs font-bold text-gray-800 block truncate">
                                        {{ $int->user?->profile?->full_name ?? 'Peserta' }}
                                    </span>
                                    <span class="text-[11px] text-gray-500 block truncate">
                                        {{ $int->school?->name ?? 'Sekolah/Kampus' }}
                                    </span>
                                </div>
                            </div>
                            @if($int->division)
                            <span class="text-[10px] px-2 py-0.5 rounded bg-blue-50 text-blue-700 font-semibold border border-blue-100 shrink-0 ml-2">
                                {{ $int->division->name }}
                            </span>
                            @endif
                        </label>
                        @endforeach
                        @else
                        <p class="text-xs text-gray-400 p-3 text-center">Tidak ada data anak magang aktif.</p>
                        @endif
                    </div>
                </div>

                <!-- Field 4: Deskripsi Project -->
                <div>
                    <label for="description" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Deskripsi Project <span class="text-red-500">*</span>
                    </label>
                    <textarea id="description" name="description" rows="3"
                        placeholder="Masukkan rincian, tujuan, atau deskripsi penugasan proyek..."
                        class="w-full text-xs p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required></textarea>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-3.5 bg-gray-50 border-t border-gray-200 flex justify-end gap-2.5">
                <button type="button" onclick="closeProjectModal()"
                    class="px-4 py-2 border border-gray-300 text-gray-700 rounded-xl text-xs font-semibold hover:bg-gray-100 transition">
                    Batal
                </button>
                <button type="submit" id="btnSubmitProject"
                    class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-sm transition flex items-center gap-1.5">
                    <i class="fa-solid fa-paper-plane text-xs"></i>
                    <span id="btnSubmitProjectText">Simpan Project</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Sub Tambah Nama Project Baru -->
<div id="modalNameProject" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[60] flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl overflow-hidden animate-fadeIn border border-gray-100">
        <div class="flex justify-between items-center px-6 py-4 border-b bg-gray-900 text-white">
            <h3 class="text-sm font-bold flex items-center gap-2">
                <i class="fa-solid fa-plus text-blue-400"></i>
                Tambah Nama Project Baru
            </h3>
            <button type="button" onclick="closeNameProjectModal()" class="text-gray-300 hover:text-white text-xl font-bold leading-none">&times;</button>
        </div>
        <form action="{{ route('projects.nameProject') }}" method="POST" class="p-6">
            @csrf
            <div class="mb-5">
                <label for="new_project_name" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                    Judul / Nama Project Baru <span class="text-red-500">*</span>
                </label>
                <input type="text" id="new_project_name" name="new_project_name" placeholder="Contoh: Project Programmer - API Resource"
                    class="w-full text-xs p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
            </div>
            <div class="flex justify-end gap-2.5">
                <button type="button" onclick="closeNameProjectModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-xl text-xs font-medium hover:bg-gray-100 transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-sm transition">
                    Tambah
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Konfirmasi Hapus Project -->
<div id="deleteProjectModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[60] flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl overflow-hidden p-6 animate-fadeIn border border-gray-100 text-center">
        <div class="w-12 h-12 mx-auto rounded-full bg-red-100 text-red-600 flex items-center justify-center text-xl mb-3">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <h3 class="text-base font-bold text-gray-900 mb-1">Hapus Project?</h3>
        <p class="text-xs text-gray-600 leading-relaxed mb-6">
            Apakah Anda yakin ingin menghapus data project ini? Tindakan ini tidak dapat dibatalkan.
        </p>
        <form id="deleteProjectForm" action="#" method="POST" class="flex justify-center gap-2.5">
            @csrf
            @method('DELETE')
            <button type="button" onclick="closeDeleteModal()"
                class="px-5 py-2 border border-gray-300 text-gray-700 rounded-xl text-xs font-medium hover:bg-gray-100 transition">
                Batal
            </button>
            <button type="submit" class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl text-xs font-bold shadow-sm transition">
                Ya, Hapus
            </button>
        </form>
    </div>
</div>

<!-- Script Logika Frontend: Modal, Pagination 5 Item, Filter & Search -->
<script>
    const itemsPerPage = 6;
    let currentPage = 1;
    let activeStatusFilter = 'all';
    let allCards = [];
    let filteredCards = [];

    document.addEventListener('DOMContentLoaded', function() {
        allCards = Array.from(document.querySelectorAll('.project-card'));
        filteredCards = [...allCards];
        renderPagination();

        // Auto-hide success message
        const message = document.getElementById('success-message');
        if (message) {
            setTimeout(() => {
                message.style.opacity = 0;
                setTimeout(() => message.remove(), 500);
            }, 3000);
        }
    });

    // Filter status button handler
    function filterStatus(status) {
        activeStatusFilter = status;

        // Update button styles
        document.querySelectorAll('.status-filter-btn').forEach(btn => {
            btn.className = 'status-filter-btn px-3 py-1.5 rounded-lg transition text-gray-600 hover:text-gray-900';
        });
        const activeBtn = document.getElementById('filter-status-' + status);
        if (activeBtn) {
            activeBtn.className = 'status-filter-btn px-3 py-1.5 rounded-lg transition bg-white text-gray-800 shadow-xs font-bold';
        }

        filterProjects();
    }

    // Live Search & Status Filter Combined
    function filterProjects() {
        const query = (document.getElementById('searchInput')?.value || '').toLowerCase().trim();

        filteredCards = allCards.filter(card => {
            const name = card.dataset.name || '';
            const team = card.dataset.team || '';
            const desc = card.dataset.desc || '';
            const members = card.dataset.members || '';
            const status = card.dataset.status || '';

            const matchesQuery = !query || name.includes(query) || team.includes(query) || desc.includes(query) || members.includes(query);
            const matchesStatus = activeStatusFilter === 'all' || status === activeStatusFilter;

            return matchesQuery && matchesStatus;
        });

        // Update count
        const countEl = document.getElementById('totalFilteredProjects');
        if (countEl) countEl.innerText = filteredCards.length;

        currentPage = 1;
        renderPagination();
    }

    function resetSearchAndFilter() {
        const searchInput = document.getElementById('searchInput');
        if (searchInput) searchInput.value = '';
        filterStatus('all');
    }

    // Render Page Items (Max 5 items displayed)
    function renderPagination() {
        const totalItems = filteredCards.length;
        const totalPages = Math.ceil(totalItems / itemsPerPage) || 1;

        if (currentPage > totalPages) currentPage = totalPages;
        if (currentPage < 1) currentPage = 1;

        // Hide all cards first
        allCards.forEach(card => card.style.display = 'none');

        // Show empty state if no matches
        const noResultsState = document.getElementById('noResultsState');
        if (totalItems === 0 && allCards.length > 0) {
            if (noResultsState) noResultsState.classList.remove('hidden');
        } else {
            if (noResultsState) noResultsState.classList.add('hidden');
        }

        // Slice 5 items for current page
        const start = (currentPage - 1) * itemsPerPage;
        const end = start + itemsPerPage;
        const currentSlice = filteredCards.slice(start, end);

        currentSlice.forEach(card => {
            card.style.display = 'flex';
        });

        // Update info text
        const pageStartEl = document.getElementById('page-start');
        const pageEndEl = document.getElementById('page-end');
        const pageTotalEl = document.getElementById('page-total');
        if (pageStartEl) pageStartEl.innerText = totalItems === 0 ? 0 : start + 1;
        if (pageEndEl) pageEndEl.innerText = Math.min(end, totalItems);
        if (pageTotalEl) pageTotalEl.innerText = totalItems;

        // Update buttons
        const prevButton = document.getElementById('prev-page');
        const nextButton = document.getElementById('next-page');
        if (prevButton) prevButton.disabled = currentPage === 1;
        if (nextButton) nextButton.disabled = currentPage === totalPages || totalItems === 0;

        // Render page number buttons
        const pageNumbersDiv = document.getElementById('page-numbers');
        if (pageNumbersDiv) {
            pageNumbersDiv.innerHTML = '';
            for (let i = 1; i <= totalPages; i++) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.innerText = i;
                btn.className = (i === currentPage) ?
                    'w-8 h-8 rounded-lg text-xs font-bold bg-blue-600 text-white shadow-xs' :
                    'w-8 h-8 rounded-lg text-xs font-semibold text-gray-700 bg-white border border-gray-300 hover:bg-gray-100 transition';
                btn.onclick = () => {
                    currentPage = i;
                    renderPagination();
                };
                pageNumbersDiv.appendChild(btn);
            }
        }
    }

    function changePage(direction) {
        const totalPages = Math.ceil(filteredCards.length / itemsPerPage) || 1;
        if (direction === 'prev' && currentPage > 1) {
            currentPage--;
        } else if (direction === 'next' && currentPage < totalPages) {
            currentPage++;
        }
        renderPagination();
    }

    // Modal Form Handlers
    function openAddProjectModal() {
        const form = document.getElementById('projectForm');
        form.reset();
        form.action = "{{ route('projects.store') }}";
        document.getElementById('formMethodContainer').innerHTML = '';
        document.getElementById('project-id').value = '';
        document.getElementById('modalProjectTitle').innerText = 'Tambah Project Baru';
        document.getElementById('btnSubmitProjectText').innerText = 'Simpan Project';
        document.getElementById('modalIcon').className = 'fa-solid fa-folder-plus text-sm';

        // Uncheck all member checkboxes
        document.querySelectorAll('.member-checkbox').forEach(cb => cb.checked = false);

        document.getElementById('projectModal').classList.remove('hidden');
    }

    function editProject(event, project) {
        if (event) event.preventDefault();

        const form = document.getElementById('projectForm');
        form.reset();
        form.action = "{{ url('admin/setting/projects/update') }}/" + project.id;
        document.getElementById('formMethodContainer').innerHTML = '<input type="hidden" name="_method" value="PUT">';
        document.getElementById('project-id').value = project.id;
        document.getElementById('modalProjectTitle').innerText = 'Edit Project #' + project.id;
        document.getElementById('btnSubmitProjectText').innerText = 'Perbarui Project';
        document.getElementById('modalIcon').className = 'fa-solid fa-pen-to-square text-sm';

        // Fill inputs
        if (project.name_project_id) {
            document.getElementById('project-name').value = project.name_project_id;
        }
        document.getElementById('team-name').value = project.team || '';
        document.getElementById('description').value = project.description || '';

        // Check members
        const memberIds = project.member_ids || [];
        document.querySelectorAll('.member-checkbox').forEach(cb => {
            cb.checked = memberIds.includes(parseInt(cb.value));
        });

        document.getElementById('projectModal').classList.remove('hidden');
    }

    function closeProjectModal() {
        document.getElementById('projectModal').classList.add('hidden');
    }

    // Sub-Modal Name Project
    function openNameProjectModal() {
        document.getElementById('modalNameProject').classList.remove('hidden');
    }

    function closeNameProjectModal() {
        document.getElementById('modalNameProject').classList.add('hidden');
    }

    // Delete Modal
    function openDeleteModal(projectId, event) {
        if (event) event.preventDefault();
        document.getElementById('deleteProjectForm').action = "{{ url('admin/setting/delete-projects') }}/" + projectId;
        document.getElementById('deleteProjectModal').classList.remove('hidden');
    }

    function closeDeleteModal() {
        document.getElementById('deleteProjectModal').classList.add('hidden');
    }

    // Member search filter in modal
    function filterMemberChecklist() {
        const query = (document.getElementById('memberSearchInput')?.value || '').toLowerCase().trim();
        document.querySelectorAll('.member-item').forEach(item => {
            const name = item.dataset.name || '';
            const school = item.dataset.school || '';
            if (!query || name.includes(query) || school.includes(query)) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    }

    function toggleAllVisibleMembers(select) {
        document.querySelectorAll('.member-item').forEach(item => {
            if (item.style.display !== 'none') {
                const cb = item.querySelector('.member-checkbox');
                if (cb) cb.checked = select;
            }
        });
    }

    // Update Project Status via AJAX
    function updateProjectStatus(projectId, isChecked) {
        const newStatus = isChecked ? 'done' : 'progress';

        fetch("{{ url('admin/setting/projects') }}/" + projectId + "/update-status", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    status: newStatus
                })
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                // Update local card attribute & DOM status badge
                const card = document.querySelector(`.project-card[data-id="${projectId}"]`);
                if (card) {
                    card.dataset.status = newStatus;
                    const badgeContainer = card.querySelector('label');
                    if (badgeContainer) {
                        const existingBadge = badgeContainer.querySelector('span');
                        if (existingBadge) {
                            if (newStatus === 'done') {
                                existingBadge.className = 'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200';
                                existingBadge.innerHTML = '<i class="fa-solid fa-check text-[10px]"></i> Selesai';
                            } else {
                                existingBadge.className = 'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-800 border border-amber-200';
                                existingBadge.innerHTML = '<i class="fa-regular fa-hourglass-half text-[10px]"></i> Dikerjakan';
                            }
                        }
                    }
                }
                filterProjects();
            })
            .catch(error => {
                console.error('Error updating status:', error);
                alert('Gagal memperbarui status project.');
            });
    }
</script>
@endsection