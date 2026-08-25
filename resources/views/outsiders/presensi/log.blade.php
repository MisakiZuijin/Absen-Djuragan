@extends('layouts.outsider')

@section('title', 'Log Aktivitas ' . $intern->user->profile->full_name)

@section('contents')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Back Navigation -->
        <div class="mb-8">
            <a href="{{ route('outsider.dashboard') }}"
               class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-blue-600 transition-all duration-200 shadow-sm">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali ke Dashboard
            </a>
        </div>

        <!-- Header Card -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-8 mb-8">
            <div class="flex items-start justify-between">
                <div>
                    <div class="flex items-center mb-3">
                        <div class="p-2 bg-blue-100 rounded-lg mr-4">
                            <i class="fas fa-clipboard-list text-blue-600 text-xl"></i>
                        </div>
                        <h1 class="text-3xl font-bold text-gray-900">Log Aktivitas</h1>
                    </div>
                    <p class="text-gray-600 text-lg">
                        Menampilkan log aktivitas yang telah disetujui untuk:
                        <span class="font-semibold text-gray-800">{{ $intern->user->profile->full_name }}</span>
                    </p>
                </div>
                <div class="hidden sm:block">
                    <div class="text-right">
                        <div class="text-2xl font-bold text-blue-600">{{ $logs->count() }}</div>
                        <div class="text-sm text-gray-500">Total Aktivitas</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Table Card -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <!-- Table Header -->
            <div class="px-8 py-6 border-b border-gray-200 bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-800">Daftar Aktivitas</h2>
                <p class="text-sm text-gray-600 mt-1">Semua aktivitas yang telah disetujui</p>
            </div>

            <!-- Table Content -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-8 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-calendar-alt mr-2 text-gray-400"></i>
                                    Tanggal
                                </div>
                            </th>
                            <th class="px-8 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-tasks mr-2 text-gray-400"></i>
                                    Aktivitas
                                </div>
                            </th>
                            <th class="px-8 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center justify-center">
                                    <i class="fas fa-check-circle mr-2 text-gray-400"></i>
                                    Status
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($logs as $log)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-8 py-6">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                                        <i class="fas fa-calendar text-blue-600"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm font-semibold text-gray-900">
                                            {{ \Carbon\Carbon::parse($log->date)->translatedFormat('l') }}
                                        </div>
                                        <div class="text-sm text-gray-600">
                                            {{ \Carbon\Carbon::parse($log->date)->translatedFormat('d F Y') }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="max-w-md">
                                    <p class="text-sm text-gray-900 leading-relaxed whitespace-pre-wrap">{{ $log->activity }}</p>
                                </div>
                            </td>
                            <td class="px-8 py-6 text-center">
                                <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                                    <i class="fas fa-check mr-1"></i>
                                    Disetujui
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-8 py-16 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                        <i class="fa-solid fa-clipboard-check text-2xl text-gray-400"></i>
                                    </div>
                                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Belum ada aktivitas</h3>
                                    <p class="text-gray-600 max-w-sm">Belum ada log aktivitas yang disetujui untuk ditampilkan.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($logs->hasPages())
            <div class="px-8 py-6 bg-gray-50 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-600">
                        Menampilkan {{ $logs->firstItem() }} - {{ $logs->lastItem() }} dari {{ $logs->total() }} hasil
                    </div>
                    <div class="pagination-wrapper">
                        {{ $logs->links() }}
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<style>
/* Custom pagination styling */
.pagination-wrapper .pagination {
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.pagination-wrapper .page-link {
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
    color: #6b7280;
    background-color: white;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    text-decoration: none;
    transition: all 0.15s ease-in-out;
}

.pagination-wrapper .page-link:hover {
    background-color: #f9fafb;
    border-color: #9ca3af;
    color: #374151;
}

.pagination-wrapper .page-item.active .page-link {
    background-color: #3b82f6;
    border-color: #3b82f6;
    color: white;
}

.pagination-wrapper .page-item.disabled .page-link {
    color: #9ca3af;
    background-color: #f9fafb;
    cursor: not-allowed;
}

/* Responsive adjustments */
@media (max-width: 640px) {
    .max-w-6xl {
        max-width: 100%;
    }

    table th,
    table td {
        padding: 1rem 0.75rem;
    }

    .hidden.sm\\:block {
        display: none !important;
    }
}
</style>
@endsection
