@extends('layouts.main')

@section('title', 'Manajemen Raise Hand & Presentasi')

@section('contents')
    <div class="ml-64 mt-20 p-4 md:p-6 bg-gray-50 min-h-screen">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-blue-600 rounded-xl shadow-sm text-white">
                        <i class="fa-solid fa-hand-paper text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Manajemen Raise Hand & Presentasi</h1>
                        <p class="text-gray-500 text-sm mt-0.5">Kelola pertanyaan, permintaan tugas baru, jadwal presentasi siswa, dan berikan tanggapan / evaluasi</p>
                    </div>
                </div>
                <!-- Auto-refresh indicator -->
                <div class="flex items-center gap-2 text-xs bg-white border border-gray-200 px-3 py-2 rounded-xl shadow-sm">
                    <div id="refreshIndicator" class="w-2.5 h-2.5 bg-green-500 rounded-full animate-pulse"></div>
                    <span class="text-gray-600 font-medium">Auto-refresh aktif</span>
                </div>
            </div>
        </div>

        @livewire('admin.raise-hand-manager')
    </div>

    <!-- MODAL 1: PROSES TANGGAPAN (BERTANYA / BERI TUGAS / EDIT TUGAS) -->
    <div id="processModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-[9999] hidden p-4">
        <div class="bg-white rounded-2xl p-6 w-full max-w-lg shadow-2xl animate-fadeIn flex flex-col">
            <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600" id="proc-icon-wrap">
                        <i class="fa-solid fa-reply text-lg" id="proc-icon"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900" id="proc-modal-title">Proses & Berikan Tanggapan</h3>
                        <p class="text-xs text-gray-500" id="proc-modal-subtitle">Berikan balasan atau arahan tugas kepada pemagang</p>
                    </div>
                </div>
                <button type="button" onclick="closeProcessModal()" class="text-gray-400 hover:text-gray-600 text-xl font-bold">&times;</button>
            </div>

            <form id="formProcessModal" method="POST" action="" class="space-y-4">
                @csrf
                <input type="hidden" name="action" id="proc-action" value="">
                <input type="hidden" name="tab" id="proc-tab" value="question">

                <!-- Detail Info Siswa & Permintaan -->
                <div class="p-3.5 bg-gray-50 rounded-xl border border-gray-200 text-xs space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500 font-medium">Peserta:</span>
                        <strong class="text-gray-900" id="proc-user-name">-</strong>
                    </div>
                    <div>
                        <span class="text-gray-500 font-medium block mb-1" id="proc-note-label">Keterangan / Pertanyaan Siswa:</span>
                        <div class="p-2.5 bg-white rounded-lg border border-gray-200 text-gray-800 whitespace-pre-line leading-relaxed" id="proc-user-note">
                            -
                        </div>
                    </div>
                </div>

                <!-- Input Judul Tugas / Project (Khusus Beri Tugas & Edit Tugas) -->
                <div id="proc-title-container" class="hidden">
                    <label class="block text-xs font-semibold text-gray-700 mb-1" for="proc-task-title">
                        Judul Tugas / Project Divisi <span class="text-red-500">*</span>
                    </label>
                    <div class="flex rounded-xl overflow-hidden border border-gray-300 focus-within:ring-2 focus-within:ring-purple-500 focus-within:border-purple-500 shadow-xs">
                        <span class="bg-purple-50 text-purple-800 px-3 py-2 text-xs font-bold flex items-center border-r border-gray-300 select-none whitespace-nowrap" id="proc-division-badge">
                            Project Divisi -
                        </span>
                        <input type="text" name="task_title" id="proc-task-title" 
                            class="flex-1 text-xs p-2.5 outline-none bg-white text-gray-900" 
                            placeholder="Contoh: Modul Autentikasi dan API Resource">
                    </div>
                    <p class="text-[11px] text-gray-500 mt-1">
                        Nama project tetap mempertahankan divisi dan di sebelahnya judul spesifik tugas.
                    </p>
                </div>

                <!-- Input Tanggapan / Jawaban Mentor -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1" for="proc-response" id="proc-response-label">
                        Tanggapan / Jawaban / Arahan Tugas
                    </label>
                    <textarea name="admin_response" id="proc-response" rows="4"
                        class="w-full text-xs p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 leading-relaxed"
                        placeholder="Tuliskan solusi kendala, jawaban pertanyaan, atau instruksi tugas baru di sini..."></textarea>
                    <p class="text-[11px] text-gray-400 mt-1" id="proc-response-help">Tanggapan ini akan tersimpan dan dapat dibaca oleh pemagang.</p>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" onclick="closeProcessModal()" class="px-4 py-2 border border-gray-300 rounded-xl text-xs text-gray-700 hover:bg-gray-50 transition">
                        Batal
                    </button>
                    <button type="submit" id="proc-submit-btn" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl shadow-sm transition flex items-center gap-1.5">
                        <i class="fa-solid fa-paper-plane"></i> Kirim Tanggapan & Selesaikan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 3: SAHKAN PENYELESAIAN TUGAS (TUGAS SELESAI VALID) -->
    <div id="completeTaskModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-[9999] hidden p-4">
        <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl animate-fadeIn flex flex-col">
            <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-600">
                        <i class="fa-solid fa-check-double text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Sahkan Penyelesaian Tugas</h3>
                        <p class="text-xs text-gray-500">Menyudahi tugas secara valid tanpa revisi</p>
                    </div>
                </div>
                <button type="button" onclick="closeCompleteTaskModal()" class="text-gray-400 hover:text-gray-600 text-xl font-bold">&times;</button>
            </div>

            <form id="formCompleteTask" method="POST" action="" class="space-y-4">
                @csrf
                <input type="hidden" name="action" value="complete_task">
                <input type="hidden" name="tab" value="new_task">

                <div class="p-3.5 bg-emerald-50/50 rounded-xl border border-emerald-200 text-xs space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500 font-medium">Peserta:</span>
                        <strong class="text-gray-900 text-sm" id="comp-user-name">-</strong>
                    </div>
                    <div>
                        <span class="text-gray-500 font-medium block mb-1">Instruksi Tugas:</span>
                        <div class="p-2.5 bg-white rounded-lg border border-emerald-200 text-gray-800 text-xs max-h-28 overflow-y-auto leading-relaxed" id="comp-task-instruction">
                            -
                        </div>
                    </div>
                </div>

                <div class="p-3 bg-blue-50/60 rounded-xl border border-blue-100 text-xs text-gray-700 space-y-1">
                    <p class="font-semibold text-blue-900 flex items-center gap-1">
                        <i class="fa-solid fa-circle-check text-emerald-600"></i> Konfirmasi Tugas Selesai
                    </p>
                    <p class="text-[11px] text-gray-600 leading-relaxed">
                        Tugas ini akan disahkan selesai secara valid tanpa revisi lagi, dan otomatis dipindahkan ke riwayat <strong>History Selesai</strong> (Tab 4).
                    </p>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" onclick="closeCompleteTaskModal()" class="px-4 py-2 border border-gray-300 rounded-xl text-xs text-gray-700 hover:bg-gray-50 transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-sm transition flex items-center gap-1.5">
                        <i class="fa-solid fa-check-double"></i> Ya, Tugas Selesai Valid
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 4: REVIEW PRA-PRESENTASI (PERBAIKAN ATAU SIAP) -->
    <style>
        /* Card Option Styling */
        .pres-option-btn {
            transition: all 0.15s ease-in-out;
            cursor: pointer;
            position: relative;
            background-color: #ffffff !important;
            border-color: #e2e8f0 !important;
        }
        
        /* Sudah Presentasi Active State (Vibrant Emerald) */
        .pres-option-btn.state-ready-active {
            background-color: #ecfdf5 !important; /* emerald-50 */
            border-color: #059669 !important; /* emerald-600 */
            box-shadow: 0 4px 12px -2px rgba(16, 185, 129, 0.25), 0 0 0 3px rgba(16, 185, 129, 0.25) !important;
        }
        .pres-option-btn.state-ready-active .card-icon-wrap {
            background-color: #059669 !important;
            color: #ffffff !important;
        }
        .pres-option-btn.state-ready-active .card-title {
            color: #064e3b !important;
            font-weight: 800 !important;
        }
        .pres-option-btn.state-ready-active .card-desc {
            color: #047857 !important;
            font-weight: 500 !important;
        }
        .pres-option-btn.state-ready-active .card-radio {
            border-color: #059669 !important;
            background-color: #ffffff !important;
        }
        .pres-option-btn.state-ready-active .card-radio-dot {
            background-color: #059669 !important;
            opacity: 1 !important;
            transform: scale(1) !important;
            display: block !important;
        }

        /* Perlu Perbaikan Active State (Vibrant Amber / Orange) */
        .pres-option-btn.state-revision-active {
            background-color: #fffbeb !important; /* amber-50 */
            border-color: #d97706 !important; /* amber-600 */
            box-shadow: 0 4px 12px -2px rgba(217, 119, 6, 0.25), 0 0 0 3px rgba(217, 119, 6, 0.25) !important;
        }
        .pres-option-btn.state-revision-active .card-icon-wrap {
            background-color: #d97706 !important;
            color: #ffffff !important;
        }
        .pres-option-btn.state-revision-active .card-title {
            color: #78350f !important;
            font-weight: 800 !important;
        }
        .pres-option-btn.state-revision-active .card-desc {
            color: #b45309 !important;
            font-weight: 500 !important;
        }
        .pres-option-btn.state-revision-active .card-radio {
            border-color: #d97706 !important;
            background-color: #ffffff !important;
        }
        .pres-option-btn.state-revision-active .card-radio-dot {
            background-color: #d97706 !important;
            opacity: 1 !important;
            transform: scale(1) !important;
            display: block !important;
        }

        /* Inactive State for Both */
        .pres-option-btn.state-inactive {
            background-color: #ffffff !important;
            border-color: #e2e8f0 !important;
            box-shadow: none !important;
        }
        .pres-option-btn.state-inactive:hover {
            border-color: #cbd5e1 !important;
            background-color: #f8fafc !important;
        }
        .pres-option-btn.state-inactive .card-icon-wrap {
            background-color: #f1f5f9 !important;
            color: #94a3b8 !important;
        }
        .pres-option-btn.state-inactive .card-title {
            color: #334155 !important;
            font-weight: 700 !important;
        }
        .pres-option-btn.state-inactive .card-desc {
            color: #64748b !important;
            font-weight: 400 !important;
        }
        .pres-option-btn.state-inactive .card-radio {
            border-color: #cbd5e1 !important;
            background-color: #ffffff !important;
        }
        .pres-option-btn.state-inactive .card-radio-dot {
            opacity: 0 !important;
            transform: scale(0) !important;
            display: none !important;
        }
    </style>

    <div id="prePresentationModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-[9999] hidden p-4">
        <div class="bg-white rounded-3xl p-6 w-full max-w-lg shadow-2xl animate-fadeIn flex flex-col border border-gray-100">
            <!-- Header Modal -->
            <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 bg-sky-50 rounded-2xl flex items-center justify-center text-sky-600 shadow-sm border border-sky-100">
                        <i class="fa-solid fa-clipboard-check text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-gray-900">Status Presentasi & Revisi Projek</h3>
                        <p class="text-xs text-gray-500">Tentukan apakah peserta sudah presentasi atau masih ada catatan revisi</p>
                    </div>
                </div>
                <button type="button" onclick="closePrePresentationModal()" class="w-8 h-8 rounded-full hover:bg-gray-100 flex items-center justify-center text-gray-400 hover:text-gray-600 text-xl font-bold transition">&times;</button>
            </div>

            <form id="formPrePresentation" method="POST" action="" class="space-y-4">
                @csrf
                <input type="hidden" name="action" id="pre-pres-action" value="ready_presentation">
                <input type="hidden" name="tab" value="presentation">

                <!-- Detail Siswa & Materi Projek -->
                <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200/80 text-xs space-y-2.5">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-bold uppercase tracking-wider text-[10px]">Data Peserta Presentasi</span>
                        <span id="pre-pres-status-badge" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 text-slate-700">
                            <i class="fa-regular fa-clock text-slate-500"></i> Belum Direview
                        </span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-0.5">
                        <div class="flex items-center gap-2.5 p-2 bg-white rounded-xl border border-slate-200/80 shadow-xs">
                            <div class="w-8 h-8 rounded-full bg-slate-800 text-white font-bold text-xs flex items-center justify-center flex-shrink-0 shadow-xs" id="pre-pres-avatar">
                                P
                            </div>
                            <div class="min-w-0">
                                <span class="text-[10px] text-slate-400 block font-semibold leading-none mb-0.5">Peserta:</span>
                                <div class="font-extrabold text-slate-900 text-xs truncate" id="pre-pres-user-name">-</div>
                            </div>
                        </div>
                        <div class="p-2 bg-white rounded-xl border border-slate-200/80 shadow-xs flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0 text-sm">
                                <i class="fa-solid fa-diagram-project"></i>
                            </div>
                            <div class="min-w-0">
                                <span class="text-[10px] text-slate-400 block font-semibold leading-none mb-0.5">Materi:</span>
                                <div class="font-extrabold text-slate-900 text-xs truncate" id="pre-pres-title">-</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pilihan Keputusan Review (Interactive Buttons) -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold text-slate-800">
                            Hasil Pemeriksaan Projek:
                        </label>
                        <span class="text-[10px] text-slate-400 font-medium">Klik untuk memilih status</span>
                    </div>

                    <div class="space-y-2.5">
                        <!-- Opsi 1: Sudah Presentasi -->
                        <button type="button" id="btn-review-ready" onclick="switchPreReviewMode('ready_presentation')"
                            class="pres-option-btn state-ready-active w-full text-left p-3.5 rounded-2xl border-2 cursor-pointer flex items-center justify-between gap-3 focus:outline-none">
                            <div class="flex items-center gap-3 min-w-0 pointer-events-none">
                                <div class="card-icon-wrap w-10 h-10 rounded-xl flex items-center justify-center text-sm flex-shrink-0 transition-all duration-150 shadow-xs">
                                    <i class="fa-solid fa-circle-check"></i>
                                </div>
                                <div class="min-w-0">
                                    <div class="card-title text-sm font-bold transition-colors">Sudah Presentasi</div>
                                    <div class="card-desc text-xs leading-tight mt-0.5 transition-colors">Peserta sudah mempresentasikan projek (lulus / tanpa revisi).</div>
                                </div>
                            </div>
                            <div class="card-radio w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0 transition-all pointer-events-none">
                                <div class="card-radio-dot w-2.5 h-2.5 rounded-full transition-all duration-150"></div>
                            </div>
                        </button>

                        <!-- Opsi 2: Perlu Perbaikan -->
                        <button type="button" id="btn-review-revision" onclick="switchPreReviewMode('request_revision')"
                            class="pres-option-btn state-inactive w-full text-left p-3.5 rounded-2xl border-2 cursor-pointer flex items-center justify-between gap-3 focus:outline-none">
                            <div class="flex items-center gap-3 min-w-0 pointer-events-none">
                                <div class="card-icon-wrap w-10 h-10 rounded-xl flex items-center justify-center text-sm flex-shrink-0 transition-all duration-150 shadow-xs">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                </div>
                                <div class="min-w-0">
                                    <div class="card-title text-sm font-bold transition-colors">Perlu Perbaikan</div>
                                    <div class="card-desc text-xs leading-tight mt-0.5 transition-colors">Ada revisi / perbaikan projek yang harus dibenahi.</div>
                                </div>
                            </div>
                            <div class="card-radio w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0 transition-all pointer-events-none">
                                <div class="card-radio-dot w-2.5 h-2.5 rounded-full transition-all duration-150"></div>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Info Penjelasan Pilihan Status (Tanpa input textarea untuk admin) -->
                <div id="pre-pres-revision-info" class="p-3.5 bg-amber-50 rounded-2xl border border-amber-200 text-xs text-amber-900 space-y-1.5 hidden">
                    <div class="flex items-center gap-1.5 font-bold text-amber-950">
                        <i class="fa-solid fa-triangle-exclamation text-amber-600 text-sm"></i>
                        <span>Status: Ada Revisi (Perlu Perbaikan)</span>
                    </div>
                    <p class="text-[11px] leading-relaxed text-amber-900">
                        Project akan tetap berstatus aktif (sedang berjalan) pada daftar tugas pemagang. <strong>Rincian dan catatan poin revisi akan diisi sendiri oleh pemagang</strong> langsung di halaman tugasnya.
                    </p>
                </div>

                <div id="pre-pres-ready-info" class="p-3.5 bg-emerald-50 rounded-2xl border border-emerald-200 text-xs text-emerald-900 space-y-1.5">
                    <div class="flex items-center gap-1.5 font-bold text-emerald-950">
                        <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
                        <span>Status: Selesai Valid (Tanpa Revisi)</span>
                    </div>
                    <p class="text-[11px] leading-relaxed text-emerald-900">
                        Presentasi disahkan tanpa revisi. Project akan otomatis ditandai sebagai <strong>Selesai (Valid)</strong> dan masuk ke daftar portofolio selesai.
                    </p>
                </div>

                <!-- Footer Aksi -->
                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-gray-100 mt-2">
                    <button type="button" onclick="closePrePresentationModal()" class="px-4 py-2.5 border border-gray-300 rounded-xl text-xs font-semibold text-gray-700 hover:bg-gray-50 transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" id="pre-pres-submit-btn" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm transition flex items-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-circle-check text-xs"></i>
                        <span>Konfirmasi Selesai Valid</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: EVALUASI & SELESAIKAN PRESENTASI -->
    <div id="evaluateModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-[9999] hidden p-4">
        <div class="bg-white rounded-2xl p-6 w-full max-w-lg shadow-2xl animate-fadeIn flex flex-col">
            <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-amber-50 rounded-xl flex items-center justify-center text-amber-600">
                        <i class="fa-solid fa-award text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Evaluasi & Selesaikan Presentasi</h3>
                        <p class="text-xs text-gray-500">Berikan penilaian performa dan catatan evaluasi hasil presentasi</p>
                    </div>
                </div>
                <button type="button" onclick="closeEvaluateModal()" class="text-gray-400 hover:text-gray-600 text-xl font-bold">&times;</button>
            </div>

            <form id="formEvaluate" method="POST" action="" class="space-y-4">
                @csrf
                <input type="hidden" name="tab" value="presentation">
                <!-- Detail Info Presentasi -->
                <div class="p-3.5 bg-gray-50 rounded-xl border border-gray-200 text-xs space-y-1.5">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Peserta:</span>
                        <strong class="text-gray-900" id="eval-user-name">-</strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Materi Presentasi:</span>
                        <strong class="text-gray-900 text-right max-w-xs" id="eval-presentation-title">-</strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Jadwal & Mode:</span>
                        <span class="text-gray-700 font-medium" id="eval-presentation-schedule">-</span>
                    </div>
                </div>

                <!-- Input Nilai Performa (1-100) -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1" for="eval-rating">
                        Nilai Performa (1 - 100) <span class="text-gray-400 font-normal">(Opsional)</span>
                    </label>
                    <div class="relative">
                        <input type="number" name="performance_rating" id="eval-rating" min="0" max="100" step="1"
                            class="w-full text-xs p-2.5 pl-9 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                            placeholder="Contoh: 85">
                        <div class="absolute left-3 top-1/2 -translate-y-1/2 text-amber-500">
                            <i class="fa-solid fa-star text-xs"></i>
                        </div>
                    </div>
                    <p class="text-[11px] text-gray-400 mt-1">Rentang: 80-100 (Sangat Baik), 60-79 (Cukup), <60 (Perlu Bimbingan)</p>
                </div>

                <!-- Input Catatan Mentor / Tanggapan -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1" for="eval-notes">
                        Catatan Evaluasi / Tanggapan Mentor <span class="text-gray-400 font-normal">(Opsional)</span>
                    </label>
                    <textarea name="performance_notes" id="eval-notes" rows="3"
                        class="w-full text-xs p-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                        placeholder="Contoh: Penguasaan materi sangat baik, pemahaman arsitektur MVC jelas. Rekomendasi lanjut ke modul berikutnya..."></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" onclick="closeEvaluateModal()" class="px-4 py-2 border border-gray-300 rounded-xl text-xs text-gray-700 hover:bg-gray-50 transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold rounded-xl shadow-sm transition flex items-center gap-1.5">
                        <i class="fa-solid fa-check"></i> Selesaikan & Simpan Nilai
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 3: DETAIL HISTORY EVALUASI & TANGGAPAN -->
    <div id="detailHistoryModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-[9999] hidden p-4">
        <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl animate-fadeIn">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-award text-amber-500 text-lg"></i>
                    <h3 class="text-base font-bold text-gray-900">Rincian Tanggapan & Evaluasi</h3>
                </div>
                <button type="button" onclick="closeDetailHistoryModal()" class="text-gray-400 hover:text-gray-600 text-xl font-bold">&times;</button>
            </div>
            <div class="space-y-3 text-xs">
                <div>
                    <span class="text-gray-500 block mb-0.5">Peserta:</span>
                    <strong class="text-gray-900 text-sm" id="detail-eval-name">-</strong>
                </div>
                <div>
                    <span class="text-gray-500 block mb-0.5">Keterangan / Pertanyaan Awal:</span>
                    <p class="text-gray-800 font-medium bg-gray-50 p-2.5 rounded-lg border border-gray-200" id="detail-eval-title">-</p>
                </div>
                <div id="detail-eval-rating-wrap" class="p-3 bg-amber-50/70 border border-amber-200 rounded-xl hidden">
                    <span class="text-amber-900 block font-bold mb-1">Nilai Performa:</span>
                    <span class="text-xl font-black text-amber-700" id="detail-eval-rating">-</span>
                </div>
                <div>
                    <span class="text-gray-500 block mb-1 font-semibold">Tanggapan / Catatan Mentor:</span>
                    <div class="p-3 bg-emerald-50/60 rounded-xl border border-emerald-200 text-gray-800 whitespace-pre-line leading-relaxed" id="detail-eval-notes">
                        -
                    </div>
                </div>
            </div>
            <div class="mt-5 flex justify-end">
                <button type="button" onclick="closeDetailHistoryModal()" class="px-5 py-2 bg-gray-800 hover:bg-gray-700 text-white text-xs font-semibold rounded-xl transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL 5: PERSETUJUAN JADWAL PRESENTASI (TERIMA / RESCHEDULE / TOLAK) -->
    <div id="approvePresentationModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-[9999] hidden p-4">
        <div class="bg-white rounded-3xl p-6 w-full max-w-lg shadow-2xl animate-fadeIn flex flex-col border border-gray-100">
            <!-- Header Modal -->
            <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600 shadow-sm border border-indigo-100">
                        <i class="fa-solid fa-calendar-check text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-gray-900">Persetujuan Jadwal Presentasi</h3>
                        <p class="text-xs text-gray-500">Konfirmasi, jadwalkan ulang, atau tolak pengajuan presentasi pemagang</p>
                    </div>
                </div>
                <button type="button" onclick="closeApprovePresentationModal()" class="w-8 h-8 rounded-full hover:bg-gray-100 flex items-center justify-center text-gray-400 hover:text-gray-600 text-xl font-bold transition">&times;</button>
            </div>

            <form id="formApprovePresentation" method="POST" action="" class="space-y-4">
                @csrf
                <input type="hidden" name="action" id="appr-action" value="accept_presentation">
                <input type="hidden" name="tab" value="presentation">

                <!-- Detail Peserta, Shift & Judul Project -->
                <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200/80 text-xs space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-bold uppercase tracking-wider text-[10px]">Data Pengajuan Pemagang</span>
                        <span id="appr-mode-badge" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-800">
                            <i class="fa-solid fa-building text-[9px]"></i> Tatap Muka
                        </span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-1">
                        <div class="p-2.5 bg-white rounded-xl border border-slate-200 shadow-xs">
                            <span class="text-[10px] text-slate-400 block font-semibold mb-0.5">Nama Pemagang:</span>
                            <div class="font-extrabold text-slate-900 text-xs truncate" id="appr-user-name">-</div>
                        </div>
                        <div class="p-2.5 bg-white rounded-xl border border-slate-200 shadow-xs">
                            <span class="text-[10px] text-slate-400 block font-semibold mb-0.5">Shift Kerja:</span>
                            <div class="font-extrabold text-amber-900 text-xs truncate flex items-center gap-1">
                                <i class="fa-regular fa-clock text-amber-600 text-[11px]"></i>
                                <span id="appr-user-shift">-</span>
                            </div>
                        </div>
                    </div>
                    <div class="p-2.5 bg-white rounded-xl border border-slate-200 shadow-xs">
                        <span class="text-[10px] text-slate-400 block font-semibold mb-0.5">Materi / Judul Presentasi:</span>
                        <div class="font-bold text-slate-800 text-xs break-words" id="appr-presentation-title">-</div>
                    </div>
                </div>

                <!-- Input Tanggal & Jam Presentasi -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1" for="appr-presentation-date">
                            Tanggal Presentasi <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="presentation_date" id="appr-presentation-date" required
                            class="w-full text-xs p-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1" for="appr-scheduled-time">
                            Jam Pelaksanaan (WIB) <span class="text-red-500">*</span>
                        </label>
                        <input type="time" name="scheduled_time" id="appr-scheduled-time" required
                            class="w-full text-xs p-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>

                <!-- Input Catatan / Instruksi Mentor -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1" for="appr-notes">
                        Catatan / Note Pembimbing <span class="text-slate-400 font-normal">(opsional)</span>
                    </label>
                    <textarea name="notes" id="appr-notes" rows="3"
                        class="w-full text-xs p-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 leading-relaxed"
                        placeholder="Contoh: Siapkan demo aplikasi di laptop dan ringkasan modul fitur..."></textarea>
                </div>

                <!-- 3 Tombol Aksi: Tolak, Reschedule, Terima -->
                <div class="grid grid-cols-3 gap-2 pt-3 border-t border-gray-100">
                    <button type="button" onclick="submitApproveDecision('reject_presentation')"
                        class="py-2.5 px-3 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-xmark text-xs"></i>
                        <span>Tolak</span>
                    </button>
                    <button type="button" onclick="submitApproveDecision('reschedule_presentation')"
                        class="py-2.5 px-3 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-300 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-clock-rotate-left text-xs"></i>
                        <span>Reschedule</span>
                    </button>
                    <button type="button" onclick="submitApproveDecision('accept_presentation')"
                        class="py-2.5 px-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-sm cursor-pointer">
                        <i class="fa-solid fa-check text-xs"></i>
                        <span>Terima</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let currentActiveTab = 'question';
        let autoRefreshTimer = null;
        let isSearchActive = false;

        // Template URL resmi dari route Laravel (selalu mengarah ke URL relatif yang benar)
        const confirmUrlTemplate = "{{ route('admin.raiseHand.confirm', ':id', false) }}";

        function switchAdminTab(tabName) {
            currentActiveTab = tabName;

            // Update tab button styles
            document.querySelectorAll('.admin-tab-btn').forEach(btn => {
                btn.className = 'admin-tab-btn flex items-center gap-2 px-5 py-3 rounded-t-xl font-semibold text-xs border-b-2 border-transparent text-gray-500 hover:text-gray-800 transition-all';
            });

            const activeBtn = document.getElementById(`btn-tab-${tabName}`);
            if (activeBtn) {
                let borderClass = 'border-blue-600 text-blue-600';
                if (tabName === 'new_task') borderClass = 'border-purple-600 text-purple-600';
                if (tabName === 'presentation') borderClass = 'border-amber-600 text-amber-600';
                if (tabName === 'history') borderClass = 'border-emerald-600 text-emerald-600';

                activeBtn.className = `admin-tab-btn flex items-center gap-2 px-5 py-3 rounded-t-xl font-bold text-xs border-b-2 ${borderClass} bg-white transition-all`;
            }

            // Show matching panel
            document.querySelectorAll('.admin-tab-panel').forEach(panel => {
                panel.classList.add('hidden');
            });

            const activePanel = document.getElementById(`panel-tab-${tabName}`);
            if (activePanel) {
                activePanel.classList.remove('hidden');
            }

            // Re-apply filter on current tab
            filterCurrentTable();
        }

        function openProcessModal(id, name, type, note, currentResponse) {
            const form = document.getElementById('formProcessModal');
            if (form) form.action = confirmUrlTemplate.replace(':id', id);

            const actEl = document.getElementById('proc-action');
            if (actEl) actEl.value = 'respond_question';
            const tabEl = document.getElementById('proc-tab');
            if (tabEl) tabEl.value = 'question';
            const userEl = document.getElementById('proc-user-name');
            if (userEl) userEl.textContent = name;
            const noteEl = document.getElementById('proc-user-note');
            if (noteEl) noteEl.textContent = note || '-';
            const respEl = document.getElementById('proc-response');
            if (respEl) {
                respEl.value = currentResponse || '';
                respEl.required = true;
            }

            // Sembunyikan field judul tugas
            const titleContainer = document.getElementById('proc-title-container');
            if (titleContainer) titleContainer.classList.add('hidden');
            const taskTitleEl = document.getElementById('proc-task-title');
            if (taskTitleEl) {
                taskTitleEl.required = false;
                taskTitleEl.value = '';
            }

            const titleEl = document.getElementById('proc-modal-title');
            const subTitleEl = document.getElementById('proc-modal-subtitle');
            const noteLabelEl = document.getElementById('proc-note-label');
            const responseLabelEl = document.getElementById('proc-response-label');
            const submitBtn = document.getElementById('proc-submit-btn');
            const iconWrap = document.getElementById('proc-icon-wrap');
            const icon = document.getElementById('proc-icon');

            if (currentResponse && currentResponse.trim().length > 0) {
                if (titleEl) titleEl.textContent = 'Edit Tanggapan / Jawaban Mentor';
                if (subTitleEl) subTitleEl.textContent = 'Perbarui tanggapan atau solusi untuk pertanyaan pemagang';
                if (submitBtn) {
                    submitBtn.className = 'px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl shadow-sm transition flex items-center gap-1.5';
                    submitBtn.innerHTML = '<i class="fa-solid fa-save"></i> Simpan Perubahan Tanggapan';
                }
            } else {
                if (titleEl) titleEl.textContent = 'Berikan Tanggapan / Solusi Kendala';
                if (subTitleEl) subTitleEl.textContent = 'Kirim balasan solusi ke pemagang (akan muncul sebagai popup di pemagang)';
                if (submitBtn) {
                    submitBtn.className = 'px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl shadow-sm transition flex items-center gap-1.5';
                    submitBtn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Kirim Tanggapan ke Pemagang';
                }
            }
            if (noteLabelEl) noteLabelEl.textContent = 'Pertanyaan / Kendala Siswa:';
            if (responseLabelEl) responseLabelEl.innerHTML = 'Tanggapan / Jawaban Solusi <span class="text-red-500">*</span>';
            if (iconWrap) iconWrap.className = 'w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600';
            if (icon) icon.className = 'fa-solid fa-reply text-lg';

            const modal = document.getElementById('processModal');
            if (modal) {
                modal.classList.remove('hidden');
                modal.style.display = 'flex';
            }
        }
        window.openProcessModal = openProcessModal;

        function openGiveTaskModal(id, name, note, division) {
            const form = document.getElementById('formProcessModal');
            if (form) form.action = confirmUrlTemplate.replace(':id', id);

            const actEl = document.getElementById('proc-action');
            if (actEl) actEl.value = 'give_task';
            const tabEl = document.getElementById('proc-tab');
            if (tabEl) tabEl.value = 'new_task';
            const userEl = document.getElementById('proc-user-name');
            if (userEl) userEl.textContent = name;
            const noteEl = document.getElementById('proc-user-note');
            if (noteEl) noteEl.textContent = note || 'Tugas sebelumnya telah selesai, meminta tugas baru.';
            const respEl = document.getElementById('proc-response');
            if (respEl) {
                respEl.value = '';
                respEl.required = true;
            }

            // Tampilkan field judul tugas dengan prefix divisi
            const titleContainer = document.getElementById('proc-title-container');
            if (titleContainer) titleContainer.classList.remove('hidden');
            const badgeEl = document.getElementById('proc-division-badge');
            if (badgeEl) badgeEl.textContent = 'Project ' + (division || 'Divisi') + ' -';
            const taskTitleEl = document.getElementById('proc-task-title');
            if (taskTitleEl) {
                taskTitleEl.required = true;
                taskTitleEl.value = '';
                taskTitleEl.placeholder = 'Contoh: Modul Autentikasi dan API Resource';
            }

            const titleEl = document.getElementById('proc-modal-title');
            const subTitleEl = document.getElementById('proc-modal-subtitle');
            const noteLabelEl = document.getElementById('proc-note-label');
            const responseLabelEl = document.getElementById('proc-response-label');
            const submitBtn = document.getElementById('proc-submit-btn');
            const iconWrap = document.getElementById('proc-icon-wrap');
            const icon = document.getElementById('proc-icon');

            if (titleEl) titleEl.textContent = 'Berikan Tugas Baru Kepada Pemagang';
            if (subTitleEl) subTitleEl.textContent = 'Kirim instruksi tugas baru. Tugas ini akan masuk ke daftar project aktif pemagang.';
            if (noteLabelEl) noteLabelEl.textContent = 'Laporan Siswa / Permintaan Tugas:';
            if (responseLabelEl) responseLabelEl.innerHTML = 'Instruksi / Rincian Tugas Baru <span class="text-red-500">*</span>';
            if (submitBtn) {
                submitBtn.className = 'px-5 py-2 bg-purple-600 hover:bg-purple-700 text-white text-xs font-semibold rounded-xl shadow-sm transition flex items-center gap-1.5';
                submitBtn.innerHTML = '<i class="fa-solid fa-plus-circle"></i> Beri Tugas';
            }
            if (iconWrap) iconWrap.className = 'w-10 h-10 bg-purple-50 rounded-xl flex items-center justify-center text-purple-600';
            if (icon) icon.className = 'fa-solid fa-list-check text-lg';

            const modal = document.getElementById('processModal');
            if (modal) {
                modal.classList.remove('hidden');
                modal.style.display = 'flex';
            }
        }
        window.openGiveTaskModal = openGiveTaskModal;

        function openEditTaskModal(id, name, note, currentInstruction, division, currentProjectTitle) {
            const form = document.getElementById('formProcessModal');
            if (form) form.action = confirmUrlTemplate.replace(':id', id);

            const actEl = document.getElementById('proc-action');
            if (actEl) actEl.value = 'update_task';
            const tabEl = document.getElementById('proc-tab');
            if (tabEl) tabEl.value = 'new_task';
            const userEl = document.getElementById('proc-user-name');
            if (userEl) userEl.textContent = name;
            const noteEl = document.getElementById('proc-user-note');
            if (noteEl) noteEl.textContent = note || 'Tugas sedang dikerjakan.';
            const respEl = document.getElementById('proc-response');
            if (respEl) {
                respEl.value = currentInstruction || '';
                respEl.required = true;
            }

            // Tampilkan field judul tugas dengan prefix divisi
            const titleContainer = document.getElementById('proc-title-container');
            if (titleContainer) titleContainer.classList.remove('hidden');
            const badgeEl = document.getElementById('proc-division-badge');
            if (badgeEl) badgeEl.textContent = 'Project ' + (division || 'Divisi') + ' -';
            const taskTitleEl = document.getElementById('proc-task-title');
            if (taskTitleEl) {
                taskTitleEl.required = true;
                let cleanTitle = currentProjectTitle || '';
                const prefix1 = 'Project ' + (division || 'Divisi') + ' -';
                const prefix2 = 'Project ' + (division || 'Divisi');
                if (cleanTitle.startsWith(prefix1)) {
                    cleanTitle = cleanTitle.substring(prefix1.length).trim();
                } else if (cleanTitle.startsWith(prefix2)) {
                    cleanTitle = cleanTitle.substring(prefix2.length).trim().replace(/^[-–—]\s*/, '');
                }
                taskTitleEl.value = cleanTitle;
            }

            const titleEl = document.getElementById('proc-modal-title');
            const subTitleEl = document.getElementById('proc-modal-subtitle');
            const noteLabelEl = document.getElementById('proc-note-label');
            const responseLabelEl = document.getElementById('proc-response-label');
            const submitBtn = document.getElementById('proc-submit-btn');
            const iconWrap = document.getElementById('proc-icon-wrap');
            const icon = document.getElementById('proc-icon');

            if (titleEl) titleEl.textContent = 'Edit & Perbarui Instruksi Tugas';
            if (subTitleEl) subTitleEl.textContent = 'Perbaiki arahan tugas jika ada kesalahan atau revisi instruksi untuk pemagang.';
            if (noteLabelEl) noteLabelEl.textContent = 'Laporan Siswa / Permintaan Tugas:';
            if (responseLabelEl) responseLabelEl.innerHTML = 'Revisi Arahan / Instruksi Tugas <span class="text-red-500">*</span>';
            if (submitBtn) {
                submitBtn.className = 'px-5 py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold rounded-xl shadow-sm transition flex items-center gap-1.5';
                submitBtn.innerHTML = '<i class="fa-solid fa-save"></i> Simpan Perubahan Tugas';
            }
            if (iconWrap) iconWrap.className = 'w-10 h-10 bg-amber-50 rounded-xl flex items-center justify-center text-amber-600';
            if (icon) icon.className = 'fa-solid fa-pen-to-square text-lg';

            const modal = document.getElementById('processModal');
            if (modal) {
                modal.classList.remove('hidden');
                modal.style.display = 'flex';
            }
        }
        window.openEditTaskModal = openEditTaskModal;

        function closeProcessModal() {
            const modal = document.getElementById('processModal');
            if (modal) {
                modal.classList.add('hidden');
                modal.style.display = 'none';
            }
            const titleContainer = document.getElementById('proc-title-container');
            if (titleContainer) titleContainer.classList.add('hidden');
            const taskTitleEl = document.getElementById('proc-task-title');
            if (taskTitleEl) {
                taskTitleEl.required = false;
                taskTitleEl.value = '';
            }
        }
        window.closeProcessModal = closeProcessModal;

        function openCompleteTaskModal(id, name, note, instruction) {
            const form = document.getElementById('formCompleteTask');
            if (form) form.action = confirmUrlTemplate.replace(':id', id);

            const userEl = document.getElementById('comp-user-name');
            if (userEl) userEl.textContent = name;
            const instEl = document.getElementById('comp-task-instruction');
            if (instEl) instEl.textContent = instruction || note || 'Tugas yang telah diberikan.';

            const modal = document.getElementById('completeTaskModal');
            if (modal) {
                modal.classList.remove('hidden');
                modal.style.display = 'flex';
            }
        }
        window.openCompleteTaskModal = openCompleteTaskModal;

        function closeCompleteTaskModal() {
            const modal = document.getElementById('completeTaskModal');
            if (modal) {
                modal.classList.add('hidden');
                modal.style.display = 'none';
            }
        }
        window.closeCompleteTaskModal = closeCompleteTaskModal;

        function openPrePresentationModal(id, name, title, status) {
            const form = document.getElementById('formPrePresentation');
            if (form) form.action = confirmUrlTemplate.replace(':id', id);

            const userEl = document.getElementById('pre-pres-user-name');
            if (userEl) userEl.textContent = name || '-';

            const titleEl = document.getElementById('pre-pres-title');
            if (titleEl) titleEl.textContent = title || 'Presentasi Modul Magang';

            const avatarEl = document.getElementById('pre-pres-avatar');
            if (avatarEl) {
                const initial = (name && name.trim().length > 0) ? name.trim().charAt(0).toUpperCase() : 'P';
                avatarEl.textContent = initial;
            }

            const badgeEl = document.getElementById('pre-pres-status-badge');
            if (badgeEl) {
                if (status === 'needs_revision') {
                    badgeEl.className = 'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-orange-100 text-orange-800 border border-orange-200';
                    badgeEl.innerHTML = '<i class="fa-solid fa-triangle-exclamation text-orange-600"></i> Status: Perlu Perbaikan';
                } else if (status === 'ready') {
                    badgeEl.className = 'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200';
                    badgeEl.innerHTML = '<i class="fa-solid fa-circle-check text-emerald-600"></i> Status: Sudah Presentasi';
                } else {
                    badgeEl.className = 'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 text-slate-700';
                    badgeEl.innerHTML = '<i class="fa-regular fa-clock text-slate-500"></i> Belum Direview';
                }
            }

            if (status === 'needs_revision') {
                switchPreReviewMode('request_revision');
            } else {
                switchPreReviewMode('ready_presentation');
            }

            const modal = document.getElementById('prePresentationModal');
            if (modal) {
                modal.classList.remove('hidden');
                modal.style.display = 'flex';
            }
        }
        window.openPrePresentationModal = openPrePresentationModal;

        function closePrePresentationModal() {
            const modal = document.getElementById('prePresentationModal');
            if (modal) {
                modal.classList.add('hidden');
                modal.style.display = 'none';
            }
        }
        window.closePrePresentationModal = closePrePresentationModal;

        function switchPreReviewMode(mode) {
            const actionInput = document.getElementById('pre-pres-action');
            const btnReady = document.getElementById('btn-review-ready');
            const btnRev = document.getElementById('btn-review-revision');
            const revInfo = document.getElementById('pre-pres-revision-info');
            const readyInfo = document.getElementById('pre-pres-ready-info');
            const submitBtn = document.getElementById('pre-pres-submit-btn');

            if (mode === 'request_revision') {
                if (actionInput) actionInput.value = 'request_revision';

                if (btnRev) {
                    btnRev.classList.remove('state-inactive');
                    btnRev.classList.add('state-revision-active');
                }

                if (btnReady) {
                    btnReady.classList.remove('state-ready-active');
                    btnReady.classList.add('state-inactive');
                }

                if (revInfo) revInfo.classList.remove('hidden');
                if (readyInfo) readyInfo.classList.add('hidden');

                if (submitBtn) {
                    submitBtn.className = 'px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl shadow-sm transition flex items-center gap-2 cursor-pointer';
                    submitBtn.innerHTML = '<i class="fa-solid fa-triangle-exclamation text-xs"></i><span>Konfirmasi Ada Revisi</span>';
                }
            } else {
                if (actionInput) actionInput.value = 'ready_presentation';

                if (btnReady) {
                    btnReady.classList.remove('state-inactive');
                    btnReady.classList.add('state-ready-active');
                }

                if (btnRev) {
                    btnRev.classList.remove('state-revision-active');
                    btnRev.classList.add('state-inactive');
                }

                if (revInfo) revInfo.classList.add('hidden');
                if (readyInfo) readyInfo.classList.remove('hidden');

                if (submitBtn) {
                    submitBtn.className = 'px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm transition flex items-center gap-2 cursor-pointer';
                    submitBtn.innerHTML = '<i class="fa-solid fa-circle-check text-xs"></i><span>Konfirmasi Selesai Valid</span>';
                }
            }
        }
        window.switchPreReviewMode = switchPreReviewMode;

        function openEvaluateModal(id, name, title, schedule, mode) {
            const form = document.getElementById('formEvaluate');
            if (form) form.action = confirmUrlTemplate.replace(':id', id);

            const userEl = document.getElementById('eval-user-name');
            if (userEl) userEl.textContent = name;
            const titleEl = document.getElementById('eval-presentation-title');
            if (titleEl) titleEl.textContent = title || 'Presentasi';
            const schedEl = document.getElementById('eval-presentation-schedule');
            if (schedEl) schedEl.textContent = (schedule ? schedule : 'Hari ini') + ' (' + mode + ')';
            const ratEl = document.getElementById('eval-rating');
            if (ratEl) ratEl.value = '';
            const nEl = document.getElementById('eval-notes');
            if (nEl) nEl.value = '';

            const modal = document.getElementById('evaluateModal');
            if (modal) {
                modal.classList.remove('hidden');
                modal.style.display = 'flex';
            }
        }
        window.openEvaluateModal = openEvaluateModal;

        function closeEvaluateModal() {
            const modal = document.getElementById('evaluateModal');
            if (modal) {
                modal.classList.add('hidden');
                modal.style.display = 'none';
            }
        }
        window.closeEvaluateModal = closeEvaluateModal;

        function viewEvaluationDetails(name, rating, notes, title) {
            const nameEl = document.getElementById('detail-eval-name');
            if (nameEl) nameEl.textContent = name;
            const titleEl = document.getElementById('detail-eval-title');
            if (titleEl) titleEl.textContent = title || '-';
            
            const ratingWrap = document.getElementById('detail-eval-rating-wrap');
            const ratingEl = document.getElementById('detail-eval-rating');
            if (rating && rating.trim() !== '') {
                if (ratingEl) ratingEl.textContent = rating + ' / 100';
                if (ratingWrap) ratingWrap.classList.remove('hidden');
            } else {
                if (ratingWrap) ratingWrap.classList.add('hidden');
            }

            const notesEl = document.getElementById('detail-eval-notes');
            if (notesEl) notesEl.textContent = notes || 'Tidak ada catatan khusus.';

            const modal = document.getElementById('detailHistoryModal');
            if (modal) {
                modal.classList.remove('hidden');
                modal.style.display = 'flex';
            }
        }
        window.viewEvaluationDetails = viewEvaluationDetails;

        function closeDetailHistoryModal() {
            const modal = document.getElementById('detailHistoryModal');
            if (modal) {
                modal.classList.add('hidden');
                modal.style.display = 'none';
            }
        }
        window.closeDetailHistoryModal = closeDetailHistoryModal;

        function openApprovePresentationModal(id, name, shift, title, mode, date, time, notes) {
            const form = document.getElementById('formApprovePresentation');
            if (form) form.action = confirmUrlTemplate.replace(':id', id);

            const nameEl = document.getElementById('appr-user-name');
            if (nameEl) nameEl.textContent = name || '-';

            const shiftEl = document.getElementById('appr-user-shift');
            if (shiftEl) shiftEl.textContent = shift || 'Belum Diatur';

            const titleEl = document.getElementById('appr-presentation-title');
            if (titleEl) titleEl.textContent = title || '-';

            const modeBadge = document.getElementById('appr-mode-badge');
            if (modeBadge) {
                if (mode === 'online') {
                    modeBadge.className = 'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800';
                    modeBadge.innerHTML = '<i class="fa-solid fa-video text-[9px]"></i> Online (GMeet)';
                } else {
                    modeBadge.className = 'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-800';
                    modeBadge.innerHTML = '<i class="fa-solid fa-building text-[9px]"></i> Tatap Muka';
                }
            }

            const dateInput = document.getElementById('appr-presentation-date');
            if (dateInput) {
                dateInput.value = date || new Date().toISOString().split('T')[0];
            }

            const timeInput = document.getElementById('appr-scheduled-time');
            if (timeInput) {
                timeInput.value = time || '10:00';
            }

            const notesInput = document.getElementById('appr-notes');
            if (notesInput) {
                notesInput.value = notes || '';
            }

            const modal = document.getElementById('approvePresentationModal');
            if (modal) {
                modal.classList.remove('hidden');
                modal.style.display = 'flex';
            }
        }
        window.openApprovePresentationModal = openApprovePresentationModal;

        function closeApprovePresentationModal() {
            const modal = document.getElementById('approvePresentationModal');
            if (modal) {
                modal.classList.add('hidden');
                modal.style.display = 'none';
            }
        }
        window.closeApprovePresentationModal = closeApprovePresentationModal;

        function submitApproveDecision(action) {
            const form = document.getElementById('formApprovePresentation');
            const actionInput = document.getElementById('appr-action');
            if (!form || !actionInput) return;

            actionInput.value = action;

            if (action === 'reject_presentation') {
                if (!confirm('Apakah Anda yakin ingin MENOLAK pengajuan presentasi ini?')) {
                    return;
                }
                const dateInp = document.getElementById('appr-presentation-date');
                const timeInp = document.getElementById('appr-scheduled-time');
                if (dateInp) dateInp.required = false;
                if (timeInp) timeInp.required = false;
            } else if (action === 'reschedule_presentation') {
                if (!confirm('Simpan dan jadwalkan ulang presentasi ini?')) {
                    return;
                }
            }

            form.submit();
        }
        window.submitApproveDecision = submitApproveDecision;

        // Delegasi Event Global untuk Tombol Aksi (Aman untuk Rendering AJAX & Karakter Khusus)
        document.addEventListener('click', function(e) {
            const approvePresBtn = e.target.closest('.btn-trigger-approve-presentation');
            if (approvePresBtn) {
                e.preventDefault();
                openApprovePresentationModal(
                    approvePresBtn.dataset.id,
                    approvePresBtn.dataset.name,
                    approvePresBtn.dataset.shift || '',
                    approvePresBtn.dataset.title || '',
                    approvePresBtn.dataset.mode || 'offline',
                    approvePresBtn.dataset.date || '',
                    approvePresBtn.dataset.time || '',
                    approvePresBtn.dataset.notes || ''
                );
                return;
            }

            const giveTaskBtn = e.target.closest('.btn-trigger-give-task');
            if (giveTaskBtn) {
                e.preventDefault();
                openGiveTaskModal(
                    giveTaskBtn.dataset.id,
                    giveTaskBtn.dataset.name,
                    giveTaskBtn.dataset.notes,
                    giveTaskBtn.dataset.division || ''
                );
                return;
            }

            const editTaskBtn = e.target.closest('.btn-trigger-edit-task');
            if (editTaskBtn) {
                e.preventDefault();
                openEditTaskModal(
                    editTaskBtn.dataset.id,
                    editTaskBtn.dataset.name,
                    editTaskBtn.dataset.notes,
                    editTaskBtn.dataset.response,
                    editTaskBtn.dataset.division || '',
                    editTaskBtn.dataset.projectTitle || ''
                );
                return;
            }

            const compTaskBtn = e.target.closest('.btn-trigger-complete-task');
            if (compTaskBtn) {
                e.preventDefault();
                openCompleteTaskModal(
                    compTaskBtn.dataset.id,
                    compTaskBtn.dataset.name,
                    compTaskBtn.dataset.notes,
                    compTaskBtn.dataset.response
                );
                return;
            }

            const preReviewBtn = e.target.closest('.btn-trigger-pre-review');
            if (preReviewBtn) {
                e.preventDefault();
                openPrePresentationModal(
                    preReviewBtn.dataset.id,
                    preReviewBtn.dataset.name,
                    preReviewBtn.dataset.title,
                    preReviewBtn.dataset.status
                );
                return;
            }

            const procBtn = e.target.closest('.btn-trigger-process');
            if (procBtn) {
                e.preventDefault();
                openProcessModal(
                    procBtn.dataset.id,
                    procBtn.dataset.name,
                    procBtn.dataset.type,
                    procBtn.dataset.notes,
                    procBtn.dataset.response || ''
                );
                return;
            }

            const evalBtn = e.target.closest('.btn-trigger-evaluate');
            if (evalBtn) {
                e.preventDefault();
                openEvaluateModal(
                    evalBtn.dataset.id,
                    evalBtn.dataset.name,
                    evalBtn.dataset.title,
                    evalBtn.dataset.schedule,
                    evalBtn.dataset.mode
                );
                return;
            }

            const detailBtn = e.target.closest('.btn-trigger-detail');
            if (detailBtn) {
                e.preventDefault();
                viewEvaluationDetails(
                    detailBtn.dataset.name,
                    detailBtn.dataset.rating,
                    detailBtn.dataset.response,
                    detailBtn.dataset.title
                );
                return;
            }

            // Delegasi tombol pilihan modal review (Siap vs Perlu Perbaikan)
            const readyChoiceBtn = e.target.closest('#btn-review-ready');
            if (readyChoiceBtn) {
                e.preventDefault();
                switchPreReviewMode('ready_presentation');
                return;
            }

            const revisionChoiceBtn = e.target.closest('#btn-review-revision');
            if (revisionChoiceBtn) {
                e.preventDefault();
                switchPreReviewMode('request_revision');
                return;
            }
        });

        // Validasi tombol Selesai Presentasi (harus lulus atau revisi)
        function handleCompletePresentation(event, status, el) {
            if (status !== 'ready' && status !== 'needs_revision') {
                if (event) {
                    event.preventDefault();
                    event.stopPropagation();
                }

                const form = el.tagName === 'FORM' ? el : el.closest('form');
                const btn = form ? form.querySelector('button[type="submit"]') : el;
                const container = form || el.parentElement;
                const row = el.closest('tr');
                const preReviewBtn = row ? row.querySelector('.btn-trigger-pre-review') : null;

                if (!container) return false;

                // Bersihkan note yang sudah ada sebelumnya
                const existingNotes = container.querySelectorAll('.pres-status-warning-note');
                existingNotes.forEach(n => n.remove());

                // Buat elemen note kecil
                const note = document.createElement('div');
                note.className = 'pres-status-warning-note absolute -top-10 right-0 z-[100] px-3 py-1 bg-rose-600 text-white text-[11px] font-bold rounded-xl shadow-xl flex items-center gap-1.5 whitespace-nowrap pointer-events-none transition-all duration-200 transform scale-95 opacity-0';
                note.innerHTML = `
                    <i class="fa-solid fa-circle-exclamation text-xs text-amber-200"></i>
                    <span>Harus mengubah status terlebih dahulu!</span>
                    <div class="absolute -bottom-1 right-6 w-2 h-2 bg-rose-600 transform rotate-45"></div>
                `;

                container.classList.add('relative');
                container.appendChild(note);

                // Animate in
                requestAnimationFrame(() => {
                    note.classList.remove('scale-95', 'opacity-0');
                    note.classList.add('scale-100', 'opacity-100');
                });

                // Efek visual pada tombol
                if (btn) {
                    btn.classList.add('ring-2', 'ring-rose-500');
                }
                if (preReviewBtn) {
                    preReviewBtn.classList.add('ring-2', 'ring-sky-400', 'animate-pulse');
                }

                // Hilang otomatis setelah 1 detik (1000ms)
                setTimeout(() => {
                    note.classList.remove('scale-100', 'opacity-100');
                    note.classList.add('scale-95', 'opacity-0');
                    if (btn) {
                        btn.classList.remove('ring-2', 'ring-rose-500');
                    }
                    if (preReviewBtn) {
                        preReviewBtn.classList.remove('ring-2', 'ring-sky-400', 'animate-pulse');
                    }
                    setTimeout(() => {
                        if (note.parentNode) {
                            note.parentNode.removeChild(note);
                        }
                    }, 200);
                }, 1000);

                return false;
            }

            return true;
        }
        window.handleCompletePresentation = handleCompletePresentation;
    </script>
@endsection
