@extends('layouts.main')

@section('title', 'Monitoring Izin Keluar')

@section('contents')
    @include($sidebarView ?? 'layouts.sidebar-assistant')
    @include('layouts.navbar', ['user' => $user ?? null])

    <main class="ml-64 mt-24 p-6 md:ml-48 lg:ml-64 bg-gray-50 min-h-screen">
        <div class="mb-8">
            <div class="flex items-center gap-3">
                <div class="p-3 bg-yellow-500 rounded-lg shadow-sm"><i class="fas fa-sign-out-alt text-white text-xl"></i></div>
                <div><h1 class="text-3xl font-bold text-gray-900">Monitoring Izin Keluar</h1></div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Daftar Intern</h2>
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
                        <tbody class="bg-white divide-y divide-gray-200">
                            {{-- [DIUBAH] Gunakan variabel $interns --}}
                            @forelse($interns as $intern)
                                @php
                                    $permit = $intern->activePermitLog;
                                    $isPermitted = $permit && $permit->type === 'leave';
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
                                            <div class="timer-value"
                                                 data-permit-id="{{ $permit->id }}"
                                                 data-base-url="{{ route('assistant.permit.leave.duration', ['permitLog' => ':id']) }}">00:00:00</div>
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

                {{-- [DITAMBAHKAN] Link paginasi kustom --}}
                <div class="mt-6 flex items-center justify-center">
                    {{ $interns->links('vendor.pagination.custom-pagination') }}
                </div>
            </div>
        </div>
    </main>

    @include('admin.partials.universal-permit-timer-assistand')
@endsection