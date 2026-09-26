@extends('layouts.main')

@section('title', 'Monitoring Raise Hand & Presentasi')

@section('contents')
    <main class="ml-0 md:ml-64 mt-16 md:mt-20 p-4 sm:p-6 lg:p-8 bg-gray-50 min-h-screen min-w-0">
        <div class="max-w-7xl mx-auto space-y-6">
            <!-- Header Section -->
            <div class="mb-6 md:mb-8">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3 sm:gap-4">
                        <div class="p-3 sm:p-4 bg-indigo-600 rounded-2xl shadow-sm text-white shrink-0">
                            <i class="fa-solid fa-hand-paper text-xl sm:text-2xl"></i>
                        </div>
                        <div>
                            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900">Monitoring Raise Hand & Presentasi</h1>
                            <p class="text-xs sm:text-sm text-gray-500 mt-0.5">Pantau status pertanyaan, tugas baru, antrean presentasi, dan riwayat siswa magang</p>
                        </div>
                    </div>
                    <!-- Auto-refresh indicator -->
                    <div class="self-start sm:self-auto flex items-center gap-2 text-xs bg-white border border-gray-200 px-3 py-2 rounded-xl shadow-xs">
                        <div id="refreshIndicator" class="w-2.5 h-2.5 bg-emerald-500 rounded-full animate-pulse"></div>
                        <span class="text-gray-600 font-medium">Auto-refresh aktif</span>
                    </div>
                </div>
            </div>

            <!-- Livewire Component for Raise Hand Management -->
            @livewire('admin.raise-hand-manager')
        </div>
    </main>

    <!-- MODAL DETAIL RIWAYAT / EVALUASI (VIEW ONLY) -->
    <div id="detailHistoryModal" class="fixed inset-0 bg-black/60 backdrop-blur-xs flex items-center justify-center z-[9999] hidden p-4">
        <div class="bg-white rounded-2xl p-5 sm:p-6 w-full max-w-lg shadow-2xl animate-fadeIn flex flex-col max-h-[90vh]">
            <div class="flex items-center justify-between pb-3.5 border-b border-gray-100 mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600 shrink-0">
                        <i class="fa-solid fa-file-lines text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Detail Riwayat & Evaluasi</h3>
                        <p class="text-xs text-gray-500">Informasi nilai dan catatan penilaian</p>
                    </div>
                </div>
                <button type="button" onclick="closeDetailHistoryModal()" class="text-gray-400 hover:text-gray-600 text-xl font-bold p-1">&times;</button>
            </div>

            <div class="space-y-4 overflow-y-auto pr-1">
                <!-- Info Siswa -->
                <div class="p-3.5 bg-gray-50 rounded-xl border border-gray-200 text-xs space-y-1.5">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500 font-medium">Peserta:</span>
                        <strong class="text-gray-900 font-bold" id="detail-eval-name">-</strong>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500 font-medium">Judul Tugas / Modul:</span>
                        <strong class="text-gray-900" id="detail-eval-title">-</strong>
                    </div>
                </div>

                <!-- Nilai -->
                <div id="detail-eval-rating-wrap" class="p-3.5 bg-emerald-50 rounded-xl border border-emerald-200 flex items-center justify-between">
                    <span class="text-xs font-semibold text-emerald-800">Nilai Evaluasi:</span>
                    <span class="text-base font-black text-emerald-700" id="detail-eval-rating">-</span>
                </div>

                <!-- Catatan / Tanggapan Mentor -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Catatan / Evaluasi Pembimbing:</label>
                    <div class="p-3.5 bg-gray-50 rounded-xl border border-gray-200 text-xs text-gray-800 whitespace-pre-line leading-relaxed min-h-[80px]" id="detail-eval-notes">
                        -
                    </div>
                </div>
            </div>

            <div class="mt-5 pt-3 border-t border-gray-100 flex justify-end">
                <button type="button" onclick="closeDetailHistoryModal()" class="px-5 py-2 bg-gray-800 hover:bg-gray-700 text-white text-xs font-semibold rounded-xl transition shadow-xs">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <script>
        function viewEvaluationDetails(name, rating, notes, title) {
            const nameEl = document.getElementById('detail-eval-name');
            if (nameEl) nameEl.textContent = name;
            const titleEl = document.getElementById('detail-eval-title');
            if (titleEl) titleEl.textContent = title || '-';
            
            const ratingWrap = document.getElementById('detail-eval-rating-wrap');
            const ratingEl = document.getElementById('detail-eval-rating');
            if (rating && rating.trim() !== '' && rating !== '0') {
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

        // Global Event Delegation untuk tombol detail evaluasi
        document.addEventListener('click', function(e) {
            const detailBtn = e.target.closest('.btn-trigger-detail');
            if (detailBtn) {
                e.preventDefault();
                viewEvaluationDetails(
                    detailBtn.dataset.name,
                    detailBtn.dataset.rating || '',
                    detailBtn.dataset.response || '',
                    detailBtn.dataset.title || ''
                );
            }
        });

        // Close on ESC key or clicking backdrop
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeDetailHistoryModal();
            }
        });

        const detailModal = document.getElementById('detailHistoryModal');
        if (detailModal) {
            detailModal.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeDetailHistoryModal();
                }
            });
        }
    </script>
@endsection