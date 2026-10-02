@extends('layouts.main')

@section('title', 'Pengaturan Link GMeet Presentasi')

@section('contents')
@include('layouts.sidebar-pengaturan')

<!-- Main Content -->
<main class="ml-0 lg:ml-[32rem] mt-2 lg:mt-20 p-3 sm:p-5 min-w-0 max-w-full overflow-x-hidden">
    <div class="w-full max-w-4xl min-w-0 space-y-5">

        <!-- Header -->
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-xs">
            <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                <i class="fa-solid fa-video text-blue-600"></i>
                Manage Link GMeet Presentasi
            </h1>
            <p class="text-xs text-gray-500 mt-1">
                Atur tautan Google Meet yang digunakan anak magang untuk presentasi online berdasarkan divisi masing-masing.
            </p>
        </div>

        @if (session('success'))
        <div id="success-message"
            class="bg-emerald-50 border border-emerald-300 text-emerald-800 px-4 py-3 rounded-xl relative flex items-center justify-between shadow-xs transition-opacity duration-300"
            role="alert">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
                <span class="font-medium text-xs sm:text-sm">{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800">
                <i class="fa-solid fa-xmark text-base"></i>
            </button>
        </div>
        @endif

        @if ($errors->any())
        <div class="bg-rose-50 border border-rose-300 text-rose-800 px-4 py-3 rounded-xl shadow-xs" role="alert">
            <div class="font-bold text-xs sm:text-sm mb-1 flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation text-rose-600"></i>
                Terjadi Kesalahan:
            </div>
            <ul class="list-disc list-inside text-xs space-y-0.5">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Card 1: Form Atur Link GMeet & Checklist Divisi -->
        <div class="bg-white rounded-2xl shadow-xs border border-gray-100 overflow-hidden w-full max-w-full min-w-0">
            <div class="px-5 py-3.5 border-b border-gray-100 bg-gray-50/70 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-lg bg-blue-600 text-white flex items-center justify-center font-bold shrink-0 shadow-xs">
                        <i class="fa-solid fa-link text-xs"></i>
                    </div>
                    <div class="min-w-0">
                        <h2 class="text-xs sm:text-sm font-bold text-gray-900 truncate">Form Pengaturan Link Google Meet</h2>
                        <p class="text-[11px] text-gray-500 truncate">Terapkan tautan meet baru ke satu atau beberapa divisi sekaligus</p>
                    </div>
                </div>
            </div>

            <form action="{{ route('admin.pengaturan.meet.assign') }}" method="POST" class="p-4 sm:p-5 w-full max-w-full min-w-0 space-y-4">
                @csrf

                <!-- Input Link GMeet -->
                <div class="w-full">
                    <label for="meet_url" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        URL / Link Google Meet <span class="text-rose-500 ml-0.5">*</span>
                    </label>
                    <div class="relative w-full">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <i class="fa-solid fa-video text-blue-500 text-xs"></i>
                        </div>
                        <input type="url" name="meet_url" id="meet_url" required
                            value="{{ old('meet_url') }}"
                            placeholder="https://meet.google.com/abc-defg-hij"
                            class="w-full pl-8 pr-8 py-2 text-xs sm:text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none font-mono">
                        <button type="button" onclick="document.getElementById('meet_url').value = ''; document.getElementById('meet_url').focus();"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600" title="Kosongkan">
                            <i class="fa-solid fa-times-circle text-xs"></i>
                        </button>
                    </div>
                    <p class="text-[11px] text-gray-400 mt-1 flex items-center gap-1">
                        <i class="fa-solid fa-circle-info text-blue-500"></i>
                        Contoh: <code class="bg-gray-100 px-1 py-0.5 rounded text-gray-700 font-mono">https://meet.google.com/abc-defg-hij</code>
                    </p>
                </div>

                <!-- Checklist Divisi -->
                <div class="w-full min-w-0 pt-2 border-t border-gray-100">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2.5">
                        <div class="min-w-0">
                            <span class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Pilih Target Divisi <span class="text-rose-500 ml-0.5">*</span>
                            </span>
                            <p class="text-[11px] text-gray-400">Centang divisi yang akan menggunakan link ini</p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <button type="button" onclick="toggleSelectAllDivisions(true)"
                                class="px-2.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-[11px] font-bold rounded-lg shadow-xs transition flex items-center gap-1">
                                <i class="fa-solid fa-check-double text-[10px]"></i> Pilih Semua
                            </button>
                            <button type="button" onclick="toggleSelectAllDivisions(false)"
                                class="px-2.5 py-1.5 bg-gray-600 hover:bg-gray-700 text-white text-[11px] font-bold rounded-lg shadow-xs transition flex items-center gap-1">
                                <i class="fa-solid fa-xmark text-[10px]"></i> Batal Semua
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 w-full min-w-0">
                        @foreach ($divisions as $div)
                        <label class="division-card relative flex items-center p-2.5 rounded-xl border border-gray-200 hover:border-blue-400 hover:bg-blue-50/20 bg-white cursor-pointer transition-all min-w-0 overflow-hidden"
                            id="card-division-{{ $div->id }}">
                            <input type="checkbox" name="division_ids[]" value="{{ $div->id }}"
                                class="division-checkbox w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500 mr-2.5 shrink-0"
                                onchange="updateCardStyle(this.value)"
                                {{ is_array(old('division_ids')) && in_array($div->id, old('division_ids')) ? 'checked' : '' }}>
                            <div class="flex-1 min-w-0 overflow-hidden">
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <div class="w-5 h-5 rounded-md bg-gray-100 flex items-center justify-center text-gray-600 text-xs shrink-0">
                                        @if($div->icon)
                                        <img src="{{ asset('img/' . $div->icon) }}" alt="{{ $div->name }} Icon"
                                            class="w-3 h-3 object-contain">
                                        @else
                                        <i class="fa-solid fa-sitemap text-[10px]"></i>
                                        @endif
                                    </div>
                                    <span class="text-xs font-bold text-gray-800 truncate block">{{ $div->name }}</span>
                                </div>
                                <div class="mt-1 min-w-0">
                                    @if($div->meet_url)
                                    <span class="text-[10px] text-white bg-emerald-600 px-1.5 py-0.5 rounded font-semibold inline-flex items-center gap-1 max-w-full truncate shadow-xs" title="{{ $div->meet_url }}">
                                        <i class="fa-solid fa-link text-[8px] shrink-0"></i> <span class="truncate">Link aktif</span>
                                    </span>
                                    @else
                                    <span class="text-[10px] text-white bg-gray-400 px-1.5 py-0.5 rounded font-semibold inline-block">
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
                <div class="flex items-center justify-end pt-3 border-t border-gray-100">
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-2">
                        <i class="fa-solid fa-paper-plane text-xs"></i>
                        <span>Terapkan Link ke Divisi Terpilih</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Card 2: Tabel Daftar Divisi & Link GMeet Saat Ini -->
        <div class="bg-white rounded-2xl shadow-xs border border-gray-100 overflow-hidden w-full max-w-full min-w-0">
            <div class="px-5 py-3.5 border-b border-gray-100 bg-gray-50/70 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="min-w-0">
                    <h2 class="text-xs sm:text-sm font-bold text-gray-900 truncate">Daftar Link GMeet per Divisi</h2>
                    <p class="text-[11px] text-gray-500 truncate">Tinjau atau kelola link yang sedang aktif pada masing-masing divisi</p>
                </div>
                <!-- Search -->
                <div class="relative w-full sm:w-56 shrink-0">
                    <input type="text" id="filterDivisionInput" placeholder="Cari divisi..."
                        onkeyup="filterDivisionTable()"
                        class="w-full text-xs pl-8 pr-3 py-1.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-gray-400">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto w-full max-w-full min-w-0">
                <table class="w-full text-left border-collapse text-xs" id="divisionsTable">
                    <thead>
                        <tr class="bg-gray-50/80 text-gray-500 text-[11px] uppercase tracking-wider border-b border-gray-100 font-bold">
                            <th class="py-3 px-3 text-center w-12">No</th>
                            <th class="py-3 px-3">Nama Divisi</th>
                            <th class="py-3 px-3">Link Google Meet Aktif</th>
                            <th class="py-3 px-3 text-right pr-4 w-32">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-xs font-medium text-gray-700">
                        @forelse ($divisions as $index => $div)
                        <tr class="hover:bg-gray-50/80 transition-colors division-row" data-name="{{ strtolower($div->name) }}">
                            <td class="py-2.5 px-3 text-center text-gray-400 font-bold">{{ $index + 1 }}</td>
                            <td class="py-2.5 px-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold shrink-0">
                                        @if($div->icon)
                                        <img src="{{ asset('img/' . $div->icon) }}" alt="{{ $div->name }} Icon"
                                            class="w-3.5 h-3.5 object-contain">
                                        @else
                                        <i class="fa-solid fa-sitemap text-xs"></i>
                                        @endif
                                    </div>
                                    <span class="font-semibold text-gray-900 text-xs">{{ $div->name }}</span>
                                </div>
                            </td>
                            <td class="py-2.5 px-3">
                                @if($div->meet_url)
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="font-mono text-slate-800 bg-slate-100 px-2 py-0.5 rounded-lg border border-slate-200 max-w-[180px] sm:max-w-[220px] truncate inline-block text-[11px]">
                                        {{ $div->meet_url }}
                                    </span>
                                    <button type="button" onclick="copyToClipboard('{{ $div->meet_url }}', this)"
                                        class="p-1.5 bg-gray-700 hover:bg-gray-800 text-white rounded-lg shadow-xs transition shrink-0"
                                        title="Salin Link">
                                        <i class="fa-regular fa-copy text-xs"></i>
                                    </button>
                                    <a href="{{ $div->meet_url }}" target="_blank" rel="noopener noreferrer"
                                        class="p-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow-xs transition shrink-0"
                                        title="Buka Link Google Meet">
                                        <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                                    </a>
                                </div>
                                @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500 text-white shadow-xs">
                                    <i class="fa-solid fa-circle-exclamation text-[9px]"></i>
                                    Belum Diatur
                                </span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 text-right pr-4">
                                <div class="inline-flex items-center justify-end gap-1.5">
                                    <button type="button"
                                        onclick="populateFormWithDivisionFromButton(this)"
                                        data-id="{{ $div->id }}"
                                        data-url="{{ $div->meet_url ?? '' }}"
                                        class="px-2.5 py-1 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-xs font-bold shadow-xs transition flex items-center gap-1"
                                        title="Pilih divisi ini di form atas">
                                        <i class="fa-solid fa-pen-to-square text-[10px]"></i> Edit
                                    </button>

                                    @if($div->meet_url)
                                    <form action="{{ route('admin.pengaturan.meet.clear', $div->id) }}" method="POST"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus link Google Meet untuk divisi {{ addslashes($div->name) }}?')">
                                        @csrf
                                        <button type="submit"
                                            class="px-2.5 py-1 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-bold shadow-xs transition flex items-center gap-1"
                                            title="Hapus Link GMeet Divisi Ini">
                                            <i class="fa-solid fa-trash text-[10px]"></i> Hapus
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-6 text-center text-gray-400 text-xs">
                                <i class="fa-solid fa-folder-open text-2xl mb-1.5 block text-gray-300"></i>
                                Belum ada data divisi.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
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

    function populateFormWithDivisionFromButton(btn) {
        const divId = btn.getAttribute('data-id');
        const meetUrl = btn.getAttribute('data-url') || '';
        populateFormWithDivision(divId, meetUrl);
    }

    function populateFormWithDivision(divId, meetUrl) {
        if (meetUrl) {
            document.getElementById('meet_url').value = meetUrl;
        }

        toggleSelectAllDivisions(false);
        const card = document.getElementById('card-division-' + divId);
        if (card) {
            const cb = card.querySelector('.division-checkbox');
            if (cb) {
                cb.checked = true;
                updateCardStyle(divId);
            }
        }

        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
        document.getElementById('meet_url').focus();
    }

    function filterDivisionTable() {
        const input = document.getElementById('filterDivisionInput');
        const filter = (input.value || '').toLowerCase();
        const rows = document.querySelectorAll('.division-row');

        rows.forEach(row => {
            const name = (row.getAttribute('data-name') || '').toLowerCase();
            if (name.includes(filter)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    function copyToClipboard(text, btnEl) {
        if (!navigator.clipboard) {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
        } else {
            navigator.clipboard.writeText(text);
        }

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