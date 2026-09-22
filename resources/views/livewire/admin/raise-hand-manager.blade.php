<div wire:poll.3s class="w-full min-w-0 max-w-none">

    <!-- =========================================================
         QUICK STATS CARDS
         ========================================================= -->
    <div class="grid w-full min-w-0 grid-cols-1 gap-4 mb-6 sm:grid-cols-2 xl:grid-cols-4">

        <!-- 1. Bertanya -->
        <div
            wire:click="switchTab('question')"
            class="w-full min-w-0 cursor-pointer rounded-2xl border bg-white p-5 transition-all
                hover:border-blue-300 hover:shadow-md
                {{ $activeTab === 'question'
                    ? 'border-blue-500 ring-2 ring-blue-500/20 shadow-md'
                    : 'border-gray-200 shadow-sm' }}">
            <div class="flex items-center justify-between gap-4">
                <div class="min-w-0">
                    <div class="text-xs font-semibold uppercase tracking-wider text-gray-500">
                        Bertanya
                    </div>

                    <div class="mt-1 text-2xl font-black text-gray-900">
                        {{ $countQuestions }}
                    </div>

                    <div class="mt-0.5 text-[11px] text-gray-400">
                        Konsultasi kendala teknis
                    </div>
                </div>

                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-xl text-blue-600">
                    <i class="fa-solid fa-comments"></i>
                </div>
            </div>
        </div>

        <!-- 2. Tugas Baru -->
        <div
            wire:click="switchTab('new_task')"
            class="w-full min-w-0 cursor-pointer rounded-2xl border bg-white p-5 transition-all
                hover:border-purple-300 hover:shadow-md
                {{ $activeTab === 'new_task'
                    ? 'border-purple-500 ring-2 ring-purple-500/20 shadow-md'
                    : 'border-gray-200 shadow-sm' }}">
            <div class="flex items-center justify-between gap-4">
                <div class="min-w-0">
                    <div class="text-xs font-semibold uppercase tracking-wider text-gray-500">
                        Tugas Baru
                    </div>

                    <div class="mt-1 text-2xl font-black text-gray-900">
                        {{ $countNewTasks }}
                    </div>

                    <div class="mt-0.5 text-[11px] text-gray-400">
                        Tugas selesai butuh modul
                    </div>
                </div>

                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-purple-50 text-xl text-purple-600">
                    <i class="fa-solid fa-list-check"></i>
                </div>
            </div>
        </div>

        <!-- 3. Presentasi -->
        <div
            wire:click="switchTab('presentation')"
            class="relative w-full min-w-0 cursor-pointer rounded-2xl border bg-white p-5 transition-all
                hover:border-amber-300 hover:shadow-md
                {{ $activeTab === 'presentation'
                    ? 'border-amber-500 ring-2 ring-amber-500/20 shadow-md'
                    : 'border-gray-200 shadow-sm' }}">
            @if($countUrgentPresentations > 0)
            <span class="absolute right-3 top-3 rounded-full bg-red-600 px-2 py-0.5 text-[10px] font-black text-white animate-pulse">
                {{ $countUrgentPresentations }} HARI INI
            </span>
            @endif

            <div class="flex items-center justify-between gap-4">
                <div class="min-w-0">
                    <div class="text-xs font-semibold uppercase tracking-wider text-gray-500">
                        Jadwal Presentasi
                    </div>

                    <div class="mt-1 text-2xl font-black text-gray-900">
                        {{ $countPresentations }}
                    </div>

                    <div class="mt-0.5 text-[11px] text-gray-400">
                        @if($countUrgentPresentations > 0)
                        <strong class="text-red-600">
                            {{ $countUrgentPresentations }} prioritas hari ini
                        </strong>
                        @else
                        Menunggu jadwal uji
                        @endif
                    </div>
                </div>

                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-xl text-amber-600">
                    <i class="fa-solid fa-chalkboard-user"></i>
                </div>
            </div>
        </div>

        <!-- 4. History Selesai -->
        <div
            wire:click="switchTab('history')"
            class="w-full min-w-0 cursor-pointer rounded-2xl border bg-white p-5 transition-all
                hover:border-green-300 hover:shadow-md
                {{ $activeTab === 'history'
                    ? 'border-emerald-500 ring-2 ring-emerald-500/20 shadow-md'
                    : 'border-gray-200 shadow-sm' }}">
            <div class="flex items-center justify-between gap-4">
                <div class="min-w-0">
                    <div class="text-xs font-semibold uppercase tracking-wider text-gray-500">
                        History Selesai
                    </div>

                    <div class="mt-1 text-2xl font-black text-gray-900">
                        {{ $countDone }}
                    </div>

                    <div class="mt-0.5 text-[11px] text-gray-400">
                        Rekap tanggapan & nilai
                    </div>
                </div>

                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-xl text-emerald-600">
                    <i class="fa-solid fa-clipboard-check"></i>
                </div>
            </div>
        </div>

    </div>


    <!-- =========================================================
         SEARCH BAR
         ========================================================= -->
    <div class="mb-6 w-full min-w-0 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
        <div class="flex w-full min-w-0 items-center gap-3">

            <div class="relative min-w-0 flex-1">
                <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                    <i class="fa-solid fa-magnifying-glass text-sm"></i>
                </div>

                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    class="w-full rounded-xl border border-gray-200 bg-gray-50/50 py-2.5 pl-11 pr-10
                        text-xs text-gray-700
                        focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                    placeholder="Cari nama peserta, sekolah, materi, atau catatan..."
                    autocomplete="off">

                @if(!empty($search))
                <button
                    type="button"
                    wire:click="$set('search', '')"
                    class="absolute right-3 top-1/2 flex h-6 w-6 -translate-y-1/2 items-center justify-center
                            rounded-full text-gray-400 hover:text-red-500">
                    <i class="fa-solid fa-xmark text-xs"></i>
                </button>
                @endif
            </div>

            <div class="hidden shrink-0 items-center gap-1.5 px-2 text-xs text-gray-400 sm:flex">
                <i class="fa-solid fa-filter text-gray-400"></i>
                <span>Pencarian</span>
            </div>

        </div>
    </div>


    <!-- =========================================================
         TAB + CONTENT
         ========================================================= -->
    <div class="w-full min-w-0 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">

        <!-- Tab Navigation -->
        <div class="w-full min-w-0 border-b border-gray-200 bg-gray-50/50 px-4 pt-3">
            <div class="grid w-full min-w-0 grid-cols-1 gap-1 sm:grid-cols-2 xl:grid-cols-4">

                <!-- Tab 1 -->
                <button
                    type="button"
                    wire:click="switchTab('question')"
                    class="admin-tab-btn flex min-w-0 items-center justify-center gap-2 rounded-t-xl
                        border-b-2 px-4 py-3 text-xs transition-all
                        {{ $activeTab === 'question'
                            ? 'border-blue-600 bg-white font-bold text-blue-600 shadow-xs'
                            : 'border-transparent font-semibold text-gray-500 hover:text-gray-800' }}">
                    <i class="fa-solid fa-comments shrink-0"></i>

                    <span class="truncate">
                        1. Bertanya
                    </span>

                    <span
                        class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-black
                        {{ $activeTab === 'question'
                            ? 'bg-blue-100 text-blue-800'
                            : 'bg-gray-200 text-gray-700' }}">
                        {{ $countQuestions }}
                    </span>
                </button>


                <!-- Tab 2 -->
                <button
                    type="button"
                    wire:click="switchTab('new_task')"
                    class="admin-tab-btn flex min-w-0 items-center justify-center gap-2 rounded-t-xl
                        border-b-2 px-4 py-3 text-xs transition-all
                        {{ $activeTab === 'new_task'
                            ? 'border-purple-600 bg-white font-bold text-purple-600 shadow-xs'
                            : 'border-transparent font-semibold text-gray-500 hover:text-gray-800' }}">
                    <i class="fa-solid fa-list-check shrink-0"></i>

                    <span class="truncate">
                        2. Permintaan Tugas Baru
                    </span>

                    <span
                        class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-black
                        {{ $activeTab === 'new_task'
                            ? 'bg-purple-100 text-purple-800'
                            : 'bg-gray-200 text-gray-700' }}">
                        {{ $countNewTasks }}
                    </span>
                </button>


                <!-- Tab 3 -->
                <button
                    type="button"
                    wire:click="switchTab('presentation')"
                    class="admin-tab-btn flex min-w-0 items-center justify-center gap-2 rounded-t-xl
                        border-b-2 px-4 py-3 text-xs transition-all
                        {{ $activeTab === 'presentation'
                            ? 'border-amber-600 bg-white font-bold text-amber-600 shadow-xs'
                            : 'border-transparent font-semibold text-gray-500 hover:text-gray-800' }}">
                    <i class="fa-solid fa-chalkboard-user shrink-0"></i>

                    <span class="truncate">
                        3. Penjadwalan Presentasi
                    </span>

                    <span
                        class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-black
                        {{ $activeTab === 'presentation'
                            ? 'bg-amber-100 text-amber-800'
                            : 'bg-gray-200 text-gray-700' }}">
                        {{ $countPresentations }}
                    </span>

                    @if($countUrgentPresentations > 0)
                    <span class="shrink-0 rounded bg-red-600 px-1.5 py-0.5 text-[9px] font-black text-white animate-pulse">
                        URGENT
                    </span>
                    @endif
                </button>


                <!-- Tab 4 -->
                <button
                    type="button"
                    wire:click="switchTab('history')"
                    class="admin-tab-btn flex min-w-0 items-center justify-center gap-2 rounded-t-xl
                        border-b-2 px-4 py-3 text-xs transition-all
                        {{ $activeTab === 'history'
                            ? 'border-emerald-600 bg-white font-bold text-emerald-600 shadow-xs'
                            : 'border-transparent font-semibold text-gray-500 hover:text-gray-800' }}">
                    <i class="fa-solid fa-clipboard-check shrink-0"></i>

                    <span class="truncate">
                        4. History Selesai
                    </span>

                    <span
                        class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-black
                        {{ $activeTab === 'history'
                            ? 'bg-emerald-100 text-emerald-800'
                            : 'bg-gray-200 text-gray-700' }}">
                        {{ $countDone }}
                    </span>
                </button>

            </div>
        </div>


        <!-- =====================================================
             CONTENT TABLE
             ===================================================== -->
        <div class="w-full min-w-0 p-4 sm:p-6">

            @if($activeTab === 'question')

            <!-- Panel 1: Bertanya -->
            <div class="w-full min-w-0 overflow-x-auto rounded-xl border border-gray-200">
                <table class="w-full min-w-[960px] text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/80 border-b border-gray-200 text-[11px] font-bold uppercase tracking-wider text-gray-600">
                            <th class="w-12 px-4 py-3.5 text-center">#</th>
                            <th class="w-72 min-w-[260px] px-4 py-3.5">Peserta & Asal Sekolah</th>
                            <th class="min-w-[320px] px-4 py-3.5">Detail Pertanyaan / Kendala</th>
                            <th class="w-48 min-w-[160px] px-4 py-3.5">Status</th>
                            <th class="w-40 min-w-[140px] px-4 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @include('admin.partials.raise-hand-table-body', ['handRaises' => $items, 'activeTab' => 'question'])
                    </tbody>
                </table>
            </div>


            @elseif($activeTab === 'new_task')

            <!-- Panel 2: Permintaan Tugas Baru -->
            <div class="w-full min-w-0 overflow-x-auto rounded-xl border border-gray-200">
                <table class="w-full min-w-[1050px] text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/80 border-b border-gray-200 text-[11px] font-bold uppercase tracking-wider text-gray-600">
                            <th class="w-12 px-4 py-3.5 text-center">#</th>
                            <th class="w-72 min-w-[260px] px-4 py-3.5">Peserta & Asal Sekolah</th>
                            <th class="min-w-[320px] px-4 py-3.5">Rincian Tugas & Permintaan</th>
                            <th class="w-48 min-w-[160px] px-4 py-3.5">Status</th>
                            <th class="w-64 min-w-[230px] px-4 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @include('admin.partials.raise-hand-table-body', ['handRaises' => $items, 'activeTab' => 'new_task'])
                    </tbody>
                </table>
            </div>


            @elseif($activeTab === 'presentation')

            <!-- Panel 3: Penjadwalan Presentasi -->
            <div class="w-full min-w-0 overflow-x-auto rounded-xl border border-gray-200">
                <table class="w-full min-w-[1200px] text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/80 border-b border-gray-200 text-[11px] font-bold uppercase tracking-wider text-gray-600">
                            <th class="w-12 px-4 py-3.5 text-center">#</th>
                            <th class="w-64 min-w-[240px] px-4 py-3.5">Peserta & Asal Sekolah</th>
                            <th class="min-w-[280px] px-4 py-3.5">Judul / Materi Presentasi</th>
                            <th class="w-52 min-w-[180px] px-4 py-3.5">Jadwal & Mode</th>
                            <th class="w-48 min-w-[160px] px-4 py-3.5">Urgensi & Review</th>
                            <th class="w-72 min-w-[260px] px-4 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @include('admin.partials.raise-hand-table-body', ['handRaises' => $items, 'activeTab' => 'presentation'])
                    </tbody>
                </table>
            </div>


            @else

            <!-- Panel 4: History Selesai -->
            <div class="w-full min-w-0 space-y-4">

                <!-- Sub-Tabs History & Filter Per Divisi -->
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 bg-gray-50/90 p-3 rounded-2xl border border-gray-200 shadow-2xs">
                    <!-- 3 Sub-Tabs History -->
                    <div class="flex items-center gap-2 flex-wrap">
                        <!-- 1. Meminta Bantuan -->
                        <button
                            type="button"
                            wire:click="switchHistoryTab('question')"
                            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition shadow-2xs cursor-pointer
                            {{ $historyTab === 'question'
                                ? 'bg-blue-600 text-white shadow-blue-500/20'
                                : 'bg-white text-gray-600 hover:bg-gray-100 hover:text-gray-900 border border-gray-200' }}">
                            <i class="fa-solid fa-comments text-xs"></i>
                            <span>1. Meminta Bantuan</span>
                            <span class="px-1.5 py-0.5 rounded-full text-[10px] font-black {{ $historyTab === 'question' ? 'bg-white/25 text-white' : 'bg-gray-100 text-gray-700' }}">
                                {{ $countHistoryQuestion }}
                            </span>
                        </button>

                        <!-- 2. Tugas Baru -->
                        <button
                            type="button"
                            wire:click="switchHistoryTab('new_task')"
                            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition shadow-2xs cursor-pointer
                            {{ $historyTab === 'new_task'
                                ? 'bg-purple-600 text-white shadow-purple-500/20'
                                : 'bg-white text-gray-600 hover:bg-gray-100 hover:text-gray-900 border border-gray-200' }}">
                            <i class="fa-solid fa-list-check text-xs"></i>
                            <span>2. Tugas Baru</span>
                            <span class="px-1.5 py-0.5 rounded-full text-[10px] font-black {{ $historyTab === 'new_task' ? 'bg-white/25 text-white' : 'bg-gray-100 text-gray-700' }}">
                                {{ $countHistoryNewTask }}
                            </span>
                        </button>

                        <!-- 3. Presentasi -->
                        <button
                            type="button"
                            wire:click="switchHistoryTab('presentation')"
                            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition shadow-2xs cursor-pointer
                            {{ $historyTab === 'presentation'
                                ? 'bg-amber-600 text-white shadow-amber-500/20'
                                : 'bg-white text-gray-600 hover:bg-gray-100 hover:text-gray-900 border border-gray-200' }}">
                            <i class="fa-solid fa-chalkboard-user text-xs"></i>
                            <span>3. Presentasi</span>
                            <span class="px-1.5 py-0.5 rounded-full text-[10px] font-black {{ $historyTab === 'presentation' ? 'bg-white/25 text-white' : 'bg-gray-100 text-gray-700' }}">
                                {{ $countHistoryPresentation }}
                            </span>
                        </button>
                    </div>

                    <!-- Filter Per Divisi -->
                    <div class="flex items-center gap-2 self-start md:self-auto">
                        <label for="history-division-filter" class="text-xs font-bold text-gray-500 flex items-center gap-1.5 whitespace-nowrap">
                            <i class="fa-solid fa-filter text-gray-400"></i>
                            <span>Divisi:</span>
                        </label>
                        <select
                            id="history-division-filter"
                            wire:model.live="selectedDivision"
                            class="text-xs py-2 pl-3 pr-8 rounded-xl border border-gray-200 bg-white text-gray-800 font-semibold focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 shadow-2xs cursor-pointer">
                            <option value="">Semua Divisi</option>
                            @foreach($divisions as $div)
                            <option value="{{ $div->id }}">{{ $div->name }}</option>
                            @endforeach
                        </select>
                        @if(!empty($selectedDivision))
                        <button
                            type="button"
                            wire:click="$set('selectedDivision', '')"
                            class="h-8 w-8 rounded-xl bg-gray-200/70 hover:bg-red-100 text-gray-500 hover:text-red-600 flex items-center justify-center transition cursor-pointer"
                            title="Reset Filter Divisi">
                            <i class="fa-solid fa-xmark text-xs"></i>
                        </button>
                        @endif
                    </div>
                </div>

                <!-- History Table -->
                <div class="w-full min-w-0 overflow-x-auto rounded-xl border border-gray-200">
                    <table class="w-full min-w-[1150px] text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/80 border-b border-gray-200 text-[11px] font-bold uppercase tracking-wider text-gray-600">
                                <th class="w-12 px-4 py-3.5 text-center">#</th>
                                <th class="w-64 min-w-[240px] px-4 py-3.5">Peserta & Asal Sekolah</th>
                                @if($historyTab === 'presentation')
                                <th class="min-w-[280px] px-4 py-3.5">Materi & Jadwal Presentasi</th>
                                <th class="w-48 min-w-[170px] px-4 py-3.5">Waktu Selesai & Penguji</th>
                                <th class="min-w-[280px] px-4 py-3.5">Nilai & Evaluasi Presentasi</th>
                                @elseif($historyTab === 'new_task')
                                <th class="min-w-[280px] px-4 py-3.5">Permintaan / Laporan Tugas</th>
                                <th class="w-48 min-w-[170px] px-4 py-3.5">Waktu Selesai & Petugas</th>
                                <th class="min-w-[280px] px-4 py-3.5">Instruksi Tugas yang Diberikan</th>
                                @else
                                <th class="min-w-[280px] px-4 py-3.5">Pertanyaan / Kendala Siswa</th>
                                <th class="w-48 min-w-[170px] px-4 py-3.5">Waktu Selesai & Petugas</th>
                                <th class="min-w-[280px] px-4 py-3.5">Tanggapan & Solusi Mentor</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @include('admin.partials.raise-hand-table-body', [
                            'handRaises' => $items,
                            'activeTab' => 'history',
                            'historySubTab' => $historyTab
                            ])
                        </tbody>
                    </table>
                </div>

            </div>

            @endif

        </div>
    </div>

</div>