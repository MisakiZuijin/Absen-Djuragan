@extends('layouts.main')

@section('title', 'Raise Hand List')

@section('contents')
    {{-- Memuat sidebar dan navbar untuk layout Admin --}}
    @include('layouts.sidebar')
    @include('layouts.navbar')

    <main class="ml-64 mt-24 p-6 md:ml-48 lg:ml-64 bg-gray-50 min-h-screen">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-blue-600 rounded-lg shadow-sm">
                        <i class="fa-solid fa-hand-paper text-white text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Permintaan Bantuan Aktif</h1>
                        <p class="text-gray-600 mt-1">Kelola permintaan bantuan dari peserta yang sedang berlangsung</p>
                    </div>
                </div>
                <!-- Auto-refresh indicator -->
                <div class="flex items-center gap-2 text-sm">
                    <div id="refreshIndicator" class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                    <span class="text-gray-600">Auto-refresh aktif</span>
                </div>
            </div>
        </div>

        <!-- Alert Messages -->
        <div id="liveAlert" class="mb-6 animate-slide-down hidden">
            <div class="px-6 py-4 rounded-lg flex items-start gap-4 border" id="alertContent">
                <div class="mt-1">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center" id="alertIcon">
                        <i class="fa-solid text-lg" id="alertIconClass"></i>
                    </div>
                </div>
                <div>
                    <div class="font-semibold text-lg" id="alertTitle"></div>
                    <div class="mt-1 text-sm leading-relaxed" id="alertMessage"></div>
                </div>
            </div>
        </div>

        <!-- Search Section -->
        <div class="mb-6">
            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                <div class="flex items-center gap-4">
                    <div class="relative flex-1 max-w-md">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                            <i class="fa-solid fa-magnifying-glass text-lg"></i>
                        </div>
                        <input type="text" id="searchRaiseHand"
                            class="pl-12 pr-12 py-3 w-full border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 text-gray-700 bg-white"
                            placeholder="Cari nama, sekolah, no. HP..."
                            autocomplete="off">
                        <button id="clearSearchRaiseHand"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500 hover:bg-red-50 w-8 h-8 rounded-full flex items-center justify-center transition-all duration-200 hidden"
                                type="button">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-gray-500">
                        <i class="fa-solid fa-info-circle"></i>
                        <span>Pencarian real-time</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Section -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            @if($handRaises->isNotEmpty())
                <!-- Stats Cards -->
                <div class="bg-slate-800 p-6 text-white" id="statsSection">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-slate-700 rounded-lg p-4">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-yellow-500 rounded-lg">
                                    <i class="fa-solid fa-hand-paper text-white"></i>
                                </div>
                                <div>
                                    <div class="text-2xl font-bold" id="pendingCount">{{ $handRaises->count() }}</div>
                                    <div class="text-slate-300 text-sm">Menunggu Bantuan</div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-700 rounded-lg p-4">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-blue-500 rounded-lg">
                                    <i class="fa-solid fa-clock text-white"></i>
                                </div>
                                <div>
                                    <div class="text-2xl font-bold" id="avgWaitTime">
                                        @if($handRaises->isNotEmpty())
                                            {{ round($handRaises->avg(function($item) { return $item->created_at->diffInMinutes(now()); })) }}m
                                        @else
                                            0m
                                        @endif
                                    </div>
                                    <div class="text-slate-300 text-sm">Rata-rata Tunggu</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table Section -->
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="w-full" id="raiseHandTable">
                            <thead>
                                <tr class="border-b-2 border-gray-200">
                                    <th class="text-left py-4 px-3 text-sm font-semibold text-gray-700 w-16">#</th>
                                    <th class="text-left py-4 px-3 text-sm font-semibold text-gray-700">Peserta</th>
                                    <th class="text-left py-4 px-3 text-sm font-semibold text-gray-700 w-48">Status</th>
                                    <th class="text-left py-4 px-3 text-sm font-semibold text-gray-700 w-32">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="raiseHandTableBody" class="divide-y divide-gray-100">
                                @include('admin.partials.raise-hand-table-body', ['handRaises' => $handRaises])
                            </tbody>
                        </table>
                    </div>

                    <!-- No Results Message -->
                    <div id="noResultMsg" class="hidden flex flex-col items-center justify-center py-16 text-gray-500">
                        <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                            <i class="fa-solid fa-search text-3xl text-gray-400"></i>
                        </div>
                        <div class="text-xl font-semibold mb-2 text-gray-600">Tidak ditemukan hasil pencarian</div>
                        <div class="text-gray-500">Coba kata kunci lain atau periksa kembali ejaannya</div>
                    </div>
                </div>
            @else
                <!-- Empty State -->
                <div class="flex flex-col items-center justify-center py-20 text-gray-500" id="emptyState">
                    <div class="w-32 h-32 bg-green-50 rounded-full flex items-center justify-center mb-6">
                        <i class="fa-solid fa-check-circle text-5xl text-green-500"></i>
                    </div>
                    <div class="text-2xl font-bold mb-3 text-gray-700">Tidak ada permintaan bantuan</div>
                    <div class="text-gray-500 text-center max-w-md">
                        Semua peserta sedang fokus bekerja! Halaman ini akan otomatis update ketika ada yang membutuhkan bantuan.
                    </div>
                    <div class="mt-6 flex items-center gap-2 text-green-600">
                        <i class="fa-solid fa-thumbs-up"></i>
                        <span class="font-semibold">Semua terkendali!</span>
                    </div>
                </div>
            @endif
        </div>
    </main>

    <!-- Konfirmasi Modal -->
    <div id="confirmModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fa-solid fa-question text-blue-600"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900">Konfirmasi Raise Hand</h3>
            </div>
            <p class="text-gray-600 mb-6">Apakah Anda yakin ingin mengonfirmasi raise hand ini?</p>
            <div class="flex justify-end gap-3">
                <button id="cancelConfirm" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                    Batal
                </button>
                <button id="submitConfirm" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    Ya, Konfirmasi
                </button>
            </div>
        </div>
    </div>

    <style>
        @keyframes highlightRow {
            0% {
                background-color: #fef3c7;
                transform: scale(1.01);
            }
            100% {
                background-color: transparent;
                transform: scale(1);
            }
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .raise-hand-row {
            animation: fadeInUp 0.3s ease-out;
        }

        .animate-slide-down {
            animation: slideDown 0.4s ease-out;
        }

        .highlight {
            background-color: #fbbf24;
            color: white;
            font-weight: 600;
            border-radius: 4px;
            padding: 2px 6px;
            box-shadow: 0 1px 3px rgba(251, 191, 36, 0.3);
        }

        .new-request {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { background-color: #fef3c7; }
            50% { background-color: #fde68a; }
            100% { background-color: #fef3c7; }
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 6px;
        }

        ::-webkit-scrollbar-thumb {
            background: #64748b;
            border-radius: 6px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #475569;
        }

        /* Loading animation for search */
        .search-loading::after {
            content: '';
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            width: 16px;
            height: 16px;
            border: 2px solid #e5e7eb;
            border-top: 2px solid #3b82f6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: translateY(-50%) rotate(0deg); }
            100% { transform: translateY(-50%) rotate(360deg); }
        }

        /* Refresh indicator */
        .refresh-paused {
            background-color: #f59e0b !important;
        }

        .refresh-error {
            background-color: #ef4444 !important;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchRaiseHand');
            const clearBtn = document.getElementById('clearSearchRaiseHand');
            const tableBody = document.getElementById('raiseHandTableBody');
            const noResultMsg = document.getElementById('noResultMsg');
            const refreshIndicator = document.getElementById('refreshIndicator');
            const pendingCount = document.getElementById('pendingCount');
            const avgWaitTime = document.getElementById('avgWaitTime');
            const statsSection = document.getElementById('statsSection');
            const emptyState = document.getElementById('emptyState');

            let rows = [];
            let autoRefreshInterval;
            let isSearching = false;
            let searchTimeout;

            // Fungsi untuk update stats
            function updateStats(handRaises) {
                if (!handRaises || handRaises.length === 0) {
                    if (pendingCount) pendingCount.textContent = '0';
                    if (avgWaitTime) avgWaitTime.textContent = '0m';
                    if (statsSection) statsSection.style.display = 'none';
                    if (emptyState) emptyState.style.display = 'flex';
                    return;
                }

                const pendingCountValue = handRaises.length;
                if (pendingCount) pendingCount.textContent = pendingCountValue;
                if (avgWaitTime) avgWaitTime.textContent = '~5m'; // Simplified for demo
                if (statsSection) statsSection.style.display = 'block';
                if (emptyState) emptyState.style.display = 'none';
            }

            // Fungsi untuk me-refresh data tabel dari server
            async function refreshTable() {
                if (isSearching) {
                    console.log('Pencarian aktif, refresh otomatis ditunda.');
                    updateRefreshIndicator('paused');
                    return;
                }

                try {
                    updateRefreshIndicator('loading');

                    const response = await fetch('{{ route("admin.raiseHand.tableData") }}', {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/html'
                        }
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    const newHtml = await response.text();
                    const scrollPosition = window.scrollY;

                    // Update table body
                    if (tableBody) {
                        tableBody.innerHTML = newHtml;
                    }

                    // Re-attach event listeners
                    attachAllEventListeners();

                    // Maintain scroll position
                    window.scrollTo(0, scrollPosition);

                    // Update stats (you might need to fetch this data separately or parse from HTML)
                    const newRows = tableBody ? tableBody.querySelectorAll('.raise-hand-row') : [];
                    const handRaisesData = Array.from(newRows).map(row => ({
                        id: row.dataset.id,
                        status: row.dataset.status
                    }));

                    updateStats(handRaisesData);
                    updateRefreshIndicator('active');

                    console.log('Tabel berhasil di-refresh');

                } catch (error) {
                    console.error('Gagal me-refresh tabel:', error);
                    updateRefreshIndicator('error');

                    // Try again after 5 seconds if there's an error
                    setTimeout(() => {
                        updateRefreshIndicator('active');
                    }, 5000);
                }
            }

            // Fungsi untuk update indikator refresh
            function updateRefreshIndicator(status) {
                if (!refreshIndicator) return;

                refreshIndicator.className = 'w-2 h-2 rounded-full';

                switch(status) {
                    case 'active':
                        refreshIndicator.classList.add('bg-green-500', 'animate-pulse');
                        break;
                    case 'paused':
                        refreshIndicator.classList.add('bg-yellow-500', 'refresh-paused');
                        break;
                    case 'loading':
                        refreshIndicator.classList.add('bg-blue-500', 'animate-spin');
                        break;
                    case 'error':
                        refreshIndicator.classList.add('bg-red-500', 'refresh-error');
                        break;
                }
            }

            // Fungsi untuk memasang semua event listener
            function attachAllEventListeners() {
                rows = tableBody ? Array.from(tableBody.querySelectorAll('.raise-hand-row')) : [];

                // Remove existing event listeners and attach new ones
                document.querySelectorAll('.confirm-btn').forEach(button => {
                    // Clone button to remove existing event listeners
                    const newButton = button.cloneNode(true);
                    button.parentNode.replaceChild(newButton, button);

                    newButton.addEventListener('click', function(e) {
                        e.preventDefault();
                        const form = this.closest('form');

                        Swal.fire({
                            title: 'Yakin selesaikan bantuan?',
                            text: 'Permintaan bantuan ini akan dihapus dari daftar dan dianggap selesai.',
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: '#16a34a',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Ya, Selesai',
                            cancelButtonText: 'Batal'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Show loading state
                                newButton.disabled = true;
                                newButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses...';

                                // Pause auto-refresh while form is submitting
                                clearInterval(autoRefreshInterval);
                                form.submit();
                            }
                        });
                    });
                });
            }

            // Fungsi untuk memfilter tabel
            function filterTable() {
                const query = searchInput ? searchInput.value.trim().toLowerCase() : '';
                let visibleCount = 0;

                isSearching = query.length > 0;

                rows.forEach(row => {
                    const name = row.dataset.name || '';
                    const school = row.dataset.school || '';
                    const phone = row.dataset.phone || '';
                    const status = row.dataset.status || '';

                    const isMatch = !query ||
                        name.includes(query) ||
                        school.includes(query) ||
                        phone.includes(query) ||
                        status.includes(query);

                    row.style.display = isMatch ? '' : 'none';
                    if (isMatch) visibleCount++;
                });

                if (noResultMsg) {
                    noResultMsg.style.display = (visibleCount === 0 && query) ? 'flex' : 'none';
                }

                if (tableBody) {
                    tableBody.style.display = (visibleCount === 0 && query) ? 'none' : '';
                }

                if (clearBtn) {
                    clearBtn.style.display = query ? 'flex' : 'none';
                }

                // Update refresh indicator based on search status
                if (isSearching) {
                    updateRefreshIndicator('paused');
                } else {
                    updateRefreshIndicator('active');
                }
            }

            // Debounce function
            function debounce(func, wait) {
                let timeout;
                return function(...args) {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func.apply(this, args), wait);
                };
            }

            // Event listeners
            if (searchInput) {
                searchInput.addEventListener('input', debounce(function() {
                    filterTable();

                    // Clear search timeout
                    clearTimeout(searchTimeout);

                    // Set timeout to resume auto-refresh if user stops typing
                    searchTimeout = setTimeout(() => {
                        if (searchInput.value.trim() === '') {
                            isSearching = false;
                            updateRefreshIndicator('active');
                        }
                    }, 2000);

                }, 200));

                searchInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        this.value = '';
                        isSearching = false;
                        filterTable();
                        updateRefreshIndicator('active');
                    }
                });

                // Resume auto-refresh when search is cleared
                searchInput.addEventListener('blur', function() {
                    setTimeout(() => {
                        if (this.value.trim() === '') {
                            isSearching = false;
                            updateRefreshIndicator('active');
                        }
                    }, 1000);
                });
            }

            if (clearBtn) {
                clearBtn.addEventListener('click', function() {
                    searchInput.value = '';
                    isSearching = false;
                    filterTable();
                    searchInput.focus();
                    updateRefreshIndicator('active');
                });
            }

            // Initialize
            attachAllEventListeners();
            filterTable();
            updateRefreshIndicator('active');

            // Start auto-refresh
            autoRefreshInterval = setInterval(refreshTable, 10000);

            console.log('Auto-refresh sistem dimulai setiap 10 detik');

            // Handle page visibility changes
            document.addEventListener('visibilitychange', function() {
                if (document.hidden) {
                    clearInterval(autoRefreshInterval);
                    console.log('Auto-refresh dihentikan (tab tidak aktif)');
                } else {
                    clearInterval(autoRefreshInterval);
                    autoRefreshInterval = setInterval(refreshTable, 10000);
                    refreshTable(); // Immediate refresh when tab becomes active
                    console.log('Auto-refresh dimulai kembali (tab aktif)');
                }
            });

            // Cleanup on page unload
            window.addEventListener('beforeunload', function() {
                if (autoRefreshInterval) {
                    clearInterval(autoRefreshInterval);
                }
            });
        });
    </script>
@endsection
