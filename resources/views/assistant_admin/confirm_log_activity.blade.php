@extends('layouts.main')

@section('title', 'Konfirmasi Log Aktivitas')

@section('contents')
    <main class="ml-0 md:ml-64 mt-16 md:mt-20 p-4 sm:p-6 lg:p-8 bg-gray-50 min-h-screen min-w-0">
        <div class="max-w-6xl mx-auto space-y-6">
        <!-- Header Section with Breadcrumb -->
        <div class="mb-6 sm:mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
                <div>
                    <nav class="flex mb-1.5" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 md:space-x-3">
                          <li class="inline-flex items-center">
                            <a href="{{ route('assistant.logactivity') }}" class="inline-flex items-center text-xs sm:text-sm font-medium text-gray-700 hover:text-indigo-600">
                              <svg class="w-3.5 h-3.5 mr-1.5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg>
                              Log Aktivitas
                            </a>
                          </li>
                          <li aria-current="page">
                            <div class="flex items-center">
                              <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                              <span class="ml-1 text-xs sm:text-sm font-medium text-gray-500 md:ml-2">Konfirmasi</span>
                            </div>
                          </li>
                        </ol>
                    </nav>
                    <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-800">Konfirmasi Log Aktivitas</h1>
                    <p class="text-xs sm:text-sm text-gray-600 mt-0.5">Tinjau, edit, dan berikan persetujuan untuk log aktivitas siswa.</p>
                </div>
                {{-- Tombol kembali sekarang juga menggunakan tanggal yang benar --}}
                <a href="{{ route('assistant.logactivity', ['date' => \Carbon\Carbon::parse($log->date)->format('Y-m-d')]) }}" class="self-start sm:self-auto bg-white hover:bg-gray-100 text-gray-800 font-semibold py-2 px-3.5 sm:px-4 border border-gray-300 rounded-xl text-xs sm:text-sm shadow-xs transition-colors flex items-center gap-2">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Kembali</span>
                </a>
            </div>
        </div>

        <!-- Alert Messages -->
        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-300 text-green-800 px-4 py-3 rounded-lg relative" role="alert">
                <strong class="font-bold">Sukses!</strong>
                <span class="block sm:inline ml-2">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-300 text-red-800 px-4 py-3 rounded-lg relative" role="alert">
                <strong class="font-bold">Gagal!</strong>
                <span class="block sm:inline ml-2">{{ session('error') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 bg-red-50 border border-red-300 text-red-800 px-4 py-3 rounded-lg">
                <div class="font-medium">Terdapat beberapa kesalahan validasi:</div>
                <ul class="mt-2 list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- Left Column: Student & Log Details Card -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6 h-full">
                    <h2 class="text-xl font-bold text-gray-800 mb-6 border-b pb-4">Detail Siswa & Log</h2>
                    
                    <div class="flex items-center mb-6">
                        <div class="flex-shrink-0 h-16 w-16 bg-indigo-100 rounded-full flex items-center justify-center border-2 border-indigo-200">
                            <span class="text-2xl font-bold text-indigo-600">{{ strtoupper(substr($log->detailSchedule->schedule->intern->user->profile->full_name ?? 'U', 0, 1)) }}</span>
                        </div>
                        <div class="ml-4">
                            <p class="font-bold text-lg text-gray-900">{{ $log->detailSchedule->schedule->intern->user->profile->full_name }}</p>
                            <p class="text-sm text-gray-500">{{ $log->detailSchedule->schedule->intern->user->email }}</p>
                        </div>
                    </div>

                    <dl class="space-y-4 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Sekolah</dt>
                            <dd class="font-semibold text-gray-800 text-right">{{ $log->detailSchedule->schedule->intern->school->name }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Tanggal Log</dt>
                            <dd class="font-semibold text-gray-800">{{ \Carbon\Carbon::parse($log->date)->translatedFormat('d F Y') }}</dd>
                        </div>
                        <div class="flex justify-between items-center">
                            <dt class="text-gray-500">Status Saat Ini</dt>
                            <dd>
                                @php
                                    $statusClass = '';
                                    $statusText = '';
                                    switch ($log->status->name) {
                                        case 'Accepted':
                                            $statusClass = 'bg-green-100 text-green-800';
                                            $statusText = 'Disetujui';
                                            break;
                                        case 'Rejected':
                                            $statusClass = 'bg-red-100 text-red-800';
                                            $statusText = 'Ditolak';
                                            break;
                                        default:
                                            $statusClass = 'bg-yellow-100 text-yellow-800';
                                            $statusText = 'Menunggu';
                                            break;
                                    }
                                @endphp
                                <span class="inline-flex items-center px-3 py-1 rounded-full font-semibold {{ $statusClass }}">
                                    {{ $statusText }}
                                </span>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Right Column: Activity Form & Actions -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Activity Edit Form -->
                <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-2">Aktivitas Siswa</h2>
                    <p class="text-sm text-gray-500 mb-6">Anda dapat mengedit deskripsi aktivitas jika terdapat kesalahan penulisan atau untuk memberikan detail lebih.</p>
                    <form action="{{ route('assistant.logactivity.update', $log->id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        
                        <!-- === PERBAIKAN FORMAT TANGGAL DI SINI === -->
                        <input type="hidden" name="redirect_date" value="{{ \Carbon\Carbon::parse($log->date)->format('Y-m-d') }}">
                        
                        <div>
                            <label for="activity" class="block text-sm font-medium text-gray-700 mb-2">Deskripsi Aktivitas</label>
                            <textarea
                                name="activity"
                                id="activity"
                                rows="6"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow @error('activity') border-red-500 @enderror"
                                required
                            >{{ old('activity', $log->activity) }}</textarea>
                            @error('activity')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <input type="hidden" name="action" value="update_text">

                        <div class="mt-6 flex justify-end">
                             <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded-lg transition-colors flex items-center gap-2">
                                <i class="fa-solid fa-save"></i>
                                <span>Simpan Perubahan</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Approval Actions Card -->
                <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6">
                     <h2 class="text-xl font-bold text-gray-800 mb-2">Tindakan Persetujuan</h2>
                     <p class="text-sm text-gray-500 mb-6">Setujui atau tolak log aktivitas ini. Tindakan ini akan mengubah status log secara permanen.</p>

                     <form action="{{ route('assistant.logactivity.update', $log->id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        
                        <!-- === PERBAIKAN FORMAT TANGGAL DI SINI JUGA === -->
                        <input type="hidden" name="redirect_date" value="{{ \Carbon\Carbon::parse($log->date)->format('Y-m-d') }}">

                        <input type="hidden" name="activity" value="{{ old('activity', $log->activity) }}">

                        <div>
                            <label for="assistant_notes" class="block text-sm font-medium text-gray-700 mb-2">Catatan Asisten (Opsional)</label>
                            <textarea name="assistant_notes" id="assistant_notes" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Berikan catatan jika log ditolak atau ada revisi khusus...">{{ old('assistant_notes', $log->assistant_notes) }}</textarea>
                            <p class="mt-2 text-xs text-gray-500">Catatan ini akan dapat dilihat oleh siswa. Sangat disarankan untuk diisi jika menolak log.</p>
                        </div>

                        <div class="mt-6 flex flex-col sm:flex-row gap-4">
                            <button type="submit" name="action" value="approve" class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-4 rounded-lg transition-colors flex items-center justify-center gap-2 text-lg">
                                <i class="fa-solid fa-check-circle"></i>
                                <span>Setujui Log</span>
                            </button>
                            <button type="submit" name="action" value="reject" class="w-full bg-red-500 hover:bg-red-600 text-white font-bold py-3 px-4 rounded-lg transition-colors flex items-center justify-center gap-2 text-lg">
                                <i class="fa-solid fa-times-circle"></i>
                                <span>Tolak Log</span>
                            </button>
                        </div>
                     </form>
                </div>

            </div>
        </div>
        </div>
    </main>
@endsection