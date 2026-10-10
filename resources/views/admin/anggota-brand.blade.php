@extends('layouts.main')

@section('title', 'Daftar Pemagang - ' . ($brand->name ?? 'Brand'))

@section('contents')
    <!-- Main Content -->
    <main class="ml-0 md:ml-64 mt-16 md:mt-20 p-3 sm:p-6 min-w-0">
        <!-- Breadcrumb & Back Button -->
        <div class="mb-4">
            <a href="{{ route('admin.brand.view') }}"
                class="inline-flex items-center gap-2 text-xs sm:text-sm font-semibold text-gray-500 hover:text-blue-600 transition">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Kembali ke Daftar Brand</span>
            </a>
        </div>

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold overflow-hidden border border-blue-100 shrink-0">
                        @if (!empty($brand->logo) && file_exists(public_path('img/brands/' . $brand->logo)))
                            <img src="{{ asset('img/brands/' . $brand->logo) }}" alt="{{ $brand->name }}" class="w-full h-full object-contain p-1">
                        @else
                            <i class="fa-solid fa-tags text-xl"></i>
                        @endif
                    </div>
                    <div>
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-900">{{ $brand->name ?? 'Daftar Pemagang Brand' }}</h1>
                        <p class="text-xs sm:text-sm text-gray-500">
                            {{ $brand->description ?: 'Daftar pemagang yang terafiliasi dengan unit bisnis / brand ini.' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Quick Counter Badges -->
            <div class="flex items-center gap-2 flex-wrap">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-blue-50 text-blue-700 border border-blue-100">
                    <i class="fa-solid fa-users text-blue-500"></i>
                    <span>{{ $teamData ? $teamData->count() : 0 }} Pemagang Terdaftar</span>
                </span>
                @if(isset($brand->is_active))
                    <span class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl text-xs font-semibold {{ $brand->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-gray-100 text-gray-600 border border-gray-200' }}">
                        <i class="fa-solid fa-circle text-[8px] {{ $brand->is_active ? 'text-emerald-500' : 'text-gray-400' }}"></i>
                        <span>{{ $brand->is_active ? 'Brand Aktif' : 'Nonaktif' }}</span>
                    </span>
                @endif
            </div>
        </div>

        <!-- Search Bar -->
        <div class="mb-6">
            <div class="relative max-w-md">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                    <i class="fa-solid fa-search text-sm"></i>
                </span>
                <input type="text" id="searchInput"
                    class="w-full pl-10 pr-4 py-2.5 text-sm bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-xs transition"
                    placeholder="Cari nama pemagang, sekolah, divisi, kantor...">
            </div>
        </div>

        <!-- Informational Table Card -->
        <div class="bg-white rounded-2xl shadow-xs border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50/75 text-gray-700 text-xs uppercase font-semibold">
                        <tr>
                            <th class="py-3.5 px-4 text-left w-12">No</th>
                            <th class="py-3.5 px-4 text-left">Pemagang</th>
                            <th class="py-3.5 px-4 text-left">Asal Sekolah / Kampus</th>
                            <th class="py-3.5 px-4 text-left">Divisi</th>
                            <th class="py-3.5 px-4 text-left">Penempatan Kantor</th>
                            <th class="py-3.5 px-4 text-left">Shift Kerja</th>
                            <th class="py-3.5 px-4 text-left">Periode Magang</th>
                            <th class="py-3.5 px-4 text-center w-36">Aksi</th>
                        </tr>
                    </thead>

                    @if (!$teamData || $teamData->isEmpty())
                        <tbody>
                            <tr>
                                <td colspan="8" class="py-12 px-4 text-center">
                                    <div class="max-w-xs mx-auto text-center">
                                        <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3 text-gray-400">
                                            <i class="fa-solid fa-users-slash text-xl"></i>
                                        </div>
                                        <h3 class="text-sm font-bold text-gray-900">Belum ada pemagang terdaftar</h3>
                                        <p class="text-xs text-gray-500 mt-1">Belum ada pemagang yang ditugaskan pada brand ini.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    @else
                        <tbody id="teamTableBody" class="divide-y divide-gray-100 text-sm">
                            @foreach ($teamData as $key => $data)
                                @php
                                    $profile = $data->user?->profile;
                                    $fullName = $profile->full_name ?? ($data->user?->username ?? 'Tanpa Nama');
                                    $sched = $data->schedules->first();
                                    $schedOffice = $sched?->office?->name ?? ($data->lockedOffice?->name ?? '');
                                    $schedStart = $sched?->start_period ?? $data->start_date;
                                    $schedEnd = $sched?->end_period ?? $data->end_date;
                                    $isWeekly = $sched && $sched->type === 'weekly';
                                    $currentShift = $data->resolved_shift ?? $data->shift;

                                    // Format nomor WhatsApp
                                    $rawPhone = $profile->phone ?? ($data->whatsappNumber->number ?? '');
                                    $cleanPhone = preg_replace('/[^0-9]/', '', (string)$rawPhone);
                                    if (str_starts_with($cleanPhone, '0')) {
                                        $waPhone = '62' . substr($cleanPhone, 1);
                                    } elseif (str_starts_with($cleanPhone, '62')) {
                                        $waPhone = $cleanPhone;
                                    } else {
                                        $waPhone = $cleanPhone ? '62' . $cleanPhone : '';
                                    }
                                @endphp
                                <tr class="hover:bg-blue-50/20 transition">
                                    <td class="py-3.5 px-4 text-gray-500 font-medium">{{ $key + 1 }}</td>
                                    <td class="py-3.5 px-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-blue-600 to-indigo-600 text-white font-bold text-xs flex items-center justify-center shrink-0 shadow-xs">
                                                {{ strtoupper(substr($fullName, 0, 2)) }}
                                            </div>
                                            <div class="min-w-0">
                                                <div class="font-bold text-gray-900 leading-tight">
                                                    {{ $fullName }}
                                                </div>
                                                @if ($data->nim)
                                                    <div class="text-[11px] text-gray-500 font-mono mt-0.5">
                                                        <span class="text-gray-400">NIM:</span>
                                                        <span class="font-medium text-gray-700">{{ $data->nim }}</span>
                                                    </div>
                                                @endif
                                                @if ($rawPhone)
                                                    <div class="text-[11px] text-gray-500 flex items-center gap-1.5 mt-0.5">
                                                        <i class="fa-solid fa-phone text-[10px] text-gray-400"></i>
                                                        <span>{{ $rawPhone }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        @if ($data->school)
                                            <div class="text-xs font-semibold text-gray-800 flex items-center gap-1.5">
                                                <i class="fa-solid fa-graduation-cap text-gray-400 text-xs"></i>
                                                <span class="line-clamp-1">{{ $data->school->name }}</span>
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-400 italic">Belum Ada Sekolah</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        @if ($data->division)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-gray-100 text-gray-800">
                                                <i class="fa-solid fa-sitemap text-[10px] text-gray-500"></i>
                                                {{ $data->division->name }}
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-400 italic">Belum Ada Divisi</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        @php
                                            $officeNames = $data->schedules->pluck('office.name')->filter()->unique()->implode(', ') ?: ($data->lockedOffice->name ?? '');
                                        @endphp
                                        @if ($officeNames)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-100">
                                                <i class="fa-solid fa-building text-[10px] text-emerald-600"></i>
                                                {{ $officeNames }}
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-400 italic">Belum Ditentukan</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        @if ($currentShift)
                                            <div class="text-xs font-semibold text-gray-800 flex items-center gap-1.5">
                                                <span>{{ $currentShift->name }}</span>
                                                @if ($isWeekly)
                                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-700">Rolling</span>
                                                @else
                                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-gray-100 text-gray-600">Tetap</span>
                                                @endif
                                            </div>
                                            <div class="text-[11px] text-gray-500 font-mono mt-0.5">
                                                {{ \Carbon\Carbon::parse($currentShift->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($currentShift->end_time)->format('H:i') }}
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-400 italic">Belum Diatur</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 whitespace-nowrap text-xs text-gray-600">
                                        @if ($schedStart && $schedEnd)
                                            <div class="flex items-center gap-1 text-gray-700 font-medium">
                                                <i class="fa-regular fa-calendar text-gray-400 text-xs"></i>
                                                <span>{{ \Carbon\Carbon::parse($schedStart)->format('d M Y') }}</span>
                                                <span class="text-gray-400">-</span>
                                                <span>{{ \Carbon\Carbon::parse($schedEnd)->format('d M Y') }}</span>
                                            </div>
                                        @else
                                            <span class="text-gray-400 italic">Belum Diatur</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 whitespace-nowrap text-center">
                                        <div class="relative inline-block text-left dropdown-container">
                                            <!-- Toggle Dropdown Button -->
                                            <button
                                                type="button"
                                                onclick="toggleActionDropdown(event, 'action-dropdown-{{ $data->id }}')"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 hover:text-gray-900 text-xs font-semibold rounded-xl transition border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 shadow-2xs">
                                                <span>Aksi</span>
                                                <i class="fa-solid fa-chevron-down text-[10px] text-gray-500 transition-transform duration-200 dropdown-arrow"></i>
                                            </button>

                                            <!-- Dropdown Menu -->
                                            <div id="action-dropdown-{{ $data->id }}"
                                                class="action-dropdown-menu hidden absolute right-0 mt-1.5 w-44 bg-white rounded-xl shadow-lg border border-gray-100 py-1.5 z-40 text-left">
                                                @if ($waPhone)
                                                    <a href="https://wa.me/{{ $waPhone }}" target="_blank"
                                                        onclick="closeAllDropdowns()"
                                                        class="w-full text-left px-3.5 py-2 text-xs font-medium text-emerald-700 hover:bg-emerald-50 flex items-center gap-2.5 transition">
                                                        <i class="fa-brands fa-whatsapp text-emerald-600 text-sm w-4 text-center"></i>
                                                        <span>Chat WhatsApp</span>
                                                    </a>
                                                @endif
                                                <a href="{{ route('admin.presence.detail', ['intern_id' => $data->id]) }}"
                                                    onclick="closeAllDropdowns()"
                                                    class="w-full text-left px-3.5 py-2 text-xs font-medium text-gray-700 hover:bg-blue-50 hover:text-blue-700 flex items-center gap-2.5 transition {{ $waPhone ? 'border-t border-gray-100' : '' }}">
                                                    <i class="fa-regular fa-id-card text-blue-600 w-4 text-center"></i>
                                                    <span>Detail Presensi</span>
                                                </a>
                                                <a href="{{ route('admin.division.edit.view', ['userId' => $data->user_id]) }}"
                                                    onclick="closeAllDropdowns()"
                                                    class="w-full text-left px-3.5 py-2 text-xs font-medium text-blue-600 hover:bg-blue-50 flex items-center gap-2.5 transition border-t border-gray-100">
                                                    <i class="fa-solid fa-pen-to-square text-blue-600 w-4 text-center"></i>
                                                    <span>Edit Data</span>
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    @endif
                </table>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const searchInput = document.getElementById('searchInput');
                if (searchInput) {
                    searchInput.addEventListener('input', function() {
                        const filter = this.value.toLowerCase().trim();
                        const tableRows = document.querySelectorAll('#teamTableBody tr');
                        if (tableRows.length > 0) {
                            tableRows.forEach(function(row) {
                                const rowText = row.textContent.toLowerCase();
                                row.style.display = rowText.includes(filter) ? '' : 'none';
                            });
                        }
                    });
                }
            });

            // Toggle Action Dropdown
            function toggleActionDropdown(event, dropdownId) {
                event.stopPropagation();
                const dropdown = document.getElementById(dropdownId);
                const arrow = event.currentTarget.querySelector('.dropdown-arrow');
                const isCurrentlyOpen = !dropdown.classList.contains('hidden');

                closeAllDropdowns();

                if (!isCurrentlyOpen) {
                    dropdown.classList.remove('hidden');
                    if (arrow) arrow.classList.add('rotate-180');
                }
            }

            // Close all open action dropdowns
            function closeAllDropdowns() {
                document.querySelectorAll('.action-dropdown-menu').forEach(menu => {
                    menu.classList.add('hidden');
                });
                document.querySelectorAll('.dropdown-arrow').forEach(arrow => {
                    arrow.classList.remove('rotate-180');
                });
            }

            // Close dropdowns when clicking outside
            document.addEventListener('click', (event) => {
                if (!event.target.closest('.dropdown-container')) {
                    closeAllDropdowns();
                }
            });
        </script>
    </main>
@endsection
