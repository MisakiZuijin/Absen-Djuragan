@extends('layouts.main')

@section('title', 'Pengaturan Project')

@section('contents')
@include('layouts.sidebar-pengaturan')

<!-- Main Content -->
<main class="ml-0 lg:ml-[32rem] mt-2 lg:mt-20 p-3 sm:p-6 min-w-0 max-w-full overflow-x-hidden">
    <div class="w-full max-w-full min-w-0 space-y-6">

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
                                onchange="updateProjectStatus(this.dataset.id, this.checked)"
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
                        <div class="flex items-center gap-1.5">
                            <button type="button"
                                class="w-7 h-7 rounded-lg bg-amber-500 hover:bg-amber-600 text-white flex items-center justify-center transition shadow-xs"
                                title="Edit Project"
                                data-project="{{ json_encode([
                                        'id' => $project->id,
                                        'name_project_id' => $project->name_project_id,
                                        'team' => $project->team,
                                        'description' => $project->description,
                                        'division_id' => $project->division_id,
                                        'member_ids' => $project->members->pluck('id')->toArray()
                                    ]) }}"
                                onclick="editProject(event, JSON.parse(this.dataset.project))">
                                <i class="fa-solid fa-pen-to-square text-xs"></i>
                            </button>
                            <button type="button"
                                class="w-7 h-7 rounded-lg bg-rose-600 hover:bg-rose-700 text-white flex items-center justify-center transition shadow-xs"
                                title="Hapus Project"
                                data-id="{{ $project->id }}"
                                onclick="openDeleteModal(this.dataset.id, event)">
                                <i class="fa-solid fa-trash-can text-xs"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Team / Divisi Badge -->
                    <div class="mb-2">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-blue-600 text-white shadow-xs">
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
            <button type="button" onclick="resetSearchAndFilter()" class="mt-3 px-4 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-bold hover:bg-blue-700 shadow-xs transition">
                Reset Pencarian
            </button>
        </div>

        <!-- Pagination (Dibatasi 6 item per halaman) -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="text-xs text-gray-500" id="pagination-info">
                Menampilkan <strong class="text-gray-800" id="page-start">1</strong> - <strong class="text-gray-800" id="page-end">5</strong> dari <strong class="text-gray-800" id="page-total">0</strong> project
            </div>

            <div class="flex items-center space-x-1.5">
                <button id="prev-page" onclick="changePage('prev')"
                    class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-gray-800 text-white hover:bg-gray-900 disabled:bg-gray-300 disabled:text-gray-500 disabled:cursor-not-allowed shadow-xs transition flex items-center gap-1">
                    <i class="fa-solid fa-chevron-left text-[10px]"></i> Sebelumnya
                </button>

                <div id="page-numbers" class="flex items-center space-x-1"></div>

                <button id="next-page" onclick="changePage('next')"
                    class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-gray-800 text-white hover:bg-gray-900 disabled:bg-gray-300 disabled:text-gray-500 disabled:cursor-not-allowed shadow-xs transition flex items-center gap-1">
                    Selanjutnya <i class="fa-solid fa-chevron-right text-[10px]"></i>
                </button>
            </div>
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
            <input type="hidden" id="project-raise-id" name="raise_id" value="{{ $raiseId ?? '' }}">

            <div class="p-6 space-y-5 flex-1">
                @if(isset($targetIntern) && $targetIntern)
                <div class="p-3.5 bg-purple-50 border border-purple-200 rounded-xl flex items-center justify-between gap-3 text-xs text-purple-900">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-8 h-8 rounded-lg bg-purple-600 text-white flex items-center justify-center font-bold text-xs shrink-0">
                            <i class="fa-solid fa-folder-plus"></i>
                        </div>
                        <div class="min-w-0">
                            <span class="font-bold text-purple-950 block truncate">Penugasan Cepat Raise Hand</span>
                            <span class="text-[11px] text-purple-700 block truncate">
                                Pemagang: <strong>{{ $targetIntern->user?->profile?->full_name ?? $targetIntern->user?->username ?? 'Peserta' }}</strong> ({{ $targetIntern->school?->name ?? '-' }} • {{ $targetIntern->division?->name ?? 'Divisi' }})
                            </span>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-300 shrink-0">
                        <i class="fa-solid fa-hand text-purple-600"></i> Minta Tugas
                    </span>
                </div>
                @endif

                <!-- Field 0: Pilih Divisi (Dropdown Utama Dependent) -->
                <div class="bg-blue-50/70 p-3.5 rounded-xl border border-blue-200">
                    <label for="project-division" class="block text-xs font-bold text-blue-900 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                        <span>Pilih Divisi Terlebih Dahulu <span class="text-red-500">*</span></span>
                        <span id="divisionLoadingIndicator" class="hidden text-[11px] font-semibold text-blue-700 flex items-center gap-1">
                            <i class="fa-solid fa-spinner fa-spin"></i> Memuat data divisi...
                        </span>
                    </label>
                    <select id="project-division" name="division_id" onchange="onDivisionSelected(this.value)"
                        class="w-full text-xs p-3 bg-white border border-blue-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-semibold text-gray-800" required>
                        <option value="">-- Pilih Divisi Terlebih Dahulu --</option>
                        @foreach ($divisions as $div)
                        <option value="{{ $div->id }}">{{ $div->name }}</option>
                        @endforeach
                    </select>
                    <p class="text-[10px] text-blue-700 mt-1 flex items-center gap-1">
                        <i class="fa-solid fa-circle-info"></i>
                        Judul project dan daftar anggota pemagang akan disaring otomatis sesuai divisi yang dipilih.
                    </p>
                </div>

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
                        <span class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Anggota Tim (Anak Magang)
                        </span>
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
                            data-school="{{ strtolower($int->school?->name ?? '') }}"
                            data-division-id="{{ $int->division_id }}">
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
                    class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-xl text-xs font-semibold shadow-xs transition">
                    Batal
                </button>
                <button type="submit" id="btnSubmitProject"
                    class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md hover:shadow transition flex items-center gap-1.5">
                    <i class="fa-solid fa-paper-plane text-xs"></i>
                    <span id="btnSubmitProjectText">Simpan Project</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Sub Tambah Nama Project Baru -->
