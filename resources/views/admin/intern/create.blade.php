@extends('layouts.main')

@section('title', 'Pendaftaran Pemagang Baru')

@section('contents')
<!-- Main Content -->
<main class="ml-0 md:ml-64 mt-16 md:mt-20 p-3 sm:p-6 bg-slate-50/50 min-h-screen min-w-0">
    <div class="max-w-5xl mx-auto space-y-4 sm:space-y-6">

        <!-- Top Navigation / Breadcrumb -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 sm:pb-4 border-b border-slate-200">
            <a href="{{ route('admin.division') }}" 
                class="inline-flex items-center gap-2 text-xs sm:text-sm font-semibold text-slate-600 hover:text-indigo-600 transition">
                <i class="fa-solid fa-arrow-left text-xs"></i>
                <span>Kembali ke Daftar Divisi</span>
            </a>
            <div class="text-[11px] sm:text-xs text-slate-400 font-medium">
                Panel Manajemen &bull; Tambah Anggota Pemagang
            </div>
        </div>

        <!-- Header Card -->
        <div class="bg-gradient-to-r from-blue-700 via-indigo-700 to-indigo-800 rounded-2xl p-4 sm:p-6 text-white shadow-md relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 w-36 h-36 bg-white/10 rounded-full blur-xl pointer-events-none"></div>
            <div class="relative z-10 flex items-center gap-3.5 sm:gap-4">
                <div class="w-11 h-11 sm:w-14 sm:h-14 shrink-0 rounded-xl sm:rounded-2xl bg-white/15 backdrop-blur-md flex items-center justify-center border border-white/20 shadow-inner">
                    <i class="fa-solid fa-user-plus text-lg sm:text-2xl text-white"></i>
                </div>
                <div>
                    <h1 class="text-lg sm:text-2xl font-bold tracking-tight">Pendaftaran Pemagang Baru</h1>
                    <p class="text-blue-100 text-xs mt-0.5 sm:mt-1 leading-relaxed">Lengkapi formulir di bawah ini untuk membuat akun dan menetapkan divisi, shift, serta sekolah pemagang secara langsung.</p>
                </div>
            </div>
        </div>

        <!-- Alert Error / Validation -->
        @if ($errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-800 p-3.5 sm:p-4 rounded-xl shadow-xs text-xs sm:text-sm">
            <div class="flex items-center gap-2 font-bold mb-1">
                <i class="fa-solid fa-circle-exclamation text-rose-600"></i>
                <span>Terdapat kesalahan pada formulir pendaftaran:</span>
            </div>
            <ul class="list-disc list-inside text-xs space-y-1 ml-2 text-rose-700">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @if (session('error'))
        <div class="bg-rose-50 border border-rose-200 text-rose-800 p-3.5 sm:p-4 rounded-xl shadow-xs text-xs sm:text-sm flex items-center gap-2 font-semibold">
            <i class="fa-solid fa-circle-exclamation text-rose-600 text-base sm:text-lg shrink-0"></i>
            <span>{{ session('error') }}</span>
        </div>
        @endif

        <!-- Form Tambah Pemagang -->
        <form action="{{ route('admin.interns.store') }}" method="POST" class="space-y-4 sm:space-y-6">
            @csrf

            <!-- Section 1: Informasi Akun Login -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-4 sm:p-6 space-y-4">
                <div class="flex items-center gap-2.5 sm:gap-3 border-b border-slate-100 pb-3">
                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs sm:text-sm font-bold shrink-0">1</div>
                    <div>
                        <h2 class="text-xs sm:text-sm font-bold text-slate-800">Informasi Akun & Login</h2>
                        <p class="text-slate-400 text-[11px] leading-tight">Kredensial yang akan digunakan oleh pemagang untuk masuk ke portal sistem</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5 sm:gap-4">
                    <!-- Nama Lengkap -->
                    <div class="md:col-span-2">
                        <label for="full_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Nama Lengkap <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="full_name" name="full_name" required
                            value="{{ old('full_name') }}"
                            placeholder="Contoh: Muhammad Raihan Pratama"
                            class="w-full px-3.5 py-2.5 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition font-medium text-slate-800">
                    </div>

                    <!-- Username -->
                    <div>
                        <label for="username" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Username <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 text-xs sm:text-sm">@</span>
                            <input type="text" id="username" name="username" required
                                value="{{ old('username') }}"
                                placeholder="raihan_pratama"
                                class="w-full pl-8 pr-3.5 py-2.5 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition font-medium text-slate-800">
                        </div>
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Alamat Email <span class="text-rose-500">*</span>
                        </label>
                        <input type="email" id="email" name="email" required
                            value="{{ old('email') }}"
                            placeholder="raihan@example.com"
                            class="w-full px-3.5 py-2.5 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition font-medium text-slate-800">
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Password <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="password" id="password" name="password" required minlength="6"
                                placeholder="Minimal 6 karakter"
                                class="w-full pl-3.5 pr-10 py-2.5 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition font-medium text-slate-800">
                            <button type="button" onclick="togglePass('password', 'eye1')" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none">
                                <i class="fa-regular fa-eye" id="eye1"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Konfirmasi Password -->
                    <div>
                        <label for="password_confirmation" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Konfirmasi Password <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="password" id="password_confirmation" name="password_confirmation" required minlength="6"
                                placeholder="Ulangi password di atas"
                                class="w-full pl-3.5 pr-10 py-2.5 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition font-medium text-slate-800">
                            <button type="button" onclick="togglePass('password_confirmation', 'eye2')" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none">
                                <i class="fa-regular fa-eye" id="eye2"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Toggle Status GPS -->
                    <div class="md:col-span-2">
                        <label for="is_gps_active" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Kewajiban Lokasi GPS <span class="text-rose-500">*</span>
                        </label>
                        <select id="is_gps_active" name="is_gps_active" required
                            class="w-full px-3.5 py-2.5 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition font-medium text-slate-800">
                            <option value="1" {{ old('is_gps_active', '1') == '1' ? 'selected' : '' }}>Aktif (Wajib berada di radius kantor saat presensi WFO)</option>
                            <option value="0" {{ old('is_gps_active') == '0' ? 'selected' : '' }}>Non-Aktif (Bebas radius GPS / WFH Penuh)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Section 2: Informasi Pribadi & Kontak -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-4 sm:p-6 space-y-4">
                <div class="flex items-center gap-2.5 sm:gap-3 border-b border-slate-100 pb-3">
                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs sm:text-sm font-bold shrink-0">2</div>
                    <div>
                        <h2 class="text-xs sm:text-sm font-bold text-slate-800">Data Pribadi & Kontak</h2>
                        <p class="text-slate-400 text-[11px] leading-tight">Informasi kontak dan biodata pemagang</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5 sm:gap-4">
                    <!-- No WhatsApp / Telepon -->
                    <div>
                        <label for="phone" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            No. WhatsApp / HP <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="phone" name="phone" required
                            value="{{ old('phone') }}"
                            placeholder="Contoh: 081234567890"
                            class="w-full px-3.5 py-2.5 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition font-medium text-slate-800">
                    </div>

                    <!-- Jenis Kelamin -->
                    <div>
                        <label for="gender" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Jenis Kelamin <span class="text-rose-500">*</span>
                        </label>
                        <select id="gender" name="gender" required
                            class="w-full px-3.5 py-2.5 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition font-medium text-slate-800">
                            <option value="" disabled {{ old('gender') ? '' : 'selected' }}>-- Pilih Jenis Kelamin --</option>
                            <option value="Laki-laki" {{ old('gender') == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="Perempuan" {{ old('gender') == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>

                    <!-- Tempat Lahir -->
                    <div>
                        <label for="birth_place" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Tempat Lahir
                        </label>
                        <input type="text" id="birth_place" name="birth_place"
                            value="{{ old('birth_place') }}"
                            placeholder="Contoh: Jember"
                            class="w-full px-3.5 py-2.5 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition font-medium text-slate-800">
                    </div>

                    <!-- Tanggal Lahir -->
                    <div>
                        <label for="date_of_birth" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Tanggal Lahir
                        </label>
                        <input type="date" id="date_of_birth" name="date_of_birth"
                            value="{{ old('date_of_birth') }}"
                            class="w-full px-3.5 py-2.5 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition font-medium text-slate-800">
                    </div>

                    <!-- NIP / No Induk Presensi -->
                    <div>
                        <label for="nip" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            NIP (Nomor Induk Pemagang)
                        </label>
                        <input type="text" id="nip" name="nip"
                            value="{{ old('nip') }}"
                            placeholder="Otomatis / input manual jika ada"
                            class="w-full px-3.5 py-2.5 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition font-medium text-slate-800">
                    </div>

                    <!-- NIM / NISN Asal Kampus/Sekolah -->
                    <div>
                        <label for="nim" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            NIM / NISN (Asal Sekolah/Kampus)
                        </label>
                        <input type="text" id="nim" name="nim"
                            value="{{ old('nim') }}"
                            placeholder="Nomor Induk Mahasiswa / Siswa"
                            class="w-full px-3.5 py-2.5 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition font-medium text-slate-800">
                    </div>
                </div>
            </div>

            <!-- Section 3: Penempatan & Periode Magang -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-4 sm:p-6 space-y-4">
                <div class="flex items-center gap-2.5 sm:gap-3 border-b border-slate-100 pb-3">
                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center text-xs sm:text-sm font-bold shrink-0">3</div>
                    <div>
                        <h2 class="text-xs sm:text-sm font-bold text-slate-800">Penempatan & Periode Magang</h2>
                        <p class="text-slate-400 text-[11px] leading-tight">Penetapan asal institusi, divisi kerja, shift jam kerja, dan rentang tanggal magang</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5 sm:gap-4">
                    <!-- Asal Sekolah / Kampus -->
                    <div>
                        <label for="school_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Asal Sekolah / Kampus <span class="text-rose-500">*</span>
                        </label>
                        <select id="school_id" name="school_id" required
                            class="w-full px-3.5 py-2.5 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition font-medium text-slate-800">
                            <option value="" disabled {{ old('school_id') ? '' : 'selected' }}>-- Pilih Asal Sekolah / Kampus --</option>
                            @foreach ($schools as $school)
                                <option value="{{ $school->id }}" {{ old('school_id') == $school->id ? 'selected' : '' }}>
                                    {{ $school->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Divisi -->
                    <div>
                        <label for="division_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Divisi Magang <span class="text-rose-500">*</span>
                        </label>
                        <select id="division_id" name="division_id" required
                            class="w-full px-3.5 py-2.5 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition font-medium text-slate-800">
                            <option value="" disabled {{ old('division_id') ? '' : 'selected' }}>-- Pilih Divisi --</option>
                            @foreach ($divisions as $division)
                                <option value="{{ $division->id }}" {{ old('division_id') == $division->id ? 'selected' : '' }}>
                                    {{ $division->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Shift Kerja -->
                    <div>
                        <label for="shift_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Shift Jam Kerja <span class="text-rose-500">*</span>
                        </label>
                        <select id="shift_id" name="shift_id" required
                            class="w-full px-3.5 py-2.5 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition font-medium text-slate-800">
                            <option value="" disabled {{ old('shift_id') ? '' : 'selected' }}>-- Pilih Shift Kerja --</option>
                            @foreach ($shifts as $shift)
                                <option value="{{ $shift->id }}" {{ old('shift_id') == $shift->id ? 'selected' : '' }}>
                                    {{ $shift->name }} ({{ substr($shift->start_time, 0, 5) }} - {{ substr($shift->end_time, 0, 5) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Brand / Unit Usaha -->
                    <div>
                        <label for="brand_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Brand / Unit Usaha (Opsional)
                        </label>
                        <select id="brand_id" name="brand_id"
                            class="w-full px-3.5 py-2.5 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition font-medium text-slate-800">
                            <option value="">-- Tanpa Brand / Default --</option>
                            @foreach ($brands as $brand)
                                <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>
                                    {{ $brand->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Tanggal Mulai Magang -->
                    <div>
                        <label for="start_date" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Tanggal Mulai Magang <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" id="start_date" name="start_date" required
                            value="{{ old('start_date', today()->toDateString()) }}"
                            class="w-full px-3.5 py-2.5 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition font-medium text-slate-800">
                    </div>

                    <!-- Tanggal Selesai Magang -->
                    <div>
                        <label for="end_date" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Tanggal Selesai Magang <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" id="end_date" name="end_date" required
                            value="{{ old('end_date', today()->addMonths(6)->toDateString()) }}"
                            class="w-full px-3.5 py-2.5 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition font-medium text-slate-800">
                    </div>
                </div>
            </div>

            <!-- Submit Action Footer -->
            <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2.5 sm:gap-3 pt-4 border-t border-slate-200">
                <a href="{{ route('admin.division') }}"
                    class="w-full sm:w-auto text-center px-5 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold rounded-xl text-xs sm:text-sm transition">
                    Batal
                </a>
                <button type="submit"
                    class="w-full sm:w-auto justify-center px-6 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold rounded-xl text-xs sm:text-sm transition shadow-md shadow-indigo-200 flex items-center gap-2">
                    <i class="fa-solid fa-user-plus"></i>
                    <span>Daftarkan Pemagang</span>
                </button>
            </div>
        </form>

    </div>
</main>

<script>
    function togglePass(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        if (!input || !icon) return;
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>
@endsection
