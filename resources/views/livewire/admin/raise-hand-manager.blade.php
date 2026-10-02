<div @if(isset($pollInterval) && $pollInterval > 0) wire:poll.{{ $pollInterval }}s @endif class="w-full min-w-0 max-w-none">
@php
$isAssistantAdmin = auth()->check() && (int) auth()->user()->role_id === 6;
@endphp

    <!-- =========================================================
         QUICK STATS CARDS
         ========================================================= -->
    <div class="grid w-full min-w-0 grid-cols-1 gap-4 mb-6 {{ $isAssistantAdmin ? 'sm:grid-cols-3 xl:grid-cols-3' : 'sm:grid-cols-2 xl:grid-cols-4' }}">

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

        @if(!$isAssistantAdmin)
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
                        Rekap riwayat selesai
                    </div>
                </div>

                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-xl text-emerald-600">
                    <i class="fa-solid fa-clipboard-check"></i>
                </div>
            </div>
        </div>
        @endif

    </div>




    <!-- =========================================================
         TAB + CONTENT
         ========================================================= -->
    <div class="w-full min-w-0 rounded-2xl border border-gray-200 bg-white shadow-sm">

        <!-- Tab Navigation -->
        <div class="w-full min-w-0 border-b border-gray-200 bg-gray-50/50 px-4 pt-3 rounded-t-2xl">
            <div class="grid w-full min-w-0 grid-cols-1 gap-1 {{ $isAssistantAdmin ? 'sm:grid-cols-3 xl:grid-cols-3' : 'sm:grid-cols-2 xl:grid-cols-4' }}">

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

                @if(!$isAssistantAdmin)
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
                @endif

            </div>
        </div>


        <!-- =====================================================
             CONTENT TABLE
             ===================================================== -->
        <div class="w-full min-w-0 p-4 sm:p-6">

            @if($activeTab === 'question')

            <!-- Panel 1: Bertanya (Item Cards List) -->
            <div class="w-full space-y-4">
                @include('admin.partials.raise-hand-table-body', ['handRaises' => $items, 'activeTab' => 'question'])
            </div>


            @elseif($activeTab === 'new_task')

            <!-- Panel 2: Permintaan Tugas Baru (Item Cards List) -->
            <div class="w-full space-y-4">
                @include('admin.partials.raise-hand-table-body', ['handRaises' => $items, 'activeTab' => 'new_task'])
            </div>


            @elseif($activeTab === 'presentation')

            <!-- Panel 3: Penjadwalan Presentasi (Item Cards List) -->
            <div class="w-full space-y-4">
                @include('admin.partials.raise-hand-table-body', ['handRaises' => $items, 'activeTab' => 'presentation'])
            </div>



            @else

            <!-- Panel 4: History Selesai -->
            <div class="w-full space-y-4">

                <!-- Sub-Tabs History & Filter Bar (Compact & Rapi) -->
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-2.5 bg-gray-50/80 p-2 sm:p-2.5 rounded-xl border border-gray-200/80 shadow-2xs">
                    <!-- 3 Sub-Tabs History -->
                    <div class="inline-flex items-center gap-1.5 flex-wrap">
                        <!-- 1. Meminta Bantuan -->
                        <button
                            type="button"
                            wire:click="switchHistoryTab('question')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[11px] font-bold transition shadow-2xs cursor-pointer
                            {{ $historyTab === 'question'
                                ? 'bg-blue-600 text-white shadow-blue-500/20'
                                : 'bg-white text-gray-600 hover:bg-gray-100 hover:text-gray-900 border border-gray-200' }}">
                            <i class="fa-solid fa-comments text-[10px]"></i>
                            <span>1. Meminta Bantuan</span>
                            <span class="px-1.5 py-0.5 rounded-full text-[9px] font-black {{ $historyTab === 'question' ? 'bg-white/25 text-white' : 'bg-gray-100 text-gray-700' }}">
                                {{ $countHistoryQuestion }}
                            </span>
                        </button>

                        <!-- 2. Tugas Baru -->
                        <button
                            type="button"
                            wire:click="switchHistoryTab('new_task')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[11px] font-bold transition shadow-2xs cursor-pointer
                            {{ $historyTab === 'new_task'
                                ? 'bg-purple-600 text-white shadow-purple-500/20'
                                : 'bg-white text-gray-600 hover:bg-gray-100 hover:text-gray-900 border border-gray-200' }}">
                            <i class="fa-solid fa-list-check text-[10px]"></i>
                            <span>2. Tugas Baru</span>
                            <span class="px-1.5 py-0.5 rounded-full text-[9px] font-black {{ $historyTab === 'new_task' ? 'bg-white/25 text-white' : 'bg-gray-100 text-gray-700' }}">
                                {{ $countHistoryNewTask }}
                            </span>
                        </button>

                        <!-- 3. Presentasi -->
                        <button
                            type="button"
                            wire:click="switchHistoryTab('presentation')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[11px] font-bold transition shadow-2xs cursor-pointer
                            {{ $historyTab === 'presentation'
                                ? 'bg-amber-600 text-white shadow-amber-500/20'
                                : 'bg-white text-gray-600 hover:bg-gray-100 hover:text-gray-900 border border-gray-200' }}">
                            <i class="fa-solid fa-chalkboard-user text-[10px]"></i>
                            <span>3. Presentasi</span>
                            <span class="px-1.5 py-0.5 rounded-full text-[9px] font-black {{ $historyTab === 'presentation' ? 'bg-white/25 text-white' : 'bg-gray-100 text-gray-700' }}">
                                {{ $countHistoryPresentation }}
                            </span>
                        </button>
                    </div>

                    <!-- Right Controls: Filter Peserta & Divisi -->
                    <div class="flex items-center gap-2 flex-wrap self-start lg:self-auto">
                        <!-- Filter Peserta dengan Autocomplete Search Dropdown -->
                        <div x-data="{ open: false }" class="relative">
                            <div class="relative flex items-center">
                                <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-gray-400">
                                    <i class="fa-solid fa-magnifying-glass text-[10px]"></i>
                                </span>
                                <input
                                    type="text"
                                    wire:model.live.debounce.300ms="historySearchName"
                                    @focus="open = true"
                                    @input="open = true"
                                    @keydown.escape="open = false"
                                    placeholder="Cari nama peserta..."
                                    class="text-[11px] py-1.5 pl-7 pr-6 rounded-lg border {{ !empty($selectedUserId) ? 'border-emerald-500 bg-emerald-50/60 text-emerald-900 font-semibold ring-1 ring-emerald-500/30' : 'border-gray-200 bg-white text-gray-800' }} focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 shadow-2xs w-44 sm:w-52 transition-all placeholder:text-gray-400"
                                    autocomplete="off"
                                />
                                @if(!empty($historySearchName) || !empty($selectedUserId))
                                <button
                                    type="button"
                                    wire:click="clearHistoryUser"
                                    @click="open = false"
                                    class="absolute inset-y-0 right-0 pr-2 flex items-center text-gray-400 hover:text-red-500 transition cursor-pointer"
                                    title="Hapus filter peserta">
                                    <i class="fa-solid fa-circle-xmark text-[11px]"></i>
                                </button>
                                @endif
                            </div>

                            <!-- Dropdown Autocomplete Bantuan -->
                            <div
                                x-show="open && ({{ strlen(trim($historySearchName)) >= 1 ? 'true' : 'false' }})"
                                @click.outside="open = false"
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                                class="absolute z-50 mt-1 w-64 sm:w-72 max-h-60 overflow-y-auto bg-white rounded-xl shadow-lg border border-gray-200 py-1 right-0 lg:left-0 lg:right-auto divide-y divide-gray-50"
                                style="display: none;">
                                <div class="px-2.5 py-1 bg-gray-50/90 text-[9px] font-bold uppercase tracking-wider text-gray-400 flex items-center justify-between">
                                    <span>Pilih Peserta</span>
                                    <span class="text-[9px] font-medium lowercase text-gray-400">{{ count($suggestedUsers ?? []) }} hasil</span>
                                </div>
                                @forelse($suggestedUsers ?? [] as $sUser)
                                    @php
                                        $sName = $sUser->profile->full_name ?? $sUser->name ?? 'Peserta';
                                        $sSchool = $sUser->intern->school->name ?? '-';
                                        $sDivision = $sUser->intern->division->name ?? '-';
                                        $sInitial = strtoupper(substr($sName, 0, 1));
                                    @endphp
                                    <button
                                        type="button"
                                        wire:click="selectHistoryUser({{ $sUser->id }}, '{{ addslashes($sName) }}')"
                                        @click="open = false"
                                        class="w-full text-left px-2.5 py-1.5 hover:bg-emerald-50/70 flex items-center gap-2 transition group cursor-pointer {{ $selectedUserId === $sUser->id ? 'bg-emerald-50 text-emerald-900 font-bold' : '' }}">
                                        <div class="w-6 h-6 rounded-md bg-slate-800 group-hover:bg-emerald-600 text-white flex items-center justify-center text-[10px] font-bold flex-shrink-0 transition">
                                            {{ $sInitial }}
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="text-[11px] font-semibold text-gray-900 group-hover:text-emerald-700 truncate">
                                                {{ $sName }}
                                            </div>
                                            <div class="text-[9px] text-gray-500 truncate flex items-center gap-1">
                                                <span class="text-gray-600 font-medium">{{ $sDivision }}</span>
                                                <span class="text-gray-300">•</span>
                                                <span class="truncate">{{ $sSchool }}</span>
                                            </div>
                                        </div>
                                        @if($selectedUserId === $sUser->id)
                                            <i class="fa-solid fa-check text-emerald-600 text-[10px]"></i>
                                        @endif
                                    </button>
                                @empty
                                    <div class="px-3 py-2.5 text-center text-[11px] text-gray-500">
                                        <i class="fa-solid fa-user-slash text-gray-300 block text-sm mb-0.5"></i>
                                        Tidak ada peserta yang cocok
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <!-- Filter Per Divisi -->
                        <div class="flex items-center gap-1">
                            <label for="history-division-filter" class="text-[11px] font-semibold text-gray-500 flex items-center gap-1 whitespace-nowrap">
                                <i class="fa-solid fa-filter text-gray-400 text-[10px]"></i>
                                <span class="hidden sm:inline">Divisi:</span>
                            </label>
                            <select
                                id="history-division-filter"
                                wire:model.live="selectedDivision"
                                class="text-[11px] py-1.5 pl-2.5 pr-7 rounded-lg border border-gray-200 bg-white text-gray-800 font-semibold focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 shadow-2xs cursor-pointer">
                                <option value="">Semua Divisi</option>
                                @foreach($divisions as $div)
                                <option value="{{ $div->id }}">{{ $div->name }}</option>
                                @endforeach
                            </select>
                            @if(!empty($selectedDivision))
                            <button
                                type="button"
                                wire:click="$set('selectedDivision', '')"
                                class="h-7 w-7 rounded-lg bg-gray-200/70 hover:bg-red-100 text-gray-500 hover:text-red-600 flex items-center justify-center transition cursor-pointer"
                                title="Reset Filter Divisi">
                                <i class="fa-solid fa-xmark text-[10px]"></i>
                            </button>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- History Table -->
                <div class="w-full overflow-x-auto rounded-xl border border-gray-200 shadow-2xs">
                    <table class="w-full text-left border-collapse table-fixed min-w-[860px]">
                        <thead>
                            <tr class="bg-gray-50/90 border-b border-gray-200 text-[11px] font-bold uppercase tracking-wider text-gray-600">
                                <th class="w-[5%] min-w-[38px] px-3 py-3 text-center">#</th>
                                <th class="w-[27%] min-w-[200px] px-3 py-3">Peserta & Asal Sekolah</th>
                                @if($historyTab === 'presentation')
                                <th class="w-[48%] min-w-[280px] px-3 py-3">Materi & Jadwal Presentasi</th>
                                <th class="w-[20%] min-w-[160px] px-3 py-3">Waktu Selesai & Penguji</th>
                                @elseif($historyTab === 'new_task')
                                <th class="w-[48%] min-w-[280px] px-3 py-3">Permintaan / Laporan Tugas</th>
                                <th class="w-[20%] min-w-[160px] px-3 py-3">Waktu Selesai & Petugas</th>
                                @else
                                <th class="w-[48%] min-w-[280px] px-3 py-3">Pertanyaan / Kendala Siswa</th>
                                <th class="w-[20%] min-w-[160px] px-3 py-3">Waktu Selesai & Petugas</th>
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

                <!-- History Table Pagination (Setting Style) -->
                @if($activeTab === 'history' && $items instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator && $items->hasPages())
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 p-3.5 bg-white rounded-xl border border-gray-200 shadow-2xs mt-4">
                    <div class="text-xs text-gray-500 font-medium">
                        Menampilkan <span class="font-bold text-gray-800">{{ $items->firstItem() ?? 0 }}</span> - <span class="font-bold text-gray-800">{{ $items->lastItem() ?? 0 }}</span> dari <span class="font-bold text-gray-800">{{ $items->total() }}</span> riwayat selesai
                    </div>
                    <div class="flex items-center space-x-1.5">
                        {{-- Prev Button --}}
                        <button
                            type="button"
                            wire:click="previousPage"
                            @disabled($items->onFirstPage())
                            class="cursor-pointer bg-gray-800 text-white hover:bg-gray-900 px-3 py-1.5 rounded-lg text-xs font-semibold disabled:bg-gray-200 disabled:text-gray-400 disabled:cursor-not-allowed shadow-2xs transition flex items-center gap-1">
                            <i class="fas fa-chevron-left text-[10px]"></i>
                            <span>Prev</span>
                        </button>

                        {{-- Page Numbers --}}
                        <div class="flex space-x-1">
                            @foreach (range(1, $items->lastPage()) as $page)
                                @if ($page == $items->currentPage())
                                    <button
                                        type="button"
                                        class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-bold shadow-2xs transition cursor-default">
                                        {{ $page }}
                                    </button>
                                @else
                                    <button
                                        type="button"
                                        wire:click="gotoPage({{ $page }})"
                                        class="px-3 py-1.5 bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-lg text-xs font-bold shadow-2xs transition cursor-pointer">
                                        {{ $page }}
                                    </button>
                                @endif
                            @endforeach
                        </div>

                        {{-- Next Button --}}
                        <button
                            type="button"
                            wire:click="nextPage"
                            @disabled(!$items->hasMorePages())
                            class="cursor-pointer bg-gray-800 text-white hover:bg-gray-900 px-3 py-1.5 rounded-lg text-xs font-semibold disabled:bg-gray-200 disabled:text-gray-400 disabled:cursor-not-allowed shadow-2xs transition flex items-center gap-1">
                            <span>Next</span>
                            <i class="fas fa-chevron-right text-[10px]"></i>
                        </button>
                    </div>
                </div>
                @endif

            </div>

            @endif

        </div>
    </div>

</div>