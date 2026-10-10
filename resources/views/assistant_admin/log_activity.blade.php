@extends('layouts.main')

@section('title', 'Log Aktivitas Pemagang')

@section('contents')
    <main class="ml-0 md:ml-64 mt-16 md:mt-20 p-4 sm:p-6 lg:p-8 bg-gray-50 min-h-screen min-w-0">
        <div class="max-w-7xl mx-auto space-y-6">
        <!-- Header Section -->
        <div class="mb-6 sm:mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 mb-4">
                <div class="flex items-center gap-3 sm:gap-4">
                    <div class="p-3 sm:p-4 bg-indigo-600 rounded-2xl shadow-sm shrink-0">
                        <i class="fa-solid fa-book-open text-white text-xl sm:text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-800">Log Aktivitas Pemagang</h1>
                        <p class="text-xs sm:text-sm text-gray-600 mt-0.5">Daftar laporan aktivitas harian yang dibuat oleh siswa magang.</p>
                    </div>
                </div>
                <a href="{{ route('assistant.dashboard') }}" class="self-start sm:self-auto bg-white hover:bg-gray-100 text-gray-800 font-semibold py-2 px-3.5 sm:px-4 border border-gray-300 rounded-xl text-xs sm:text-sm shadow-xs transition-colors flex items-center gap-2">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Kembali ke Dashboard</span>
                </a>
            </div>
        </div>

        <!-- Alert Messages -->
        @if(session('success'))
            <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-800 p-4 rounded-xl shadow-xs" role="alert">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-circle-check text-xl shrink-0"></i>
                    <p class="font-bold text-xs sm:text-sm">{{ session('success') }}</p>
                </div>
            </div>
        @endif
        @if(session('error'))
            <div class="mb-6 bg-amber-100 border-l-4 border-amber-500 text-amber-800 p-4 rounded-xl shadow-xs" role="alert">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-triangle-exclamation text-xl shrink-0"></i>
                    <p class="font-bold text-xs sm:text-sm">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        <!-- Status Tabs & Date Picker Container -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('assistant.logactivity', ['status' => 'pending', 'date' => $selectedDate]) }}"
                   class="px-3.5 py-2 rounded-xl border text-xs sm:text-sm font-semibold transition
                   {{ $status === 'pending' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50' }}">
                    <i class="fa-solid fa-clock-rotate-left mr-1.5"></i> Menunggu Persetujuan (Pending)
                </a>
                <a href="{{ route('assistant.logactivity', ['status' => 'processed', 'date' => $selectedDate]) }}"
                   class="px-3.5 py-2 rounded-xl border text-xs sm:text-sm font-semibold transition
                   {{ $status === 'processed' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50' }}">
                    <i class="fa-solid fa-circle-check mr-1.5"></i> Riwayat Logbook (Sudah ACC / Selesai)
                </a>
            </div>

            <!-- Date Picker -->
            <form method="GET" action="{{ route('assistant.logactivity') }}" class="flex items-center gap-2">
                <input type="hidden" name="status" value="{{ $status }}">
                <input type="date" name="date" value="{{ $selectedDate }}" class="border border-gray-200 bg-white rounded-xl px-3 py-1.5 text-xs sm:text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <button type="submit" class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs sm:text-sm font-semibold transition shadow-xs">Pilih</button>
            </form>
        </div>

        <!-- Selected Date Header -->
        <div class="mb-4">
            <h2 class="text-xl font-bold text-gray-700">
                Log Aktivitas Tanggal: {{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('d F Y') }}
            </h2>
        </div>

        <!-- Search and Filter Section -->
        <div class="mb-6 bg-white p-4 rounded-xl shadow-2xs border border-gray-200">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fa-solid fa-search text-gray-400"></i>
                </div>
                <input type="text" id="logSearchInput" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-xl leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Cari nama siswa atau isi aktivitas...">
            </div>
        </div>

        <!-- Log Activity Table -->
        <div class="bg-white rounded-2xl shadow-2xs border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead class="bg-gray-50/80 border-b border-gray-100">
                        <tr>
                            <th class="py-3.5 px-6 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Siswa</th>
                            <th class="py-3.5 px-6 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th class="py-3.5 px-6 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Aktivitas</th>
                            <th class="py-3.5 px-6 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="py-3.5 px-6 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="logTableBody" class="divide-y divide-gray-100">
                        @forelse ($logActivities as $log)
                            @php
                                $fullName = $log->detailSchedule?->schedule?->intern?->user?->profile?->full_name 
                                    ?? $log->detailSchedule?->schedule?->intern?->user?->username 
                                    ?? 'Data Siswa Tidak Ditemukan';
                                $initial = strtoupper(substr($fullName, 0, 2));
                                $email = $log->detailSchedule?->schedule?->intern?->user?->email ?? '-';
                                $school = $log->detailSchedule?->schedule?->intern?->school?->name ?? '-';
                                $statusName = $log->status?->name ?? 'In Review';
                            @endphp
                            <tr class="hover:bg-gray-50/80 transition-colors duration-150">
                                <td class="py-4 px-6 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-indigo-100 text-indigo-700 rounded-xl flex items-center justify-center font-bold text-xs shadow-2xs">
                                            {{ $initial }}
                                        </div>
                                        <div class="ml-3.5">
                                            <div class="text-sm font-semibold text-gray-900 student-name">
                                                {{ $fullName }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                {{ $school }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-6 whitespace-nowrap text-sm text-gray-600">
                                    {{ \Carbon\Carbon::parse($log->date)->translatedFormat('d F Y') }}
                                </td>
                                <td class="py-4 px-6">
                                    <div class="text-sm text-gray-800 activity-description max-w-md truncate">
                                        {{ Str::limit($log->activity, 80) }}
                                    </div>
                                </td>
                                <td class="py-4 px-6 text-center whitespace-nowrap">
                                    @if ($statusName === 'Accepted')
                                        <span class="px-2.5 py-1 inline-flex text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">Disetujui</span>
                                    @elseif ($statusName === 'Rejected')
                                        <span class="px-2.5 py-1 inline-flex text-xs font-semibold rounded-full bg-rose-100 text-rose-800 border border-rose-200">Ditolak</span>
                                    @else
                                        <span class="px-2.5 py-1 inline-flex text-xs font-semibold rounded-full bg-amber-100 text-amber-800 border border-amber-200">Menunggu</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-center whitespace-nowrap">
                                    <button type="button"
                                        onclick="openLogModal(this)"
                                        data-name="{{ $fullName }}"
                                        data-initial="{{ $initial }}"
                                        data-email="{{ $email }}"
                                        data-school="{{ $school }}"
                                        data-date="{{ \Carbon\Carbon::parse($log->date)->translatedFormat('l, d F Y') }}"
                                        data-status="{{ $statusName }}"
                                        data-activity="{{ $log->activity }}"
                                        data-notes="{{ $log->assistant_notes ?? '' }}"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 font-semibold rounded-xl text-xs transition shadow-2xs cursor-pointer">
                                        <i class="fa-solid fa-eye text-indigo-600 text-[11px]"></i>
                                        <span>Baca Logbook</span>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-12">
                                    <div class="text-gray-400">
                                        <i class="fa-solid fa-folder-open text-4xl mb-3 text-gray-300"></i>
                                        <p class="font-semibold text-gray-600 text-sm">Tidak ada log aktivitas untuk tanggal ini.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div id="noResults" class="text-center py-10 hidden">
                <div class="text-gray-400">
                    <i class="fa-solid fa-search text-3xl mb-2 text-gray-300"></i>
                    <p class="font-semibold text-gray-600 text-sm">Pencarian tidak ditemukan.</p>
                </div>
            </div>
        </div>
        </div>
    </main>

    <!-- ========================================================================= -->
    <!-- POPUP MODAL: LIHAT / BACA LOGBOOK PEMAGANG (HANYA BACA)                   -->
    <!-- ========================================================================= -->
    <div id="logDetailModal" class="hidden fixed inset-0 z-50 overflow-hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
        <div class="bg-white rounded-3xl shadow-2xl max-w-xl w-full overflow-hidden border border-gray-100 flex flex-col max-h-[90vh] my-auto animate-scale-up">
            <!-- Modal Header -->
            <div class="p-5 sm:p-6 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-slate-50 to-white">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center shadow-2xs">
                        <i class="fa-solid fa-book-open text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-gray-900">Detail Logbook Pemagang</h3>
                        <p class="text-xs text-gray-500">Laporan harian yang diisi oleh siswa magang</p>
                    </div>
                </div>
                <button type="button" onclick="closeLogModal()" class="w-8 h-8 rounded-full bg-gray-100 text-gray-400 hover:text-gray-700 hover:bg-gray-200 flex items-center justify-center transition cursor-pointer">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-5 sm:p-6 overflow-y-auto space-y-4 text-xs sm:text-sm">
                <!-- Identitas Siswa -->
                <div class="flex items-center gap-3.5 p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                    <div id="modalInitial" class="w-12 h-12 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold text-base shadow-xs shrink-0">
                        IN
                    </div>
                    <div class="min-w-0 flex-1">
                        <div id="modalStudentName" class="font-bold text-gray-900 text-sm sm:text-base leading-tight truncate">Nama Siswa</div>
                        <div class="text-xs text-gray-500 mt-0.5 flex items-center gap-2 flex-wrap">
                            <span id="modalSchool" class="truncate">-</span>
                            <span>•</span>
                            <span id="modalEmail" class="truncate">-</span>
                        </div>
                    </div>
                </div>

                <!-- Info Tanggal & Status -->
                <div class="grid grid-cols-2 gap-3">
                    <div class="p-3 bg-white rounded-xl border border-gray-200/80">
                        <span class="text-[10px] uppercase font-bold text-gray-400 block mb-0.5">Tanggal Pelaksanaan</span>
                        <div id="modalDate" class="font-semibold text-gray-800 text-xs sm:text-sm">-</div>
                    </div>
                    <div class="p-3 bg-white rounded-xl border border-gray-200/80">
                        <span class="text-[10px] uppercase font-bold text-gray-400 block mb-0.5">Status Logbook</span>
                        <div id="modalStatusBadge">-</div>
                    </div>
                </div>

                <!-- Isi Logbook Aktivitas -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5 flex items-center gap-1.5">
                        <i class="fa-solid fa-list-check text-indigo-600"></i>
                        <span>Isi Catatan Aktivitas:</span>
                    </label>
                    <div id="modalActivityText" class="p-4 bg-slate-50/80 rounded-2xl border border-slate-200 text-gray-800 text-xs sm:text-sm leading-relaxed whitespace-pre-wrap max-h-56 overflow-y-auto font-normal break-words">
                        -
                    </div>
                </div>

                <!-- Catatan Tambahan (jika ada) -->
                <div id="modalNotesSection" class="hidden">
                    <label class="block text-xs font-bold text-amber-800 uppercase tracking-wider mb-1.5 flex items-center gap-1.5">
                        <i class="fa-solid fa-comment-dots text-amber-600"></i>
                        <span>Catatan Pembimbing / Reviewer:</span>
                    </label>
                    <div id="modalNotesText" class="p-3.5 bg-amber-50/60 rounded-xl border border-amber-200 text-amber-900 text-xs leading-relaxed whitespace-pre-wrap">
                        -
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="p-4 sm:p-5 border-t border-gray-100 bg-gray-50 flex items-center justify-end">
                <button type="button" onclick="closeLogModal()" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs font-semibold rounded-xl transition shadow-2xs cursor-pointer">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Live Search Filter
            const searchInput = document.getElementById('logSearchInput');
            const tableBody = document.getElementById('logTableBody');
            const noResults = document.getElementById('noResults');
            const rows = tableBody ? tableBody.getElementsByTagName('tr') : [];

            if (searchInput) {
                searchInput.addEventListener('keyup', function () {
                    const filter = searchInput.value.toLowerCase();
                    let visibleRows = 0;

                    for (let i = 0; i < rows.length; i++) {
                        const studentNameCell = rows[i].querySelector('.student-name');
                        const descriptionCell = rows[i].querySelector('.activity-description');

                        if (studentNameCell || descriptionCell) {
                            const studentName = studentNameCell ? studentNameCell.textContent.toLowerCase() : '';
                            const description = descriptionCell ? descriptionCell.textContent.toLowerCase() : '';

                            if (studentName.includes(filter) || description.includes(filter)) {
                                rows[i].style.display = "";
                                visibleRows++;
                            } else {
                                rows[i].style.display = "none";
                            }
                        }
                    }

                    if (noResults) {
                        noResults.style.display = (visibleRows === 0 && filter !== '') ? 'block' : 'none';
                    }
                });
            }
        });

        function openLogModal(btn) {
            const name = btn.getAttribute('data-name') || 'Siswa';
            const initial = btn.getAttribute('data-initial') || 'S';
            const email = btn.getAttribute('data-email') || '-';
            const school = btn.getAttribute('data-school') || '-';
            const date = btn.getAttribute('data-date') || '-';
            const status = btn.getAttribute('data-status') || 'In Review';
            const activity = btn.getAttribute('data-activity') || 'Tidak ada catatan aktivitas.';
            const notes = btn.getAttribute('data-notes') || '';

            document.getElementById('modalStudentName').textContent = name;
            document.getElementById('modalInitial').textContent = initial;
            document.getElementById('modalEmail').textContent = email;
            document.getElementById('modalSchool').textContent = school;
            document.getElementById('modalDate').textContent = date;
            document.getElementById('modalActivityText').textContent = activity;

            // Status Badge
            const badgeContainer = document.getElementById('modalStatusBadge');
            if (status === 'Accepted') {
                badgeContainer.innerHTML = '<span class="px-2.5 py-0.5 inline-flex text-xs font-bold rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">Disetujui</span>';
            } else if (status === 'Rejected') {
                badgeContainer.innerHTML = '<span class="px-2.5 py-0.5 inline-flex text-xs font-bold rounded-full bg-rose-100 text-rose-800 border border-rose-200">Ditolak</span>';
            } else {
                badgeContainer.innerHTML = '<span class="px-2.5 py-0.5 inline-flex text-xs font-bold rounded-full bg-amber-100 text-amber-800 border border-amber-200">Menunggu (Pending)</span>';
            }

            // Notes Section
            const notesSection = document.getElementById('modalNotesSection');
            const notesText = document.getElementById('modalNotesText');
            if (notes && notes.trim() !== '') {
                notesText.textContent = notes;
                notesSection.classList.remove('hidden');
            } else {
                notesSection.classList.add('hidden');
            }

            const modal = document.getElementById('logDetailModal');
            modal.classList.remove('hidden');
        }

        function closeLogModal() {
            const modal = document.getElementById('logDetailModal');
            modal.classList.add('hidden');
        }

        // Close on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeLogModal();
            }
        });
    </script>
@endsection
