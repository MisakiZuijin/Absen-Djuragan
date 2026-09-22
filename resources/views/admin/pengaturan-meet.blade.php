@extends('layouts.main')

@section('title', 'Pengaturan Link GMeet Presentasi')

@section('contents')
    @include('layouts.sidebar')
    @include('layouts.sidebar-pengaturan')
    @include('layouts.navbar')

    <!-- Main Content -->
    <main class="ml-[32rem] mt-24 p-6">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                <i class="fa-solid fa-video text-blue-600"></i>
                Manage Link GMeet Presentasi
            </h1>
            <p class="text-sm text-gray-600 mt-1">
                Atur tautan Google Meet yang digunakan anak magang untuk presentasi online berdasarkan divisi masing-masing. Anda dapat memilih beberapa divisi sekaligus menggunakan checklist agar divisi-divisi tersebut berbagi link Google Meet yang sama.
            </p>
        </div>

        @if (session('success'))
            <div id="success-message"
                class="mb-5 bg-emerald-50 border border-emerald-400 text-emerald-800 px-4 py-3 rounded-xl relative flex items-center justify-between shadow-xs transition-opacity duration-300"
                role="alert">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
                    <span class="font-medium text-sm">{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-5 bg-red-50 border border-red-400 text-red-800 px-4 py-3 rounded-xl shadow-xs" role="alert">
                <div class="font-bold text-sm mb-1 flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation text-red-600"></i>
                    Terjadi Kesalahan:
                </div>
                <ul class="list-disc list-inside text-xs space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Card 1: Form Atur Link GMeet & Checklist Divisi -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 mb-8 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center font-bold">
                        <i class="fa-solid fa-link text-sm"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-800">Form Pengaturan Link Google Meet</h2>
                        <p class="text-xs text-gray-500">Terapkan tautan meet baru ke satu atau beberapa divisi sekaligus</p>
                    </div>
                </div>
            </div>

            <form action="{{ route('admin.pengaturan.meet.assign') }}" method="POST" class="p-6">
                @csrf

                <!-- Input Link GMeet -->
                <div class="mb-6">
                    <label for="meet_url" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                        URL / Link Google Meet <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <i class="fa-solid fa-video text-blue-500"></i>
                        </div>
                        <input type="url" name="meet_url" id="meet_url" required
                            value="{{ old('meet_url') }}"
                            placeholder="https://meet.google.com/abc-defg-hij"
                            class="w-full pl-10 pr-10 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all font-mono">
                        <button type="button" onclick="document.getElementById('meet_url').value = ''; document.getElementById('meet_url').focus();"
                            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-gray-600" title="Kosongkan">
                            <i class="fa-solid fa-times-circle text-xs"></i>
                        </button>
                    </div>
                    <p class="text-[11px] text-gray-500 mt-1.5 flex items-center gap-1">
                        <i class="fa-solid fa-circle-info text-blue-500"></i>
                        Gunakan format link lengkap, contoh: <code class="bg-gray-100 px-1 py-0.5 rounded text-gray-700">https://meet.google.com/abc-defg-hij</code>
                    </p>
                </div>

                <!-- Checklist Divisi -->
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Pilih Divisi yang Menggunakan Link Ini <span class="text-red-500">*</span>
                            </label>
                            <p class="text-[11px] text-gray-500">Centang divisi yang akan menggunakan link Google Meet di atas</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="toggleSelectAllDivisions(true)"
                                class="px-3 py-1 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-semibold rounded-lg border border-blue-200 transition flex items-center gap-1.5">
                                <i class="fa-solid fa-check-double text-[11px]"></i> Pilih Semua
                            </button>
                            <button type="button" onclick="toggleSelectAllDivisions(false)"
                                class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg border border-gray-300 transition flex items-center gap-1.5">
                                <i class="fa-solid fa-xmark text-[11px]"></i> Batal Semua
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach ($divisions as $div)
                            <label class="division-card relative flex items-center p-3 rounded-xl border-2 cursor-pointer transition-all border-gray-200 hover:border-blue-400 hover:bg-blue-50/20 bg-white"
                                id="card-division-{{ $div->id }}">
                                <input type="checkbox" name="division_ids[]" value="{{ $div->id }}"
                                    class="division-checkbox w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500 mr-3"
                                    onchange="updateCardStyle({{ $div->id }})"
                                    {{ is_array(old('division_ids')) && in_array($div->id, old('division_ids')) ? 'checked' : '' }}>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-md bg-gray-100 flex items-center justify-center text-gray-600 text-xs">
                                            @if($div->icon)
                                                <i class="{{ $div->icon }} text-xs"></i>
                                            @else
                                                <i class="fa-solid fa-sitemap text-xs"></i>
                                            @endif
                                        </div>
                                        <span class="text-xs font-bold text-gray-800 truncate">{{ $div->name }}</span>
                                    </div>
                                    <div class="mt-1">
                                        @if($div->meet_url)
                                            <span class="text-[10px] text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200 inline-flex items-center gap-1 max-w-full truncate" title="{{ $div->meet_url }}">
                                                <i class="fa-solid fa-link text-[8px]"></i> Link aktif
                                            </span>
                                        @else
                                            <span class="text-[10px] text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded inline-block">
                                                Belum ada link
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Submit Action -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                    <button type="submit"
                        class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-sm hover:shadow transition flex items-center gap-2">
                        <i class="fa-solid fa-paper-plane text-xs"></i>
                        <span>Terapkan Link ke Divisi Terpilih</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Card 2: Tabel Daftar Divisi & Link GMeet Saat Ini -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-bold text-gray-800">Daftar Link GMeet per Divisi</h2>
                    <p class="text-xs text-gray-500">Tinjau atau kelola link yang sedang aktif pada masing-masing divisi</p>
                </div>
                <!-- Search -->
                <div class="relative w-full sm:w-64">
                    <input type="text" id="filterDivisionInput" placeholder="Cari divisi..."
                        onkeyup="filterDivisionTable()"
                        class="w-full text-xs pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-gray-400">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse" id="divisionsTable">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600 text-xs uppercase tracking-wider border-b border-gray-200">
                            <th class="py-3.5 px-6 font-semibold w-16">No</th>
                            <th class="py-3.5 px-6 font-semibold">Nama Divisi</th>
                            <th class="py-3.5 px-6 font-semibold">Link Google Meet Aktif</th>
                            <th class="py-3.5 px-6 font-semibold text-center w-48">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-xs">
                        @forelse ($divisions as $index => $div)
                            <tr class="hover:bg-gray-50/70 transition-colors division-row" data-name="{{ strtolower($div->name) }}">
                                <td class="py-4 px-6 text-gray-500 font-medium">{{ $index + 1 }}</td>
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                                            @if($div->icon)
                                                <i class="{{ $div->icon }} text-sm"></i>
                                            @else
                                                <i class="fa-solid fa-sitemap text-sm"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <span class="font-bold text-gray-800 block text-sm">{{ $div->name }}</span>
                                            @if($div->description)
                                                <span class="text-[11px] text-gray-400 truncate block max-w-xs">{{ $div->description }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    @if($div->meet_url)
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono text-blue-600 bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-200 max-w-xs truncate inline-block">
                                                {{ $div->meet_url }}
                                            </span>
                                            <button type="button" onclick="copyToClipboard('{{ $div->meet_url }}', this)"
                                                class="px-2 py-1 text-gray-500 hover:text-blue-600 hover:bg-gray-100 rounded transition"
                                                title="Salin Link">
                                                <i class="fa-regular fa-copy text-xs"></i>
                                            </button>
                                            <a href="{{ $div->meet_url }}" target="_blank" rel="noopener noreferrer"
                                                class="px-2 py-1 text-gray-500 hover:text-emerald-600 hover:bg-emerald-50 rounded transition"
                                                title="Buka Link Google Meet">
                                                <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                                            </a>
                                        </div>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-medium bg-amber-50 text-amber-700 border border-amber-200">
                                            <i class="fa-solid fa-circle-exclamation text-amber-500 text-[10px]"></i>
                                            Belum Diatur
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <!-- Tombol Pilih & Isi Form -->
                                        <button type="button" onclick="populateFormWithDivision({{ $div->id }}, '{{ addslashes($div->meet_url ?? '') }}')"
                                            class="px-2.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg text-xs font-semibold border border-blue-200 transition flex items-center gap-1"
                                            title="Pilih divisi ini di form atas">
                                            <i class="fa-solid fa-pen-to-square text-[10px]"></i> Edit
                                        </button>

                                        @if($div->meet_url)
                                            <!-- Tombol Hapus Link -->
                                            <form action="{{ route('admin.pengaturan.meet.clear', $div->id) }}" method="POST"
                                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus link Google Meet untuk divisi {{ addslashes($div->name) }}?')">
                                                @csrf
                                                <button type="submit"
                                                    class="px-2.5 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg text-xs font-semibold border border-red-200 transition flex items-center gap-1"
                                                    title="Hapus Link GMeet Divisi Ini">
                                                    <i class="fa-regular fa-trash-can text-[10px]"></i> Hapus
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-gray-400">
                                    <i class="fa-solid fa-folder-open text-2xl mb-2 block"></i>
                                    Belum ada data divisi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        function toggleSelectAllDivisions(select) {
            const checkboxes = document.querySelectorAll('.division-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = select;
                const divId = cb.value;
                updateCardStyle(divId);
            });
        }

        function updateCardStyle(divId) {
            const card = document.getElementById('card-division-' + divId);
            const cb = card ? card.querySelector('.division-checkbox') : null;
            if (!card || !cb) return;

            if (cb.checked) {
                card.classList.remove('border-gray-200');
                card.classList.add('border-blue-600', 'bg-blue-50/40');
            } else {
                card.classList.remove('border-blue-600', 'bg-blue-50/40');
                card.classList.add('border-gray-200');
            }
        }

        function populateFormWithDivision(divId, meetUrl) {
            // Isi input meet url jika ada
            if (meetUrl) {
                document.getElementById('meet_url').value = meetUrl;
            }

            // Uncheck semua lalu centang divisi terkait
            toggleSelectAllDivisions(false);
            const card = document.getElementById('card-division-' + divId);
            if (card) {
                const cb = card.querySelector('.division-checkbox');
                if (cb) {
                    cb.checked = true;
                    updateCardStyle(divId);
                }
            }

            // Scroll mulus ke form
            window.scrollTo({ top: 0, behavior: 'smooth' });
            document.getElementById('meet_url').focus();
        }

        function filterDivisionTable() {
            const input = document.getElementById('filterDivisionInput');
            const filter = input.value.toLowerCase();
            const rows = document.querySelectorAll('.division-row');

            rows.forEach(row => {
                const name = row.getAttribute('data-name') || '';
                if (name.includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function copyToClipboard(text, btnEl) {
            if (!navigator.clipboard) {
                // Fallback
                const textarea = document.createElement('textarea');
                textarea.value = text;
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
            } else {
                navigator.clipboard.writeText(text);
            }

            // Visual feedback
            if (btnEl) {
                const originalHtml = btnEl.innerHTML;
                btnEl.innerHTML = '<i class="fa-solid fa-check text-emerald-600 text-xs"></i>';
                btnEl.classList.add('text-emerald-600');
                setTimeout(() => {
                    btnEl.innerHTML = originalHtml;
                    btnEl.classList.remove('text-emerald-600');
                }, 1500);
            }
        }

        // Init cards on page load (in case old input was checked)
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('.division-checkbox');
            checkboxes.forEach(cb => {
                if (cb.checked) {
                    updateCardStyle(cb.value);
                }
            });
        });
    </script>
@endsection