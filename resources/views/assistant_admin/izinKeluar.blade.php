@extends('layouts.main')

@section('title', 'Monitoring Izin Keluar')

@section('contents')
    <main class="ml-0 md:ml-64 mt-16 md:mt-20 p-4 sm:p-6 lg:p-8 bg-gray-50 min-h-screen min-w-0">
        <div class="max-w-7xl mx-auto space-y-6">
        <div class="mb-6 sm:mb-8">
            <div class="flex items-center gap-3 sm:gap-4">
                <div class="p-3 sm:p-4 bg-amber-500 rounded-2xl shadow-sm text-white shrink-0"><i class="fas fa-sign-out-alt text-xl sm:text-2xl"></i></div>
                <div>
                    <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900">Monitoring Izin Keluar</h1>
                    <p class="text-xs sm:text-sm text-gray-500 mt-0.5">Pantau status izin keluar dan durasi pemagang secara langsung</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-4 sm:p-6">
                <h2 class="text-lg sm:text-xl font-semibold text-gray-900 mb-4">Daftar Intern</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sekolah</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durasi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">History</th>
                            </tr>
                        </thead>
                        <tbody id="internsTableBody" class="bg-white divide-y divide-gray-200">
                            {{-- [DIUBAH] Gunakan variabel $interns --}}
                            @forelse($interns as $intern)
                                @php
                                    $permit = $intern->activePermitLog;
                                    $isPermitted = $permit && $permit->type === 'leave';
                                    $startEpoch = ($isPermitted && $permit->start_time) ? \Carbon\Carbon::parse($permit->start_time)->timestamp : 0;
                                    $initialElapsed = $startEpoch > 0 ? max(0, time() - $startEpoch) : 0;
                                    $initialTimerStr = sprintf('%02d:%02d:%02d', floor($initialElapsed / 3600), floor(($initialElapsed % 3600) / 60), $initialElapsed % 60);
                                @endphp
                                <tr>
                                    {{-- [DIUBAH] Penomoran yang benar untuk paginasi --}}
                                    <td class="px-6 py-4">{{ ($interns->currentPage() - 1) * $interns->perPage() + $loop->iteration }}</td>
                                    <td class="px-6 py-4">{{ $intern->user->profile->full_name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4">{{ $intern->school->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4">
                                        @if($isPermitted)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800"><i class="fas fa-sign-out-alt mr-1"></i> Sedang Izin</span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800"><i class="fas fa-check mr-1"></i> Normal</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 font-mono">
                                        @if($isPermitted)
                                            <div class="timer-value font-mono"
                                                 data-permit-id="{{ $permit->id }}"
                                                 data-base-url="{{ route('assistant.permit.leave.duration', ['permitLog' => ':id']) }}"
                                                 data-start-time="{{ $startEpoch }}">{{ $initialTimerStr }}</div>
                                        @else
                                            <div>-</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <a href="{{ route('assistant.izin.leave.history', $intern->id) }}" class="text-blue-500 hover:text-blue-700">
                                            <i class="fas fa-history"></i> History
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-6 py-4 text-center">Tidak ada data.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($interns->hasPages())
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 p-3.5 bg-white rounded-2xl border border-slate-200/90 shadow-2xs mt-6">
                    <div class="text-xs text-slate-500 font-medium">
                        Menampilkan <span class="font-bold text-slate-800">{{ $interns->firstItem() ?? 0 }}</span> - <span class="font-bold text-slate-800">{{ $interns->lastItem() ?? 0 }}</span> dari <span class="font-bold text-slate-800">{{ $interns->total() }}</span> pemagang
                    </div>
                    <div class="flex items-center space-x-1.5">
                        {{-- Prev Button --}}
                        @if ($interns->onFirstPage())
                            <button class="bg-gray-200 text-gray-400 px-3 py-1.5 rounded-lg text-xs font-semibold cursor-not-allowed shadow-2xs flex items-center gap-1" disabled>
                                <i class="fas fa-chevron-left text-[10px]"></i>
                                <span>Prev</span>
                            </button>
                        @else
                            <a href="{{ $interns->previousPageUrl() }}" class="bg-gray-800 text-white hover:bg-gray-900 px-3 py-1.5 rounded-lg text-xs font-semibold shadow-2xs transition flex items-center gap-1">
                                <i class="fas fa-chevron-left text-[10px]"></i>
                                <span>Prev</span>
                            </a>
                        @endif

                        {{-- Page Numbers --}}
                        <div class="flex space-x-1">
                            @foreach (range(1, $interns->lastPage()) as $page)
                                @if ($page == $interns->currentPage())
                                    <span class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-bold shadow-2xs transition">
                                        {{ $page }}
                                    </span>
                                @else
                                    <a href="{{ $interns->url($page) }}" class="px-3 py-1.5 bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-lg text-xs font-bold shadow-2xs transition">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach
                        </div>

                        {{-- Next Button --}}
                        @if ($interns->hasMorePages())
                            <a href="{{ $interns->nextPageUrl() }}" class="bg-gray-800 text-white hover:bg-gray-900 px-3 py-1.5 rounded-lg text-xs font-semibold shadow-2xs transition flex items-center gap-1">
                                <span>Next</span>
                                <i class="fas fa-chevron-right text-[10px]"></i>
                            </a>
                        @else
                            <button class="bg-gray-200 text-gray-400 px-3 py-1.5 rounded-lg text-xs font-semibold cursor-not-allowed shadow-2xs flex items-center gap-1" disabled>
                                <span>Next</span>
                                <i class="fas fa-chevron-right text-[10px]"></i>
                            </button>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
        </div>
    </main>

    @include('admin.partials.universal-permit-timer-assistand')
@endsection