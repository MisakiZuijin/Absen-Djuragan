@extends('layouts.main')

@section('title', 'Portofolio Project Selesai')

@section('contents')
    @include('layouts.sidebar')

    @include('layouts.navbar', ['user' => $user])

    <!-- Main Content -->
    <main class="ml-64 mt-24 p-6 md:ml-48 lg:ml-64 min-h-screen bg-gray-50/50">
        <div class="max-w-7xl mx-auto space-y-6">

            <!-- Breadcrumb & Header Title -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 text-xs text-gray-500 mb-1">
                        <a href="{{ route('admin.home') }}" class="hover:text-indigo-600">Dashboard</a>
                        <span>/</span>
                        <span class="text-gray-700 font-medium">Portofolio Project</span>
                    </div>
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-800 flex items-center gap-2.5">
                        <i class="fa-solid fa-briefcase text-indigo-600"></i>
                        <span>Portofolio Project Selesai</span>
                    </h1>
                    <p class="text-xs md:text-sm text-gray-500 mt-1">
                        Arsip seluruh hasil karya, tugas, dan project pemagang yang telah selesai dan tervalidasi lintas divisi
                    </p>
                </div>
            </div>

            <!-- Summary Metric Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Card 1: Total Project Selesai -->
                <div class="bg-white rounded-2xl p-5 border border-gray-200 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider block">Total Project Selesai</span>
                        <span class="text-2xl md:text-3xl font-black text-indigo-900 mt-1 block">{{ $totalCompletedProjects }}</span>
                        <span class="text-[11px] text-gray-400 mt-0.5 block">Tervalidasi & disahkan</span>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 text-xl flex-shrink-0">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                </div>

                <!-- Card 2: Total Pemagang Terlibat -->
                <div class="bg-white rounded-2xl p-5 border border-gray-200 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider block">Pemagang Berkontribusi</span>
                        <span class="text-2xl md:text-3xl font-black text-emerald-900 mt-1 block">{{ $totalInternsInvolved }}</span>
                        <span class="text-[11px] text-gray-400 mt-0.5 block">Alumni penugasan project</span>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 text-xl flex-shrink-0">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>

                <!-- Card 3: Divisi Terlibat -->
                <div class="bg-white rounded-2xl p-5 border border-gray-200 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider block">Total Divisi Aktif</span>
                        <span class="text-2xl md:text-3xl font-black text-violet-900 mt-1 block">{{ count($divisions) }}</span>
                        <span class="text-[11px] text-gray-400 mt-0.5 block">Kategori penugasan</span>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-violet-50 border border-violet-100 flex items-center justify-center text-violet-600 text-xl flex-shrink-0">
                        <i class="fa-solid fa-sitemap"></i>
                    </div>
                </div>
            </div>

            <!-- Filter & Search Toolbar -->
            <div class="bg-white rounded-2xl p-5 border border-gray-200 shadow-sm space-y-4">
                <form method="GET" action="{{ route('admin.projects.completed') }}" class="flex flex-col md:flex-row gap-3">
                    @if($selectedDivision)
                        <input type="hidden" name="division_id" value="{{ $selectedDivision }}">
                    @endif
                    <div class="relative flex-1">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input type="text" name="search" value="{{ $searchKeyword ?? '' }}"
                            placeholder="Cari nama project, nama tim, pemagang, atau nama kampus..."
                            class="w-full pl-10 pr-4 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold transition flex items-center gap-2 shadow-sm">
                            <i class="fa-solid fa-filter"></i>
                            <span>Cari</span>
                        </button>
                        @if(!empty($searchKeyword) || (!empty($selectedDivision) && $selectedDivision !== 'all'))
                            <a href="{{ route('admin.projects.completed') }}" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-sm font-medium transition" title="Reset Pencarian">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>

                <!-- Division Filter Pills with Mouse Scroll & Nav Buttons -->
                <div class="pt-3 border-t border-gray-100 flex items-center gap-2">
                    <span class="text-xs font-semibold text-gray-500 whitespace-nowrap mr-1 flex items-center gap-1.5">
                        <i class="fa-solid fa-layer-group text-indigo-500"></i>
                        <span>Filter Divisi:</span>
                    </span>

                    <button type="button" onclick="scrollDivisionNav(-200)"
                        class="hidden sm:flex items-center justify-center w-7 h-7 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-600 transition flex-shrink-0 cursor-pointer"
                        title="Geser ke kiri">
                        <i class="fa-solid fa-chevron-left text-[10px]"></i>
                    </button>

                    <div id="division-filter-scroll"
                        class="flex items-center gap-2 overflow-x-auto py-1.5 scrollbar-thin scroll-smooth flex-1 select-none cursor-grab active:cursor-grabbing">
                        <a href="{{ route('admin.projects.completed', array_merge(request()->query(), ['division_id' => 'all'])) }}"
                            class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition whitespace-nowrap flex-shrink-0 {{ (empty($selectedDivision) || $selectedDivision === 'all') ? 'bg-indigo-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                            Semua ({{ $totalCompletedProjects }})
                        </a>

                        @foreach($divisions as $div)
                            <a href="{{ route('admin.projects.completed', array_merge(request()->query(), ['division_id' => $div->id])) }}"
                                class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition whitespace-nowrap flex items-center gap-1.5 flex-shrink-0 {{ ($selectedDivision == $div->id) ? 'bg-indigo-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                                <span>{{ $div->name }}</span>
                                <span class="text-[10px] px-1.5 py-0.2 rounded-full {{ ($selectedDivision == $div->id) ? 'bg-indigo-700 text-indigo-100' : 'bg-gray-200 text-gray-600' }}">
                                    {{ $div->completed_count }}
                                </span>
                            </a>
                        @endforeach
                    </div>

                    <button type="button" onclick="scrollDivisionNav(200)"
                        class="hidden sm:flex items-center justify-center w-7 h-7 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-600 transition flex-shrink-0 cursor-pointer"
                        title="Geser ke kanan">
                        <i class="fa-solid fa-chevron-right text-[10px]"></i>
                    </button>
                </div>
            </div>

            <!-- Project Cards Grid -->
            @if($projects->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($projects as $project)
                        @php
                            // Ambil anggota unik & divisi project
                            $members = $project->detailProjects->map(fn($dp) => $dp->intern)->filter()->unique('id');
                            $firstDivision = $members->first()?->division;
                            $divName = $firstDivision->name ?? 'Umum';
                            $divNameLower = strtolower($divName);

                            // Deteksi divisi secara ketat (khusus Programmer & UI/UX)
                            $divId = (int) ($firstDivision?->id ?? 0);
                            $isProgrammer = ($divId === 4)
                                || str_contains($divNameLower, 'programmer')
                                || str_contains($divNameLower, 'program');

                            $isUiUx = ($divId === 1)
                                || str_contains($divNameLower, 'ui/ux')
                                || str_contains($divNameLower, 'ui / ux')
                                || (str_contains($divNameLower, 'ui') && str_contains($divNameLower, 'ux'));

                            // Tautan karya project & anggota
                            $repoUrl = $project->repository_url;

                            // GitHub: hanya diambil jika relevan dari repository project
                            $githubUrl = (!empty($repoUrl) && !str_contains($repoUrl, 'figma')) ? $repoUrl : null;

                            // Figma: diambil jika ada link figma di repository project
                            $figmaUrl = (!empty($repoUrl) && str_contains($repoUrl, 'figma')) ? $repoUrl : null;

                            // Google Drive tugas
                            $gdriveUrl = $members->first(fn($m) => !empty($m->account?->gdrive_url))?->account?->gdrive_url;
                        @endphp
                        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between overflow-hidden">
                            
                            <!-- Card Header & Project Info -->
                            <div class="p-5 space-y-3">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200/60 truncate max-w-[200px]">
                                        <i class="fa-solid fa-briefcase text-indigo-500 text-[11px]"></i>
                                        <span>{{ $divName }}</span>
                                    </span>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 shrink-0">
                                        <i class="fa-solid fa-circle-check text-emerald-600"></i>
                                        <span>Selesai</span>
                                    </span>
                                </div>

                                <div>
                                    <h3 class="font-bold text-gray-900 text-base leading-snug group-hover:text-indigo-600 transition">
                                        {{ $project->nameProject->name ?? 'Project Selesai' }}
                                    </h3>
                                    @if(!empty($project->team))
                                        <p class="text-xs text-gray-500 font-medium mt-0.5 flex items-center gap-1">
                                            <i class="fa-solid fa-user-group text-gray-400 text-[11px]"></i>
                                            <span>Tim: {{ $project->team }}</span>
                                        </p>
                                    @endif
                                </div>

                                @if(!empty($project->description))
                                    <p class="text-xs text-gray-600 line-clamp-3 leading-relaxed bg-gray-50 p-2.5 rounded-xl border border-gray-100">
                                        {{ $project->description }}
                                    </p>
                                @endif

                                <!-- Daftar Anggota Tim Pemagang -->
                                <div class="pt-2 border-t border-gray-100 space-y-1.5">
                                    <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider block">Anggota Pemagang:</span>
                                    <div class="space-y-1.5">
                                        @forelse($members as $m)
                                            @php
                                                $memberName = $m->user?->profile?->full_name ?? $m->user?->username ?? 'Pemagang';
                                                $initial = strtoupper(substr($memberName, 0, 1));
                                            @endphp
                                            <div class="flex items-center justify-between text-xs bg-slate-50 p-1.5 rounded-lg border border-slate-100">
                                                <div class="flex items-center gap-2 truncate">
                                                    <div class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 font-bold flex items-center justify-center text-[10px] shrink-0">
                                                        {{ $initial }}
                                                    </div>
                                                    <div class="truncate">
                                                        <span class="font-semibold text-gray-800 block truncate">{{ $memberName }}</span>
                                                        <span class="text-[10px] text-gray-500 block truncate">{{ $m->school->name ?? 'Sekolah/Kampus' }}</span>
                                                    </div>
                                                </div>
                                                @if(!empty($m->account?->gdrive_url))
                                                    <a href="{{ $m->account->gdrive_url }}" target="_blank" title="Buka GDrive Tugas Pemagang" class="text-indigo-600 hover:text-indigo-800 p-1">
                                                        <i class="fa-brands fa-google-drive text-amber-500"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        @empty
                                            <span class="text-xs text-gray-400 italic">Belum ada anggota terdata</span>
                                        @endforelse
                                    </div>
                                </div>
                            </div>

                            <!-- Card Footer: Tautan Karya Langsung -->
                            <div class="px-5 py-3.5 bg-gray-50 border-t border-gray-100 flex items-center justify-between gap-2">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    @if($isProgrammer)
                                        {{-- Divisi Programmer: HANYA tampilkan GitHub, Figma TIDAK AKAN MUNCUL --}}
                                        @if($githubUrl)
                                            <a href="{{ $githubUrl }}" target="_blank" rel="noopener noreferrer"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-900 hover:bg-black text-white text-xs font-semibold rounded-lg transition shadow-sm"
                                                title="Buka Repository GitHub">
                                                <i class="fa-brands fa-github text-sm"></i>
                                                <span>Repo Git</span>
                                            </a>
                                        @elseif($gdriveUrl)
                                            <a href="{{ $gdriveUrl }}" target="_blank" rel="noopener noreferrer"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 text-xs font-semibold rounded-lg transition"
                                                title="Buka Folder Google Drive Tugas">
                                                <i class="fa-brands fa-google-drive text-amber-500 text-sm"></i>
                                                <span>Drive Tugas</span>
                                            </a>
                                        @else
                                            <span class="text-[11px] text-gray-400 italic">Belum ada repo git</span>
                                        @endif
                                    @elseif($isUiUx)
                                        {{-- Divisi UI/UX: HANYA tampilkan Figma, GitHub TIDAK AKAN MUNCUL --}}
                                        @if($figmaUrl)
                                            <a href="{{ $figmaUrl }}" target="_blank" rel="noopener noreferrer"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-purple-600 hover:bg-purple-700 text-white text-xs font-semibold rounded-lg transition shadow-sm"
                                                title="Buka Project Figma">
                                                <i class="fa-brands fa-figma text-sm"></i>
                                                <span>Figma</span>
                                            </a>
                                        @elseif($gdriveUrl)
                                            <a href="{{ $gdriveUrl }}" target="_blank" rel="noopener noreferrer"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 text-xs font-semibold rounded-lg transition"
                                                title="Buka Folder Google Drive Tugas">
                                                <i class="fa-brands fa-google-drive text-amber-500 text-sm"></i>
                                                <span>Drive Tugas</span>
                                            </a>
                                        @else
                                            <span class="text-[11px] text-gray-400 italic">Belum ada link figma</span>
                                        @endif
                                    @else
                                        {{-- Divisi Lain: Tidak menggunakan link pada task (hanya GDrive tugas jika ada) --}}
                                        @if($gdriveUrl)
                                            <a href="{{ $gdriveUrl }}" target="_blank" rel="noopener noreferrer"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 text-xs font-semibold rounded-lg transition"
                                                title="Buka Folder Google Drive Tugas">
                                                <i class="fa-brands fa-google-drive text-amber-500 text-sm"></i>
                                                <span>Drive Tugas</span>
                                            </a>
                                        @else
                                            <span class="text-[11px] text-gray-400 italic">Project selesai</span>
                                        @endif
                                    @endif
                                </div>

                                <span class="text-[11px] text-gray-400 font-medium">
                                    ID #{{ $project->id }}
                                </span>
                            </div>

                        </div>
                    @endforeach
                </div>

                <!-- Pagination Container -->
                <div class="pt-4">
                    {{ $projects->links() }}
                </div>
            @else
                <!-- Empty State -->
                <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center space-y-4 shadow-sm">
                    <div class="w-16 h-16 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-3xl mx-auto">
                        <i class="fa-regular fa-folder-open"></i>
                    </div>
                    <div class="space-y-1">
                        <h3 class="text-base font-bold text-gray-800">Tidak Ada Project Selesai</h3>
                        <p class="text-xs text-gray-500 max-w-md mx-auto">
                            @if(!empty($searchKeyword) || (!empty($selectedDivision) && $selectedDivision !== 'all'))
                                Tidak ditemukan project selesai dengan kata kunci atau filter divisi yang dipilih. Silakan reset filter untuk melihat semua data.
                            @else
                                Belum ada project pemagang yang berstatus selesai (`done`). Project yang telah disahkan saat presentasi akan otomatis diarsipkan di sini.
                            @endif
                        </p>
                    </div>
                    @if(!empty($searchKeyword) || (!empty($selectedDivision) && $selectedDivision !== 'all'))
                        <div>
                            <a href="{{ route('admin.projects.completed') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold transition">
                                <i class="fa-solid fa-rotate-left"></i>
                                <span>Reset Filter</span>
                            </a>
                        </div>
                    @endif
                </div>
            @endif

        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const scrollContainer = document.getElementById('division-filter-scroll');
            if (!scrollContainer) return;

            // 1. Scroll menggunakan mouse wheel (vertikal scroll mouse diterjemahkan ke geser horizontal)
            scrollContainer.addEventListener('wheel', function (e) {
                if (e.deltaY !== 0) {
                    e.preventDefault();
                    scrollContainer.scrollLeft += (e.deltaY * 1.5);
                }
            }, { passive: false });

            // 2. Drag-to-scroll dengan mouse (klik dan geser)
            let isDown = false;
            let startX;
            let scrollLeft;

            scrollContainer.addEventListener('mousedown', (e) => {
                isDown = true;
                scrollContainer.classList.add('cursor-grabbing');
                startX = e.pageX - scrollContainer.offsetLeft;
                scrollLeft = scrollContainer.scrollLeft;
            });

            scrollContainer.addEventListener('mouseleave', () => {
                isDown = false;
                scrollContainer.classList.remove('cursor-grabbing');
            });

            scrollContainer.addEventListener('mouseup', () => {
                isDown = false;
                scrollContainer.classList.remove('cursor-grabbing');
            });

            scrollContainer.addEventListener('mousemove', (e) => {
                if (!isDown) return;
                e.preventDefault();
                const x = e.pageX - scrollContainer.offsetLeft;
                const walk = (x - startX) * 1.8;
                scrollContainer.scrollLeft = scrollLeft - walk;
            });
        });

        function scrollDivisionNav(amount) {
            const container = document.getElementById('division-filter-scroll');
            if (container) {
                container.scrollBy({ left: amount, behavior: 'smooth' });
            }
        }
    </script>
@endsection
