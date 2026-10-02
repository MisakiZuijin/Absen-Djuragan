@extends('layouts.main')

@section('title', 'Portofolio Project Selesai')

@section('contents')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .project-portfolio-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 0.875rem;
        }
        @media (min-width: 640px) {
            .project-portfolio-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        /* Pas 3 kolom pada zoom 100% (layar ~1024px) */
        @media (min-width: 900px) {
            .project-portfolio-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }
        /* Saat zoom out (lebar efektif >= 1280px) otomatis 4 kolom */
        @media (min-width: 1280px) {
            .project-portfolio-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }
        /* Saat zoom out lebih jauh (lebar efektif >= 1600px) otomatis 5 kolom */
        @media (min-width: 1600px) {
            .project-portfolio-grid {
                grid-template-columns: repeat(5, minmax(0, 1fr));
            }
        }
        /* Saat zoom out maksimal (lebar efektif >= 2000px) otomatis 6 kolom */
        @media (min-width: 2000px) {
            .project-portfolio-grid {
                grid-template-columns: repeat(6, minmax(0, 1fr));
            }
        }

        /* Select2 Custom Tailwind Styling */
        .select2-container--default .select2-selection--single {
            height: 32px !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 0.5rem !important;
            padding: 2px 8px !important;
            display: flex !important;
            align-items: center !important;
            background-color: #ffffff !important;
            font-size: 0.75rem !important;
            transition: all 0.2s ease !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #334155 !important;
            line-height: 28px !important;
            padding-left: 0 !important;
            font-weight: 500 !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 30px !important;
            right: 8px !important;
        }
        .select2-container--default.select2-container--open .select2-selection--single,
        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2) !important;
            outline: none !important;
        }
        .select2-dropdown {
            border: 1px solid #e2e8f0 !important;
            border-radius: 0.5rem !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
            font-size: 0.75rem !important;
            z-index: 9999 !important;
            overflow: hidden !important;
        }
        .select2-search--dropdown {
            padding: 6px !important;
        }
        .select2-search--dropdown .select2-search__field {
            border: 1px solid #e2e8f0 !important;
            border-radius: 0.375rem !important;
            padding: 5px 8px !important;
            font-size: 0.75rem !important;
            outline: none !important;
        }
        .select2-search--dropdown .select2-search__field:focus {
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 1px #6366f1 !important;
        }
        .select2-results__option {
            padding: 6px 10px !important;
            font-size: 0.75rem !important;
            color: #334155 !important;
        }
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #4f46e5 !important;
            color: #ffffff !important;
        }
        .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: #eef2ff !important;
            color: #4338ca !important;
            font-weight: 600 !important;
        }
    </style>

    <!-- Main Content Wrapper (Menyesuaikan lebar layar 3/4 di samping sidebar) -->
    <div class="ml-0 md:ml-48 lg:ml-64 mt-20 p-4 sm:p-6 min-h-screen">
        <div class="w-full min-w-0 space-y-4">

            <!-- Breadcrumb & Header Title -->
            <div class="w-full">
                <div class="flex items-center gap-1.5 text-[10px] text-slate-400 font-medium mb-1">
                    <a href="{{ route('admin.home') }}" class="hover:text-indigo-600 transition">Dashboard</a>
                    <span>/</span>
                    <span class="text-slate-600 font-semibold">Portofolio Project</span>
                </div>
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <h1 class="text-base sm:text-lg md:text-xl font-black text-slate-800 flex items-center gap-2">
                            <i class="fa-solid fa-briefcase text-indigo-600 text-sm sm:text-base"></i>
                            <span>Portofolio Project Selesai</span>
                        </h1>
                        <p class="text-[11px] text-slate-500 mt-0.5">
                            Arsip karya, tugas, dan project pemagang yang telah selesai dan tervalidasi lintas divisi.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Summary Metric Cards (CSS Grid) -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 sm:gap-3 w-full">
                <!-- Card 1: Total Project Selesai -->
                <div class="bg-white rounded-xl p-3 border border-slate-200/80 shadow-2xs flex items-center justify-between min-w-0">
                    <div class="min-w-0 flex-1 pr-2">
                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block truncate">Total Project Selesai</span>
                        <span class="text-lg sm:text-xl font-black text-slate-800 mt-0.5 block leading-tight">{{ $totalCompletedProjects }}</span>
                        <span class="text-[8px] text-slate-400 block truncate">Tervalidasi & disahkan</span>
                    </div>
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 text-xs shrink-0">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                </div>

                <!-- Card 2: Total Pemagang Terlibat -->
                <div class="bg-white rounded-xl p-3 border border-slate-200/80 shadow-2xs flex items-center justify-between min-w-0">
                    <div class="min-w-0 flex-1 pr-2">
                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block truncate">Pemagang Berkontribusi</span>
                        <span class="text-lg sm:text-xl font-black text-emerald-700 mt-0.5 block leading-tight">{{ $totalInternsInvolved }}</span>
                        <span class="text-[8px] text-slate-400 block truncate">Alumni penugasan</span>
                    </div>
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 text-xs shrink-0">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>

                <!-- Card 3: Divisi Terlibat -->
                <div class="bg-white rounded-xl p-3 border border-slate-200/80 shadow-2xs flex items-center justify-between min-w-0">
                    <div class="min-w-0 flex-1 pr-2">
                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block truncate">Total Divisi</span>
                        <span class="text-lg sm:text-xl font-black text-purple-700 mt-0.5 block leading-tight">{{ count($divisions) }}</span>
                        <span class="text-[8px] text-slate-400 block truncate">Kategori keahlian</span>
                    </div>
                    <div class="w-8 h-8 rounded-lg bg-purple-50 border border-purple-100 flex items-center justify-center text-purple-600 text-xs shrink-0">
                        <i class="fa-solid fa-sitemap"></i>
                    </div>
                </div>
            </div>

            <!-- Filter & Search Toolbar (CSS Grid) -->
            <div class="bg-white rounded-xl p-3 border border-slate-200/80 shadow-2xs w-full">
                <!-- Search & Filter Form -->
                <form method="GET" action="{{ route('admin.projects.completed') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-2.5 items-center w-full">
                    
                    <!-- Search Input (Keyword) -->
                    <div class="sm:col-span-6 lg:col-span-6 relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400 text-xs">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input type="text" name="search" value="{{ $searchKeyword ?? '' }}"
                            placeholder="Cari nama project, tim, pemagang, kampus..."
                            class="w-full pl-8 pr-3 py-1.5 text-xs h-8 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition placeholder:text-slate-400">
                    </div>

                    <!-- Searchable Division Dropdown -->
                    <div class="sm:col-span-4 lg:col-span-4 min-w-0">
                        <select name="division_id" id="divisionSelect" class="select2 w-full">
                            <option value="all" {{ (empty($selectedDivision) || $selectedDivision === 'all') ? 'selected' : '' }}>
                                Semua Divisi ({{ $totalCompletedProjects }})
                            </option>
                            @foreach($divisions as $div)
                                <option value="{{ $div->id }}" {{ ($selectedDivision == $div->id) ? 'selected' : '' }}>
                                    {{ $div->name }} ({{ $div->completed_count }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Action Buttons -->
                    <div class="sm:col-span-2 lg:col-span-2 grid grid-cols-2 gap-1.5">
                        <button type="submit" class="w-full py-1.5 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-xs font-bold h-8 transition flex items-center justify-center gap-1 shadow-2xs cursor-pointer" title="Terapkan Pencarian">
                            <i class="fa-solid fa-filter text-[10px]"></i>
                            <span>Cari</span>
                        </button>
                        @if(!empty($searchKeyword) || (!empty($selectedDivision) && $selectedDivision !== 'all'))
                            <a href="{{ route('admin.projects.completed') }}" class="w-full py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg text-xs font-semibold h-8 flex items-center justify-center transition" title="Reset Pencarian">
                                Reset
                            </a>
                        @else
                            <button type="button" disabled class="w-full py-1.5 bg-slate-50 text-slate-300 rounded-lg text-xs font-semibold h-8 flex items-center justify-center cursor-not-allowed">
                                Reset
                            </button>
                        @endif
                    </div>
                </form>

                @if(!empty($searchKeyword) || (!empty($selectedDivision) && $selectedDivision !== 'all'))
                    <div class="flex flex-wrap items-center gap-2 pt-2.5 mt-2.5 border-t border-slate-100 text-[11px] text-slate-500">
                        <span class="font-medium text-slate-400">Filter aktif:</span>
                        @if(!empty($searchKeyword))
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-indigo-50 text-indigo-700 rounded-md border border-indigo-200/60 font-semibold text-[10px]">
                                <i class="fa-solid fa-magnifying-glass text-[8px]"></i> "{{ $searchKeyword }}"
                            </span>
                        @endif
                        @if(!empty($selectedDivision) && $selectedDivision !== 'all')
                            @php
                                $activeDiv = $divisions->firstWhere('id', $selectedDivision);
                            @endphp
                            @if($activeDiv)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-purple-50 text-purple-700 rounded-md border border-purple-200/60 font-semibold text-[10px]">
                                    <i class="fa-solid fa-layer-group text-[8px]"></i> {{ $activeDiv->name }} ({{ $activeDiv->completed_count }})
                                </span>
                            @endif
                        @endif
                    </div>
                @endif
            </div>

            <!-- Project Cards Grid (Responsif & Adaptif: 3 kolom pas saat 100%, otomatis bertambah saat zoom out) -->
            @if($projects->count() > 0)
                <div class="project-portfolio-grid w-full">
                    @foreach($projects as $project)
                        @php
                            // Ambil anggota unik & divisi project
                            $members = $project->detailProjects->map(fn($dp) => $dp->intern)->filter()->unique('id');
                            $firstDivision = $members->first()?->division;
                            $divName = $firstDivision->name ?? 'Umum';
                            $divNameLower = strtolower($divName);

                            // Deteksi divisi
                            $divId = (int) ($firstDivision?->id ?? 0);
                            $isProgrammer = ($divId === 4)
                                || str_contains($divNameLower, 'programmer')
                                || str_contains($divNameLower, 'program');

                            $isUiUx = ($divId === 1)
                                || str_contains($divNameLower, 'ui/ux')
                                || str_contains($divNameLower, 'ui / ux')
                                || (str_contains($divNameLower, 'ui') && str_contains($divNameLower, 'ux'));

                            $repoUrl = $project->repository_url;
                            $githubUrl = (!empty($repoUrl) && !str_contains($repoUrl, 'figma')) ? $repoUrl : null;
                            $figmaUrl = (!empty($repoUrl) && str_contains($repoUrl, 'figma')) ? $repoUrl : null;
                        @endphp
                        <div class="bg-white rounded-xl border border-slate-200/80 shadow-2xs hover:shadow-md hover:border-indigo-300 transition-all duration-200 flex flex-col justify-between overflow-hidden group">
                            
                            <!-- Card Body -->
                            <div class="p-3 space-y-2">
                                <!-- Top Badges -->
                                <div class="flex items-center justify-between gap-1">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200/60 truncate max-w-[140px]">
                                        <i class="fa-solid fa-briefcase text-indigo-500 text-[9px]"></i>
                                        <span class="truncate">{{ $divName }}</span>
                                    </span>
                                    <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full text-[9px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 shrink-0">
                                        <i class="fa-solid fa-circle-check text-emerald-600 text-[9px]"></i>
                                        <span>Selesai</span>
                                    </span>
                                </div>

                                <!-- Project Title & Team -->
                                <div>
                                    <h3 class="font-bold text-slate-800 text-xs sm:text-sm leading-snug group-hover:text-indigo-600 transition line-clamp-1" title="{{ $project->nameProject->name ?? 'Project Selesai' }}">
                                        {{ $project->nameProject->name ?? 'Project Selesai' }}
                                    </h3>
                                    @if(!empty($project->team))
                                        <p class="text-[11px] text-slate-500 font-medium mt-0.5 flex items-center gap-1">
                                            <i class="fa-solid fa-user-group text-slate-400 text-[9px]"></i>
                                            <span class="truncate">Tim: {{ $project->team }}</span>
                                        </p>
                                    @endif
                                </div>

                                <!-- Description (Compact) -->
                                @if(!empty($project->description))
                                    <p class="text-[11px] text-slate-600 line-clamp-2 leading-relaxed bg-slate-50/80 p-2 rounded-lg border border-slate-100" title="{{ $project->description }}">
                                        {{ $project->description }}
                                    </p>
                                @endif

                                <!-- Daftar Anggota Tim Pemagang -->
                                <div class="pt-1.5 border-t border-slate-100 space-y-1">
                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Anggota Pemagang:</span>
                                    <div class="space-y-1 max-h-28 overflow-y-auto pr-0.5 scrollbar-thin">
                                        @forelse($members as $m)
                                            @php
                                                $memberName = $m->user?->profile?->full_name ?? $m->user?->username ?? 'Pemagang';
                                                $initial = strtoupper(substr($memberName, 0, 1));
                                            @endphp
                                            <div class="flex items-center justify-between gap-1 text-[11px] bg-slate-50/60 hover:bg-slate-50 px-2 py-1 rounded-lg border border-slate-100 transition min-w-0">
                                                <div class="flex items-center gap-1.5 min-w-0 flex-1 truncate pr-1">
                                                    <div class="w-4 h-4 rounded-full bg-indigo-100 text-indigo-700 font-bold flex items-center justify-center text-[9px] shrink-0">
                                                        {{ $initial }}
                                                    </div>
                                                    <div class="min-w-0 flex-1 truncate">
                                                        <span class="font-semibold text-slate-700 block truncate text-[11px]">{{ $memberName }}</span>
                                                        <span class="text-[9px] text-slate-400 block truncate">{{ $m->school->name ?? 'Sekolah/Kampus' }}</span>
                                                    </div>
                                                </div>
                                                @if(!empty($m->account?->gdrive_url))
                                                    <a href="{{ $m->account->gdrive_url }}" target="_blank" rel="noopener noreferrer" title="Buka Google Drive Tugas: {{ $memberName }}" class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200/80 text-[9px] font-bold transition shrink-0">
                                                        <i class="fa-brands fa-google-drive text-amber-500 text-[10px]"></i>
                                                        <span>Drive</span>
                                                    </a>
                                                @endif
                                            </div>
                                        @empty
                                            <span class="text-[10px] text-slate-400 italic">Belum ada anggota terdata</span>
                                        @endforelse
                                    </div>
                                </div>
                            </div>

                            <!-- Card Footer: Tautan Karya Langsung -->
                            <div class="px-3 py-2 bg-slate-50/60 border-t border-slate-100 flex items-center justify-between gap-1">
                                <div class="flex items-center gap-1 flex-wrap min-w-0">
                                    @if($isProgrammer)
                                        @if($githubUrl)
                                            <a href="{{ $githubUrl }}" target="_blank" rel="noopener noreferrer"
                                                class="inline-flex items-center gap-1 px-2 py-1 bg-slate-900 hover:bg-slate-800 text-white text-[10px] font-semibold rounded-lg transition shadow-2xs shrink-0"
                                                title="Buka Repository GitHub">
                                                <i class="fa-brands fa-github text-[11px]"></i>
                                                <span>Repo Git</span>
                                            </a>
                                        @else
                                            <span class="text-[9px] text-slate-400 italic">Belum ada repo</span>
                                        @endif
                                    @elseif($isUiUx)
                                        @if($figmaUrl)
                                            <a href="{{ $figmaUrl }}" target="_blank" rel="noopener noreferrer"
                                                class="inline-flex items-center gap-1 px-2 py-1 bg-purple-600 hover:bg-purple-700 text-white text-[10px] font-semibold rounded-lg transition shadow-2xs shrink-0"
                                                title="Buka Project Figma">
                                                <i class="fa-brands fa-figma text-[11px]"></i>
                                                <span>Figma</span>
                                            </a>
                                        @else
                                            <span class="text-[9px] text-slate-400 italic">Belum ada figma</span>
                                        @endif
                                    @else
                                        <span class="inline-flex items-center gap-1 text-[10px] text-slate-500 font-medium">
                                            <i class="fa-solid fa-check text-emerald-500 text-[9px]"></i>
                                            <span>Project Selesai</span>
                                        </span>
                                    @endif
                                </div>

                                <span class="text-[9px] text-slate-400 font-bold bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200/60 shrink-0">
                                    #{{ $project->id }}
                                </span>
                            </div>

                        </div>
                    @endforeach
                </div>

                <!-- Setting-style Pagination (CSS Grid / Responsive) -->
                @if ($projects->hasPages())
                <div class="grid grid-cols-1 sm:grid-cols-2 items-center gap-3 p-3 bg-white rounded-xl border border-slate-200/80 shadow-2xs mt-3 w-full">
                    <div class="text-xs text-slate-500 font-medium text-center sm:text-left">
                        Menampilkan <span class="font-bold text-slate-800">{{ $projects->firstItem() ?? 0 }}</span> - <span class="font-bold text-slate-800">{{ $projects->lastItem() ?? 0 }}</span> dari <span class="font-bold text-slate-800">{{ $projects->total() }}</span> project
                    </div>
                    <div class="flex items-center justify-center sm:justify-end space-x-1.5">
                        {{-- Prev Button --}}
                        @if ($projects->onFirstPage())
                            <button class="bg-gray-200 text-gray-400 px-2.5 py-1 rounded-lg text-xs font-semibold cursor-not-allowed shadow-2xs flex items-center gap-1" disabled>
                                <i class="fas fa-chevron-left text-[9px]"></i>
                                <span>Prev</span>
                            </button>
                        @else
                            <a href="{{ $projects->previousPageUrl() }}" class="bg-gray-800 text-white hover:bg-gray-900 px-2.5 py-1 rounded-lg text-xs font-semibold shadow-2xs transition flex items-center gap-1">
                                <i class="fas fa-chevron-left text-[9px]"></i>
                                <span>Prev</span>
                            </a>
                        @endif

                        {{-- Page Numbers --}}
                        <div class="flex space-x-1">
                            @foreach (range(1, $projects->lastPage()) as $page)
                                @if ($page == $projects->currentPage())
                                    <span class="px-2.5 py-1 bg-blue-600 text-white rounded-lg text-xs font-bold shadow-2xs transition">
                                        {{ $page }}
                                    </span>
                                @else
                                    <a href="{{ $projects->url($page) }}" class="px-2.5 py-1 bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-lg text-xs font-bold shadow-2xs transition">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach
                        </div>

                        {{-- Next Button --}}
                        @if ($projects->hasMorePages())
                            <a href="{{ $projects->nextPageUrl() }}" class="bg-gray-800 text-white hover:bg-gray-900 px-2.5 py-1 rounded-lg text-xs font-semibold shadow-2xs transition flex items-center gap-1">
                                <span>Next</span>
                                <i class="fas fa-chevron-right text-[9px]"></i>
                            </a>
                        @else
                            <button class="bg-gray-200 text-gray-400 px-2.5 py-1 rounded-lg text-xs font-semibold cursor-not-allowed shadow-2xs flex items-center gap-1" disabled>
                                <span>Next</span>
                                <i class="fas fa-chevron-right text-[8px]"></i>
                            </button>
                        @endif
                    </div>
                </div>
                @endif
            @else
                <!-- Empty State (CSS Grid friendly & Responsive) -->
                <div class="bg-white rounded-xl border border-slate-200 p-8 text-center space-y-3 shadow-2xs w-full">
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl mx-auto">
                        <i class="fa-regular fa-folder-open"></i>
                    </div>
                    <div class="space-y-1">
                        <h3 class="text-sm font-bold text-slate-800">Tidak Ada Project Selesai</h3>
                        <p class="text-xs text-slate-500 max-w-md mx-auto">
                            @if(!empty($searchKeyword) || (!empty($selectedDivision) && $selectedDivision !== 'all'))
                                Tidak ditemukan project selesai dengan kata kunci atau filter divisi yang dipilih.
                            @else
                                Belum ada project pemagang yang berstatus selesai (`done`). Project yang telah disahkan saat presentasi akan otomatis diarsipkan di sini.
                            @endif
                        </p>
                    </div>
                    @if(!empty($searchKeyword) || (!empty($selectedDivision) && $selectedDivision !== 'all'))
                        <div class="pt-2">
                            <a href="{{ route('admin.projects.completed') }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold transition shadow-2xs">
                                <i class="fa-solid fa-rotate-left text-[10px]"></i>
                                <span>Reset Filter</span>
                            </a>
                        </div>
                    @endif
                </div>
            @endif

        </div>
    </div>

    <!-- Select2 JS & Auto-submit initialization -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#divisionSelect').select2({
                placeholder: 'Pilih / Cari Divisi...',
                allowClear: false,
                width: '100%'
            }).on('change', function() {
                $(this).closest('form').submit();
            });
        });
    </script>
@endsection