<div id="modalNameProject" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[60] flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden animate-fadeIn border border-gray-100">
        <div class="flex justify-between items-center px-6 py-4 border-b bg-gray-900 text-white">
            <h3 class="text-sm font-bold flex items-center gap-2">
                <i class="fa-solid fa-plus text-blue-400"></i>
                Tambah Judul Project Baru
            </h3>
            <button type="button" onclick="closeNameProjectModal()" class="text-gray-300 hover:text-white text-xl font-bold leading-none">&times;</button>
        </div>
        <form id="formAddNameProject" action="{{ route('projects.nameProject') }}" method="POST" class="p-6" onsubmit="submitNameProjectForm(event)">
            @csrf
            <input type="hidden" id="name_project_prefix" name="prefix" value="">
            <input type="hidden" id="full_project_name" name="new_project_name" value="">

            <div class="mb-5">
                <label for="input_project_title_suffix" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                    Judul / Nama Project Baru <span class="text-red-500">*</span>
                </label>

                <!-- Input with Locked Prefix -->
                <div class="flex rounded-xl border border-gray-300 focus-within:ring-2 focus-within:ring-blue-500 focus-within:border-blue-500 overflow-hidden bg-slate-50">
                    <span id="display_prefix_badge" class="px-3 py-2.5 bg-slate-200/90 text-slate-800 text-xs font-bold border-r border-gray-300 flex items-center shrink-0 select-none whitespace-nowrap">
                        Project Divisi -
                    </span>
                    <input type="text" id="input_project_title_suffix" required
                        placeholder="Ketik judul spesifik project..."
                        class="w-full text-xs p-2.5 bg-white text-gray-800 outline-none font-medium">
                </div>
                <p class="text-[11px] text-gray-500 mt-2 flex items-center gap-1.5">
                    <i class="fa-solid fa-lock text-slate-400 text-[10px]"></i>
                    Prefix <span class="font-bold text-slate-700" id="display_prefix_note">"Project [Divisi] - "</span> terkunci otomatis mengikuti divisi yang dipilih.
                </p>
            </div>
            <div class="flex justify-end gap-2.5">
                <button type="button" onclick="closeNameProjectModal()" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-xl text-xs font-semibold shadow-xs transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md transition flex items-center gap-1.5">
                    <i class="fa-solid fa-plus text-xs"></i>
                    <span>Tambah Judul</span>
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
                class="px-5 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-xl text-xs font-semibold shadow-xs transition">
                Batal
            </button>
            <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold shadow-md transition">
                Ya, Hapus
            </button>
        </form>
    </div>
</div>

