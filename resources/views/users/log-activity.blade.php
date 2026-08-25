@extends('users.layouts.main')

@section('title', 'History Log Activity')

@section('contents')
    <!-- Main Content -->
    <div class="max-w-6xl mx-auto px-4 py-8">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row items-center justify-between mb-8">
            <!-- Back Button -->
            <a href="{{ route('user.home') }}"
                class="flex items-center text-gray-600 hover:text-blue-600 transition-colors mb-4 md:mb-0">
                <i class="fas fa-arrow-left mr-2"></i>
                <span class="text-sm md:text-base">Kembali ke Beranda</span>
            </a>

            <!-- Title -->
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800 text-center">History Log Activity</h1>

            <!-- Spacer for balance -->
            <div class="w-8 md:w-24"></div>
        </div>

        <!-- Table Container -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600 text-sm font-medium">
                            <th class="py-4 px-6 text-left">No</th>
                            <th class="py-4 px-6 text-left">
                                <div class="flex items-center">
                                    <span>Tanggal</span>
                                    <button id="sort-date"
                                        class="ml-2 text-gray-400 hover:text-gray-600 focus:outline-none">
                                        <i class="fas fa-sort"></i>
                                    </button>
                                </div>
                            </th>
                            <th class="py-4 px-6 text-left">Activity Log</th>
                            <th class="py-4 px-6 text-left">Status</th>
                            <th class="py-4 px-6 text-left">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700 divide-y divide-gray-100" id="activity-body">
                        @isset($data)
                            @php
                                // Mengurutkan data berdasarkan tanggal (dari yang terbaru)
                                $sortedData = $data->sortByDesc('date');
                            @endphp
                            @forelse ($sortedData as $key => $history)
                                @php
                                    $statusClass = 'bg-gray-100 text-gray-700';
                                    switch ($history->status->id) {
                                        case 1:
                                            $statusClass = 'bg-yellow-100 text-yellow-700';
                                            break;
                                        case 2:
                                            $statusClass = 'bg-green-100 text-green-700';
                                            break;
                                        case 3:
                                            $statusClass = 'bg-red-100 text-red-700';
                                            break;
                                    }
                                @endphp
                                <tr class="hover:bg-gray-50 transition-colors" data-date="{{ $history->date }}">
                                    <td class="py-4 px-6 text-sm">{{ $key + 1 }}</td>
                                    <td class="py-4 px-6 text-sm whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($history->date)->locale('id')->isoFormat('dddd, DD-MM-YYYY') }}
                                    </td>
                                    <td class="py-4 px-6 text-sm max-w-md">
                                        <div class="whitespace-normal break-words">
                                            {{ $history->activity }}
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $statusClass }} relative group">
                                            {{ $history->status->name }}

                                            @if ($history->status->id === 3)
                                                <span class="ml-1 cursor-help">
                                                    <i class="fas fa-info-circle"></i>
                                                    <!-- Tooltip -->
                                                    <div
                                                        class="absolute hidden group-hover:block z-10 w-48 p-3 mt-2 -ml-40 bg-gray-800 text-white text-xs rounded-lg shadow-lg">
                                                        <div class="font-semibold mb-1">Informasi Status</div>
                                                        <div>Status ini memerlukan perhatian khusus. Silakan periksa detailnya.</div>
                                                        <div class="absolute w-3 h-3 bg-gray-800 rotate-45 -bottom-1 left-1/2 -ml-1.5">
                                                        </div>
                                                    </div>
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        @if ($history->status->id == 2)
                                            <div class="flex justify-center text-green-500">
                                                <i class="fas fa-check-circle text-lg"></i>
                                            </div>
                                        @else
                                            <button onclick="togglePopup({{ $history->id }}, '{{ addslashes($history->activity) }}')"
                                                class="px-3 py-1.5 bg-blue-500 text-white text-sm rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-200 transition-colors">
                                                Edit
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-12 text-center text-gray-500">
                                        <i class="fas fa-inbox text-4xl mb-3 text-gray-300"></i>
                                        <p>Belum ada data log activity</p>
                                    </td>
                                </tr>
                            @endforelse
                        @else
                            <tr>
                                <td colspan="5" class="py-12 text-center text-gray-500">
                                    <i class="fas fa-exclamation-triangle text-4xl mb-3 text-gray-300"></i>
                                    <p>Data tidak tersedia</p>
                                </td>
                            </tr>
                        @endisset
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                <div class="flex justify-center items-center space-x-2">
                    <button id="prev-page"
                        class="flex items-center px-3 py-1.5 bg-white text-gray-700 rounded-md border border-gray-300 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed text-sm transition-colors"
                        disabled>
                        <i class="fas fa-chevron-left mr-1 text-xs"></i>
                        <span>Sebelumnya</span>
                    </button>

                    <div id="page-numbers" class="flex space-x-1"></div>

                    <button id="next-page"
                        class="flex items-center px-3 py-1.5 bg-white text-gray-700 rounded-md border border-gray-300 hover:bg-gray-50 text-sm transition-colors">
                        <span>Selanjutnya</span>
                        <i class="fas fa-chevron-right ml-1 text-xs"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Activity Popup -->
    <div id="popup-form" class="fixed inset-0 hidden flex items-center justify-center bg-black bg-opacity-50 z-50 p-4">
        <div class="bg-white rounded-lg shadow-md w-full max-w-md">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-800">Edit Activity Log</h2>
            </div>

            <!-- Form -->
            <form id="edit-activity-form" method="POST" action="{{ route('home.logActivity.update.action') }}"
                class="px-6 py-4">
                @csrf
                <input id="id" name="id" type="number" hidden>

                <div class="mb-5">
                    <label class="block text-gray-700 text-sm font-medium mb-2" for="activity">
                        Keterangan<span class="text-red-500">*</span>
                    </label>
                    <textarea
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition-colors"
                        id="activity" name="activity" rows="5" placeholder="Apa yang telah anda kerjakan hari ini"
                        required></textarea>
                    <div id="activity-error" class="text-red-500 text-xs mt-1 hidden"></div>
                    <p class="text-xs text-gray-500 mt-1">Hanya huruf, angka, spasi, dan tanda baca dasar yang
                        diperbolehkan. Tanda petik (', ") tidak diperbolehkan.</p>
                </div>

                <div class="flex justify-end space-x-2 pt-4 border-t border-gray-200">
                    <button onclick="togglePopup()" id="back" type="button"
                        class="px-4 py-2 bg-gray-200 text-gray-700 text-sm rounded-md hover:bg-gray-300 focus:outline-none transition-colors">
                        Batal
                    </button>
                    <button id="submit-button" type="submit"
                        class="px-4 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700 focus:outline-none transition-colors">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="{{ asset('js/user/log-activity.js') }}"></script>
    <script>
        // Fungsi untuk memvalidasi input activity
        function validateActivityInput(input) {
            // Regex yang lebih ketat - mencegah tanda petik tunggal dan ganda
            // Hanya menerima huruf (a-z, A-Z), angka (0-9), spasi, dan tanda baca dasar
            // Tanda petik (', ") tidak diperbolehkan
            const regex = /^[a-zA-Z0-9\s.,!?():;-]+$/;

            // Periksa setiap baris input
            const lines = input.split('\n');
            for (let i = 0; i < lines.length; i++) {
                if (!regex.test(lines[i].trim()) && lines[i].trim() !== '') {
                    return false;
                }
            }
            return true;
        }

        // Event listener untuk form edit activity
        document.getElementById('edit-activity-form').addEventListener('submit', function (e) {
            const activityInput = document.getElementById('activity').value.trim();
            const errorDiv = document.getElementById('activity-error');

            // Validasi input kosong
            if (!activityInput) {
                errorDiv.textContent = 'Keterangan aktivitas tidak boleh kosong.';
                errorDiv.classList.remove('hidden');
                e.preventDefault();
                return;
            }

            // Validasi dengan regex
            if (!validateActivityInput(activityInput)) {
                errorDiv.textContent = 'Input mengandung karakter yang tidak diizinkan. Hanya huruf, angka, spasi, dan tanda baca dasar yang diperbolehkan. Tanda petik (\', ") tidak diperbolehkan.';
                errorDiv.classList.remove('hidden');
                e.preventDefault();
                return;
            }

            // Jika validasi berhasil, sembunyikan pesan error
            errorDiv.classList.add('hidden');
        });

        // Event listener untuk input activity (real-time validation)
        document.getElementById('activity').addEventListener('input', function () {
            const errorDiv = document.getElementById('activity-error');
            const input = this.value;

            if (!validateActivityInput(input)) {
                errorDiv.textContent = 'Input mengandung karakter yang tidak diizinkan. Hanya huruf, angka, spasi, dan tanda baca dasar yang diperbolehkan. Tanda petik (\', ") tidak diperbolehkan.';
                errorDiv.classList.remove('hidden');
            } else {
                errorDiv.classList.add('hidden');
            }
        });

        // Fungsi untuk membersihkan input dari karakter yang tidak diinginkan
        document.getElementById('activity').addEventListener('blur', function () {
            const input = this.value;
            // Hapus karakter yang tidak diinginkan
            const cleanedInput = input.replace(/['"`<>{}[\]\\\/|&$%#@*+=]/g, '');
            if (input !== cleanedInput) {
                this.value = cleanedInput;
                const errorDiv = document.getElementById('activity-error');
                errorDiv.textContent = 'Karakter tidak valid telah dihapus otomatis.';
                errorDiv.classList.remove('hidden');

                // Sembunyikan pesan error setelah 3 detik
                setTimeout(() => {
                    errorDiv.classList.add('hidden');
                }, 3000);
            }
        });

        // Fungsi togglePopup yang sudah ada (jika ada di log-activity.js)
        function togglePopup(id = null, activity = '') {
            const popup = document.getElementById('popup-form');
            if (popup.classList.contains('hidden')) {
                if (id && activity) {
                    document.getElementById('id').value = id;
                    // Bersihkan activity dari karakter berbahaya sebelum menampilkan
                    const cleanedActivity = activity.replace(/\\'/g, "'").replace(/['"`<>{}[\]\\\/|&$%#@*+=]/g, '');
                    document.getElementById('activity').value = cleanedActivity;
                }
                popup.classList.remove('hidden');
                document.body.style.overflow = 'hidden'; // Prevent background scrolling
            } else {
                popup.classList.add('hidden');
                document.body.style.overflow = 'auto'; // Re-enable scrolling
                // Reset form ketika menutup popup
                document.getElementById('edit-activity-form').reset();
                document.getElementById('activity-error').classList.add('hidden');
            }
        }

        // Fungsi untuk mengurutkan data berdasarkan tanggal
        document.getElementById('sort-date').addEventListener('click', function () {
            const tbody = document.getElementById('activity-body');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            const isAscending = this.getAttribute('data-sort') === 'asc';

            // Toggle status sort
            this.setAttribute('data-sort', isAscending ? 'desc' : 'asc');

            // Ubah ikon sort
            const icon = this.querySelector('i');
            if (isAscending) {
                icon.className = 'fas fa-sort-down';
            } else {
                icon.className = 'fas fa-sort-up';
            }

            // Urutkan baris berdasarkan tanggal
            rows.sort((a, b) => {
                const dateA = new Date(a.getAttribute('data-date'));
                const dateB = new Date(b.getAttribute('data-date'));

                return isAscending ? dateA - dateB : dateB - dateA;
            });

            // Hapus semua baris dari tbody
            while (tbody.firstChild) {
                tbody.removeChild(tbody.firstChild);
            }

            // Tambahkan baris yang sudah diurutkan
            rows.forEach((row, index) => {
                // Update nomor urut
                row.querySelector('td:first-child').textContent = index + 1;
                tbody.appendChild(row);
            });
        });
    </script>
@endsection
