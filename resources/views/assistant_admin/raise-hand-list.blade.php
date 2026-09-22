@extends('layouts.main')

@section('title', 'Daftar Raise Hand')

@section('contents')
@include($sidebarView)
@include('layouts.navbar', ['user' => $user])

<main class="ml-64 mt-24 p-6 bg-gray-50 min-h-screen">
    <!-- Header Section -->
    <div class="mb-8">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="p-3 bg-blue-600 rounded-lg shadow-sm">
                    <i class="fa-solid fa-hand-paper text-white text-xl"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Daftar Raise Hand</h1>
                    <p class="text-gray-600 mt-1">Kelola raise hand dari peserta</p>
                </div>
            </div>
            <a href="{{ route('assistant.dashboard') }}"
                class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200 flex items-center gap-2">
                <i class="fa-solid fa-arrow-left"></i>
                Kembali ke Dashboard
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    @if (session('success'))
    <div class="mb-6 animate-slide-down">
        <div class="px-6 py-4 rounded-lg flex items-start gap-4 border bg-green-50 border-green-200 text-green-800">
            <div class="mt-1">
                <div class="w-10 h-10 rounded-full flex items-center justify-center bg-green-100">
                    <i class="fa-solid fa-circle-check text-green-600 text-lg"></i>
                </div>
            </div>
            <div>
                <div class="font-semibold text-lg">Berhasil!</div>
                <div class="mt-1 text-sm leading-relaxed">{{ session('success') }}</div>
            </div>
        </div>
    </div>
    @endif

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
                        placeholder="Cari nama, sekolah, atau no. HP..."
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
        <div class="bg-slate-800 p-6 text-white">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-slate-700 rounded-lg p-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-blue-500 rounded-lg">
                            <i class="fa-solid fa-users text-white"></i>
                        </div>
                        <div>
                            <div class="text-2xl font-bold">{{ $handRaises->count() }}</div>
                            <div class="text-slate-300 text-sm">Total Permintaan</div>
                        </div>
                    </div>
                </div>
                <div class="bg-slate-700 rounded-lg p-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-yellow-500 rounded-lg">
                            <i class="fa-solid fa-clock text-white"></i>
                        </div>
                        <div>
                            <div class="text-2xl font-bold">{{ $handRaises->where('is_raised', true)->count() }}</div>
                            <div class="text-slate-300 text-sm">Menunggu Tindakan</div>
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
                        @foreach($handRaises as $handRaise)
                        <tr class="raise-hand-row group hover:bg-gray-50 transition-all duration-200"
                            data-name="{{ strtolower($handRaise->user->profile->full_name ?? $handRaise->user->name ?? '') }}"
                            data-school="{{ strtolower($handRaise->user->intern->school->name ?? '') }}"
                            data-phone="{{ $handRaise->user->profile->phone_number ?? '' }}"
                            data-status="{{ $handRaise->is_raised ? 'menunggu' : 'selesai' }}">

                            <td class="py-4 px-3 font-bold text-gray-600 text-center">{{ $loop->iteration }}</td>

                            {{-- === BAGIAN YANG DIPERBARUI SESUAI PERMINTAAN === --}}
                            <td class="py-4 px-3">
                                <div class="flex items-center gap-4">
                                    <div class="relative">
                                        <div class="w-12 h-12 rounded-full bg-slate-700 flex items-center justify-center text-white font-bold text-lg shadow-sm">
                                            {{ strtoupper(substr($handRaise->user->profile->full_name ?? $handRaise->user->name ?? '-', 0, 1)) }}
                                        </div>
                                        @if($handRaise->is_raised)
                                        <div class="absolute -top-1 -right-1 w-4 h-4 bg-yellow-500 rounded-full animate-pulse shadow-sm border-2 border-white"></div>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-900 text-lg user-name">
                                            {{ $handRaise->user->profile->full_name ?? $handRaise->user->name ?? '-' }}
                                        </div>
                                        {{-- Info detail di bawah nama --}}
                                        <div class="mt-2 space-y-1">
                                            <div class="text-sm text-gray-600 school-name">
                                                {{ $handRaise->user->intern->school->name ?? 'Sekolah tidak diketahui' }}
                                            </div>
                                            <div class="text-sm text-gray-500 flex items-center gap-2">
                                                <i class="fa-solid fa-phone w-4 text-center text-gray-400"></i>
                                                <span class="phone-number">{{ $handRaise->user->profile->phone_number ?? 'No. HP tidak ada' }}</span>
                                            </div>
                                            @if($handRaise->shift_text)
                                            <div class="text-sm text-amber-800 font-medium flex items-center gap-2">
                                                <i class="fa-solid fa-business-time w-4 text-center text-amber-600"></i>
                                                <span>Shift: <strong>{{ $handRaise->shift_text }}</strong></span>
                                            </div>
                                            @endif
                                            <div class="text-sm text-gray-500 flex items-center gap-2">
                                                <i class="fa-regular fa-clock w-4 text-center text-gray-400"></i>
                                                <span>{{ $handRaise->updated_at->diffForHumans() }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td class="py-4 px-3 status-cell">
                                @if($handRaise->is_raised)
                                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-yellow-100 text-yellow-800 font-medium border border-yellow-200">
                                    <i class="fa-solid fa-hand-paper animate-bounce"></i>
                                    <span>Menunggu</span>
                                </div>
                                @else
                                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-green-100 text-green-800 font-medium border border-green-200">
                                    <i class="fa-solid fa-check-circle"></i>
                                    <span>Selesai</span>
                                </div>
                                @endif
                            </td>

                            <td class="py-4 px-3">
                                @if($handRaise->is_raised)
                                <form method="POST" action="{{ route('assistant.raisehand.confirm', $handRaise->id) }}" class="confirm-form">
                                    @csrf
                                    <button type="button"
                                        class="confirm-btn w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm rounded-lg transition duration-200 shadow flex items-center justify-center gap-2"
                                        data-id="{{ $handRaise->id }}">
                                        <i class="fa-solid fa-check"></i>
                                        Konfirmasi
                                    </button>
                                </form>
                                @else
                                <span class="text-sm text-gray-400 italic">Terkonfirmasi</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
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
        <div class="flex flex-col items-center justify-center py-20 text-gray-500">
            <div class="w-32 h-32 bg-blue-50 rounded-full flex items-center justify-center mb-6">
                <i class="fa-solid fa-hand-paper text-5xl text-blue-500"></i>
            </div>
            <div class="text-2xl font-bold mb-3 text-gray-700">Tidak ada permintaan bantuan</div>
            <div class="text-gray-500 text-center max-w-md">
                Semua peserta sedang fokus bekerja! Halaman ini akan otomatis update ketika ada yang membutuhkan bantuan.
            </div>
            <div class="mt-6 flex items-center gap-2 text-green-600">
                <i class="fa-solid fa-check-circle"></i>
                <span class="font-semibold">Semua dalam kendali!</span>
            </div>
        </div>
        @endif
    </div>

    <style>
        /* Style tidak diubah */
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchRaiseHand');
            const clearBtn = document.getElementById('clearSearchRaiseHand');
            const tableBody = document.getElementById('raiseHandTableBody');
            const noResultMsg = document.getElementById('noResultMsg');
            const rows = tableBody ? Array.from(tableBody.querySelectorAll('.raise-hand-row')) : [];

            function debounce(func, wait) {
                let timeout;
                return function(...args) {
                    const later = () => {
                        clearTimeout(timeout);
                        func.apply(this, args);
                    };
                    clearTimeout(timeout);
                    timeout = setTimeout(later, wait);
                };
            }

            function filterTable() {
                const query = searchInput.value.trim().toLowerCase();
                let visibleCount = 0;
                rows.forEach(row => {
                    const name = row.dataset.name || '';
                    const school = row.dataset.school || '';
                    const phone = row.dataset.phone || ''; // <-- Menambahkan pencarian no hp
                    const status = row.dataset.status || '';
                    const isMatch = !query ||
                        name.includes(query) ||
                        school.includes(query) ||
                        phone.includes(query) || // <-- Menambahkan pencarian no hp
                        status.includes(query);
                    if (isMatch) {
                        row.style.display = '';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });
                if (visibleCount === 0 && query) {
                    noResultMsg.classList.remove('hidden');
                    if (tableBody) tableBody.style.display = 'none';
                } else {
                    noResultMsg.classList.add('hidden');
                    if (tableBody) tableBody.style.display = '';
                }
                clearBtn.style.display = query ? 'flex' : 'none';
            }

            if (searchInput) {
                searchInput.addEventListener('input', debounce(filterTable, 200));
                searchInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        this.value = '';
                        filterTable();
                    }
                });
            }
            if (clearBtn) {
                clearBtn.addEventListener('click', function() {
                    searchInput.value = '';
                    filterTable();
                    searchInput.focus();
                });
            }
            document.querySelectorAll('.confirm-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const form = this.closest('form');
                    Swal.fire({
                        title: 'Yakin konfirmasi?',
                        text: 'Setelah dikonfirmasi, raise hand ini akan dianggap selesai.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#16a34a',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Ya, Konfirmasi',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            button.disabled = true;
                            button.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses...';
                            form.submit();
                        }
                    });
                });
            });
            filterTable();
            setInterval(function() {
                if (document.visibilityState === 'visible' && document.activeElement !== searchInput) {
                    window.location.reload();
                }
            }, 30000);
        });
    </script>
</main>
@endsection