<div id="project-page-data"
    data-action="{{ $action ?? '' }}"
    data-intern-id="{{ $targetInternId ?? '' }}"
    data-division-id="{{ $targetDivisionId ?? '' }}"
    class="hidden"></div>

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

        // Auto open Add Project Modal if triggered from shortcut (e.g. Raise Hand "Minta Tugas")
        const urlParams = new URLSearchParams(window.location.search);
        const pageData = document.getElementById('project-page-data');
        const targetAction = urlParams.get('action') || (pageData ? pageData.dataset.action : '') || '';
        const targetInternId = urlParams.get('intern_id') || (pageData ? pageData.dataset.internId : '') || '';
        const targetDivisionId = urlParams.get('division_id') || (pageData ? pageData.dataset.divisionId : '') || '';

        if (targetAction === 'assign_task' && targetInternId) {
            openAddProjectModal();
            if (targetDivisionId) {
                const divisionSelect = document.getElementById('project-division');
                if (divisionSelect) {
                    divisionSelect.value = targetDivisionId;
                    onDivisionSelected(targetDivisionId, null, [parseInt(targetInternId)]);
                }
            }
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
                    'w-8 h-8 rounded-lg text-xs font-bold text-gray-800 bg-gray-200 hover:bg-gray-300 shadow-xs transition';
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
    function onDivisionSelected(divisionId, targetProjectId = null, targetMemberIds = []) {
        const teamNameInput = document.getElementById('team-name');
        const projectNameSelect = document.getElementById('project-name');
        const membersContainer = document.getElementById('membersListContainer');
        const indicator = document.getElementById('divisionLoadingIndicator');

        if (!divisionId) {
            projectNameSelect.innerHTML = '<option value="" disabled selected>-- Pilih nama project --</option>';
            membersContainer.innerHTML = '<p class="text-xs text-gray-400 p-3 text-center">Silakan pilih divisi di atas terlebih dahulu.</p>';
            return;
        }

        if (indicator) indicator.classList.remove('hidden');

        fetch("{{ url('admin/setting/projects/division-data') }}/" + divisionId)
            .then(response => {
                if (!response.ok) throw new Error('Network response error');
                return response.json();
            })
            .then(data => {
                if (indicator) indicator.classList.add('hidden');

                if (data.success) {
                    // Auto-fill team name if creating new or if matches division
                    if (data.division && (!teamNameInput.value || teamNameInput.dataset.autofilled === 'true')) {
                        teamNameInput.value = data.division.name;
                        teamNameInput.dataset.autofilled = 'true';
                    }

                    // Populate Master Name Projects
                    projectNameSelect.innerHTML = '<option value="" disabled selected>-- Pilih nama project --</option>';
                    if (data.name_projects && data.name_projects.length > 0) {
                        data.name_projects.forEach(item => {
                            const opt = document.createElement('option');
                            opt.value = item.id;
                            opt.textContent = item.name;
                            if (targetProjectId && String(targetProjectId) === String(item.id)) {
                                opt.selected = true;
                            }
                            projectNameSelect.appendChild(opt);
                        });
                    }

                    // Populate Members Checkboxes
                    membersContainer.innerHTML = '';
                    if (data.interns && data.interns.length > 0) {
                        data.interns.forEach(intern => {
                            const isChecked = targetMemberIds.includes(parseInt(intern.id)) ? 'checked' : '';
                            const memberHtml = `
                                <label class="member-item flex items-center justify-between p-2 rounded-lg hover:bg-white cursor-pointer transition"
                                    data-name="${(intern.name || '').toLowerCase()}"
                                    data-school="${(intern.school || '').toLowerCase()}">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <input type="checkbox" name="members[]" value="${intern.id}" ${isChecked}
                                            class="member-checkbox w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                                        <div class="min-w-0">
                                            <span class="text-xs font-bold text-gray-800 block truncate">
                                                ${intern.name}
                                            </span>
                                            <span class="text-[11px] text-gray-500 block truncate">
                                                ${intern.school}
                                            </span>
                                        </div>
                                    </div>
                                    <span class="text-[10px] px-2 py-0.5 rounded bg-blue-50 text-blue-700 font-semibold border border-blue-100 shrink-0 ml-2">
                                        ${intern.division_name || '-'}
                                    </span>
                                </label>
                            `;
                            membersContainer.insertAdjacentHTML('beforeend', memberHtml);
                        });
                    } else {
                        membersContainer.innerHTML = '<p class="text-xs text-amber-700 font-medium p-3 text-center bg-amber-50 rounded-lg border border-amber-200">Tidak ada pemagang aktif pada divisi ini saat ini.</p>';
                    }
                }
            })
            .catch(err => {
                if (indicator) indicator.classList.add('hidden');
                console.error('Error fetching division data:', err);
            });
    }

    function openAddProjectModal() {
        const form = document.getElementById('projectForm');
        form.reset();
        form.action = "{{ route('projects.store') }}";
        document.getElementById('formMethodContainer').innerHTML = '';
        document.getElementById('project-id').value = '';
        const raiseInput = document.getElementById('project-raise-id');
        if (raiseInput) raiseInput.value = "{{ $raiseId ?? '' }}";
        document.getElementById('modalProjectTitle').innerText = 'Tambah Project Baru';
        document.getElementById('btnSubmitProjectText').innerText = 'Simpan Project';
        document.getElementById('modalIcon').className = 'fa-solid fa-folder-plus text-sm';

        document.getElementById('project-division').value = '';
        const teamNameInput = document.getElementById('team-name');
        teamNameInput.value = '';
        teamNameInput.dataset.autofilled = 'true';

        document.getElementById('project-name').innerHTML = '<option value="" disabled selected>-- Pilih nama project --</option>';
        document.getElementById('membersListContainer').innerHTML = '<p class="text-xs text-gray-400 p-3 text-center">Silakan pilih divisi di atas terlebih dahulu.</p>';

        document.getElementById('projectModal').classList.remove('hidden');
    }

    function editProject(event, project) {
        if (event) event.preventDefault();

        const form = document.getElementById('projectForm');
        form.reset();
        form.action = "{{ url('admin/setting/projects/update') }}/" + project.id;
        document.getElementById('formMethodContainer').innerHTML = '<input type="hidden" name="_method" value="PUT">';
        document.getElementById('project-id').value = project.id;
        const raiseInput = document.getElementById('project-raise-id');
        if (raiseInput) raiseInput.value = '';
        document.getElementById('modalProjectTitle').innerText = 'Edit Project #' + project.id;
        document.getElementById('btnSubmitProjectText').innerText = 'Perbarui Project';
        document.getElementById('modalIcon').className = 'fa-solid fa-pen-to-square text-sm';

        const teamNameInput = document.getElementById('team-name');
        teamNameInput.value = project.team || '';
        teamNameInput.dataset.autofilled = 'false';
        document.getElementById('description').value = project.description || '';

        // Deteksi divisi jika belum ter-set
        let divisionId = project.division_id;
        if (!divisionId && project.team) {
            const divisionSelect = document.getElementById('project-division');
            for (let i = 0; i < divisionSelect.options.length; i++) {
                if (divisionSelect.options[i].text.toLowerCase() === project.team.toLowerCase()) {
                    divisionId = divisionSelect.options[i].value;
                    break;
                }
            }
        }

        if (divisionId) {
            document.getElementById('project-division').value = divisionId;
            onDivisionSelected(divisionId, project.name_project_id, project.member_ids || []);
        } else {
            // Fallback: isi nama project langsung jika divisi tidak spesifik
            if (project.name_project_id) {
                document.getElementById('project-name').value = project.name_project_id;
            }
            const memberIds = project.member_ids || [];
            document.querySelectorAll('.member-checkbox').forEach(cb => {
                cb.checked = memberIds.includes(parseInt(cb.value));
            });
        }

        document.getElementById('projectModal').classList.remove('hidden');
    }

    function closeProjectModal() {
        document.getElementById('projectModal').classList.add('hidden');
    }

    // Sub-Modal Name Project with Locked Prefix
    function openNameProjectModal() {
        const divisionSelect = document.getElementById('project-division');
        const selectedOption = divisionSelect.options[divisionSelect.selectedIndex];
        const divisionId = divisionSelect.value;

        if (!divisionId || !selectedOption || selectedOption.value === "") {
            alert('Silakan pilih Divisi terlebih dahulu sebelum menambah nama project baru!');
            divisionSelect.focus();
            return;
        }

        const divisionName = selectedOption.text.trim();
        const prefixText = `Project ${divisionName} - `;

        document.getElementById('name_project_prefix').value = prefixText;
        document.getElementById('display_prefix_badge').innerText = prefixText;
        document.getElementById('display_prefix_note').innerText = `"${prefixText}"`;
        document.getElementById('input_project_title_suffix').value = '';

        document.getElementById('modalNameProject').classList.remove('hidden');
        setTimeout(() => {
            document.getElementById('input_project_title_suffix').focus();
        }, 100);
    }

    function submitNameProjectForm(event) {
        const prefix = document.getElementById('name_project_prefix').value || '';
        const suffix = document.getElementById('input_project_title_suffix').value.trim();

        if (!suffix) {
            event.preventDefault();
            alert('Silakan ketik judul project!');
            return;
        }

        document.getElementById('full_project_name').value = prefix + suffix;
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