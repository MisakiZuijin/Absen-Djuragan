@extends('layouts.main')

@section('title', 'Update Shift Massal')

@section('contents')
    <main class="ml-0 md:ml-64 mt-16 md:mt-20 p-3 sm:p-6 bg-gray-50 min-h-screen min-w-0">

        <!-- Header Halaman -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-blue-600 rounded-lg">
                        <i class="fas fa-users-cog text-white text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 mb-1">Update Shift Massal</h1>
                        <p class="text-gray-600">Ubah jadwal shift untuk intern berdasarkan sekolah dengan mudah dan cepat.</p>
                    </div>
                </div>
                <a href="{{ route('admin.shift.index') }}"
                    class="flex items-center px-4 py-2 bg-white text-gray-700 font-medium rounded-lg shadow-sm hover:bg-gray-50 border border-gray-300 transition-colors">
                    <i class="fas fa-arrow-left mr-2 text-blue-600"></i> 
                    Kembali
                </a>
            </div>
        </div>

        {{-- Notifikasi --}}
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 p-4 mb-6 rounded-lg" role="alert">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-600 mr-3"></i>
                    <p class="font-medium">{{ session('success') }}</p>
                </div>
            </div>
        @endif
        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-800 p-4 mb-6 rounded-lg" role="alert">
                <div class="flex items-start">
                    <i class="fas fa-exclamation-circle text-red-600 mr-3 mt-1"></i>
                    <div>
                        <p class="font-medium mb-2">Terjadi kesalahan:</p>
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <!-- Progress Indicator -->
        <div class="mb-8">
            <div class="flex items-center justify-center space-x-8">
                <div class="flex items-center" id="step-indicator-1">
                    <div class="step-badge w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-semibold text-sm">1</div>
                    <span class="step-text ml-3 text-sm font-semibold text-blue-600">Pilih Sekolah</span>
                </div>
                <div class="step-line w-12 h-0.5 bg-gray-300" id="step-line-1"></div>
                <div class="flex items-center" id="step-indicator-2">
                    <div class="step-badge w-8 h-8 bg-gray-300 text-gray-500 rounded-full flex items-center justify-center font-semibold text-sm">2</div>
                    <span class="step-text ml-3 text-sm font-medium text-gray-500">Pilih Intern</span>
                </div>
                <div class="step-line w-12 h-0.5 bg-gray-300" id="step-line-2"></div>
                <div class="flex items-center" id="step-indicator-3">
                    <div class="step-badge w-8 h-8 bg-gray-300 text-gray-500 rounded-full flex items-center justify-center font-semibold text-sm">3</div>
                    <span class="step-text ml-3 text-sm font-medium text-gray-500">Tentukan Jadwal</span>
                </div>
            </div>
        </div>

        <!-- Form Update Massal -->
        <div class="bg-white rounded-lg shadow border border-gray-200">
            <form action="{{ route('admin.shifts.bulk-update') }}" method="POST" id="bulk-update-form">
                @csrf
                <div class="p-6 space-y-8">

                    <!-- Step 1: Pilih Sekolah -->
                    <div class="form-step active" data-step="1">
                        <div class="flex items-center mb-6">
                            <div class="w-10 h-10 bg-blue-600 text-white rounded-lg flex items-center justify-center font-semibold mr-4">
                                1
                            </div>
                            <div>
                                <h3 class="text-xl font-semibold text-gray-900">Pilih Sekolah</h3>
                                <p class="text-gray-600 mt-1">Pilih satu atau beberapa sekolah yang ingin diubah jadwal shiftnya</p>
                            </div>
                        </div>
                        
                        <div class="bg-blue-50 rounded-lg p-6 border border-blue-100">
                            <label for="school_ids" class="block text-sm font-semibold text-gray-800 mb-3">
                                <i class="fas fa-school mr-2 text-blue-600"></i>Daftar Sekolah
                            </label>
                            <select name="school_ids[]" id="school_ids" class="select-school w-full" multiple="multiple" required>
                                @foreach($schools as $school)
                                    @php
                                        $isRemote = (bool) ($school->is_remote_school ?? false);
                                        $count = $isRemote ? 0 : ($school->interns_count ?? 0);
                                    @endphp
                                    <option value="{{ $school->id }}" data-count="{{ $count }}" data-remote="{{ $isRemote ? '1' : '0' }}">
                                        {{ $school->name }} 
                                        @if($isRemote)
                                            (Remote WFH - Terkunci)
                                        @else
                                            ({{ $count }} pemagang aktif)
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            <div class="mt-3 p-3 bg-blue-100 rounded-lg">
                                <p class="text-sm text-blue-800">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    Anda dapat memilih lebih dari satu sekolah sekaligus untuk memudahkan pengelolaan.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Pilih Intern -->
                    <div class="form-step" data-step="2">
                        <div class="flex items-center mb-6">
                            <div class="w-10 h-10 bg-blue-600 text-white rounded-lg flex items-center justify-center font-semibold mr-4">
                                2
                            </div>
                            <div>
                                <h3 class="text-xl font-semibold text-gray-900">Pilih Intern</h3>
                                <p class="text-gray-600 mt-1">Tentukan intern mana saja yang akan diubah jadwal shiftnya</p>
                            </div>
                        </div>
                        
                        <div class="bg-blue-50 rounded-lg p-6 border border-blue-100">
                            <div class="flex items-center justify-between mb-3">
                                <span class="block text-sm font-semibold text-gray-800">
                                    <i class="fas fa-user-graduate mr-2 text-blue-600"></i>Daftar Intern
                                </span>
                                <span id="intern-loading" class="text-xs text-blue-600 font-medium hidden">
                                    <i class="fas fa-spinner fa-spin mr-1"></i>Memuat data pemagang...
                                </span>
                            </div>

                            <!-- Alert jika sekolah tidak memiliki intern aktif / non-remote -->
                            <div id="intern-empty-alert" class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-lg text-amber-800 text-sm hidden">
                                <i class="fas fa-exclamation-triangle mr-2 text-amber-600"></i>
                                <span>Tidak ada pemagang aktif (non-WFH penuh) yang ditemukan dari sekolah yang Anda pilih. Silakan pilih sekolah lain yang memiliki pemagang aktif.</span>
                            </div>
                            
                            <!-- Select All Checkbox -->
                            <div class="mb-4 p-3 bg-white rounded-lg border border-blue-200" id="select-all-container" style="display: none;">
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" id="select-all-interns" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                                    <span class="ml-3 text-sm font-medium text-gray-900">
                                        <i class="fas fa-check-double text-blue-600 mr-2"></i>
                                        Pilih Semua Intern dari Sekolah Terpilih
                                    </span>
                                    <span id="total-interns-count" class="ml-2 text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-full"></span>
                                </label>
                            </div>
                            
                            <select name="intern_ids[]" id="intern_ids" class="select-intern w-full" multiple="multiple" required disabled>
                                {{-- Opsi akan diisi oleh JavaScript --}}
                            </select>
                            <div class="mt-3 p-3 bg-blue-100 rounded-lg">
                                <p class="text-sm text-blue-800">
                                    <i class="fas fa-lightbulb mr-2"></i>
                                    Daftar intern akan muncul setelah Anda memilih sekolah. Gunakan fitur pencarian untuk memudahkan.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Pilih Shift & Tanggal -->
                    <div class="form-step" data-step="3">
                        <div class="flex items-center mb-6">
                            <div class="w-10 h-10 bg-blue-600 text-white rounded-lg flex items-center justify-center font-semibold mr-4">
                                3
                            </div>
                            <div>
                                <h3 class="text-xl font-semibold text-gray-900">Tentukan Jadwal Baru</h3>
                                <p class="text-gray-600 mt-1">Pilih shift dan rentang tanggal untuk perubahan jadwal</p>
                            </div>
                        </div>
                        
                        <div class="bg-blue-50 rounded-lg p-6 border border-blue-100">
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                <div class="lg:col-span-1">
                                    <label for="shift_id" class="block text-sm font-semibold text-gray-800 mb-3">
                                        <i class="fas fa-clock mr-2 text-blue-600"></i>Shift Baru
                                        <span class="text-red-500 ml-1">*</span>
                                    </label>
                                    <select name="shift_id" id="shift_id" class="select-shift w-full" required>
                                        <option value="" disabled selected>-- Pilih Shift --</option>
                                        @foreach($shifts as $shift)
                                            <option value="{{ $shift->id }}"
                                                data-start="{{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }}"
                                                data-end="{{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }}">
                                                {{ $shift->name }} ({{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }} -
                                                {{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="lg:col-span-1">
                                    <label for="start_date" class="block text-sm font-semibold text-gray-800 mb-3">
                                        <i class="fas fa-calendar-alt mr-2 text-blue-600"></i>Dari Tanggal
                                        <span class="text-red-500 ml-1">*</span>
                                    </label>
                                    <input type="date" name="start_date" id="start_date"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                        required value="{{ today()->toDateString() }}">
                                </div>
                                <div class="lg:col-span-1">
                                    <label for="end_date" class="block text-sm font-semibold text-gray-800 mb-3">
                                        <i class="fas fa-calendar-check mr-2 text-blue-600"></i>Sampai Tanggal
                                        <span class="text-red-500 ml-1">*</span>
                                    </label>
                                    <input type="date" name="end_date" id="end_date"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                        required value="{{ today()->toDateString() }}">
                                </div>
                            </div>
                            <div class="mt-4 p-3 bg-blue-100 rounded-lg">
                                <p class="text-sm text-blue-800">
                                    <i class="fas fa-calendar-week mr-2"></i>
                                    Perubahan akan diterapkan untuk semua hari dalam rentang tanggal yang dipilih.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Form -->
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-shield-alt mr-2 text-green-600"></i>
                        Perubahan akan disimpan secara otomatis
                    </div>
                    <button type="submit"
                        class="w-full sm:w-auto px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-save mr-2"></i> 
                        Terapkan Perubahan
                    </button>
                </div>
            </form>
        </div>

        <!-- Summary Card -->
        <div class="mt-6 bg-white rounded-lg shadow border border-gray-200 p-6" id="summary-card" style="display: none;">
            <h4 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-clipboard-list mr-2 text-blue-600"></i>Ringkasan Perubahan
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div class="bg-blue-50 p-4 rounded-lg border border-blue-100">
                    <div class="text-blue-700 font-semibold">Sekolah Terpilih</div>
                    <div id="selected-schools" class="text-gray-800 mt-1">-</div>
                </div>
                <div class="bg-blue-50 p-4 rounded-lg border border-blue-100">
                    <div class="text-blue-700 font-semibold">Intern Terpilih</div>
                    <div id="selected-interns" class="text-gray-800 mt-1">-</div>
                </div>
                <div class="bg-blue-50 p-4 rounded-lg border border-blue-100">
                    <div class="text-blue-700 font-semibold">Periode</div>
                    <div id="selected-period" class="text-gray-800 mt-1">-</div>
                </div>
            </div>
        </div>

    </main>

    {{-- CSS & JS untuk Select2 --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <style>
        /* Clean Select2 Styling */
        .select2-container--default .select2-selection--single,
        .select2-container--default .select2-selection--multiple {
            border: 2px solid #d1d5db;
            border-radius: 0.5rem;
            padding: 0.75rem;
            min-height: 48px;
            background: white;
            transition: all 0.2s ease;
        }

        .select2-container--default .select2-selection--multiple {
            padding: 0.5rem;
        }

        .select2-container--default.select2-container--focus .select2-selection--single,
        .select2-container--default.select2-container--focus .select2-selection--multiple {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 100%;
            right: 12px;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            padding-left: 0;
            color: #374151;
            font-weight: 500;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background: #3b82f6;
            border: none;
            border-radius: 0.375rem;
            color: white;
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
            font-weight: 500;
            margin: 0.25rem;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: rgba(255, 255, 255, 0.8);
            margin-right: 0.5rem;
            border-right: 1px solid rgba(255, 255, 255, 0.3);
            padding-right: 0.5rem;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
            color: white;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .select2-dropdown {
            border: 2px solid #d1d5db;
            border-radius: 0.5rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            padding: 0.5rem;
            margin-top: 0.25rem;
            z-index: 1001;
        }

        .select2-container--default .select2-search--dropdown {
            padding: 0.5rem;
        }

        .select2-container--default .select2-search--dropdown .select2-search__field {
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            padding: 0.5rem 0.75rem;
            margin-bottom: 0.5rem;
        }

        .select2-container--default .select2-results__option {
            padding: 0.75rem;
            border-radius: 0.375rem;
            margin-bottom: 0.125rem;
            transition: all 0.15s;
            color: #374151;
            font-weight: 500;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background: #3b82f6;
            color: white !important;
        }

        .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: #dbeafe;
            color: #1d4ed8;
            font-weight: 600;
        }

        /* Form Step Styling */
        .form-step {
            background: white;
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid #e5e7eb;
            transition: all 0.2s ease;
        }

        .form-step:hover {
            border-color: #d1d5db;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .form-step.active {
            border-color: #3b82f6;
            box-shadow: 0 0 0 1px rgba(59, 130, 246, 0.1);
        }

        /* Shift option styling */
        .shift-option {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }

        .shift-name {
            font-weight: 600;
            color: #374151;
        }

        .shift-time {
            font-size: 0.875rem;
            color: #6b7280;
            background: #f9fafb;
            padding: 0.25rem 0.75rem;
            border-radius: 0.375rem;
            font-weight: 500;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] .shift-time {
            background: rgba(255, 255, 255, 0.2);
            color: white !important;
        }

        /* Select All Container Styling */
        #select-all-container {
            transition: all 0.3s ease;
            transform: translateY(-5px);
            opacity: 0;
        }

        #select-all-container.show {
            transform: translateY(0);
            opacity: 1;
        }

        /* Optgroup styling for grouped interns */
        .select2-container--default .select2-results__group {
            padding: 8px 12px;
            font-weight: 700;
            color: #1e40af;
            background-color: #f1f5f9;
            border-radius: 4px;
            margin-top: 6px;
            font-size: 0.85rem;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .form-step {
                padding: 1rem;
            }
            
            .select2-container--default .select2-selection--single,
            .select2-container--default .select2-selection--multiple {
                min-height: 44px;
            }
        }
    </style>

    <script>
        $(document).ready(function () {
            let currentStep = 1;
            let totalInternsCount = 0;
            let fetchAbortController = null;

            // Update progress indicator
            function setProgressStep(step) {
                currentStep = step;
                for (let i = 1; i <= 3; i++) {
                    const badge = $(`#step-indicator-${i} .step-badge`);
                    const text = $(`#step-indicator-${i} .step-text`);
                    const line = $(`#step-line-${i}`);

                    if (i < step) {
                        badge.removeClass('bg-gray-300 bg-blue-600 text-gray-500').addClass('bg-green-600 text-white').html('<i class="fas fa-check text-xs"></i>');
                        text.removeClass('text-gray-500 text-blue-600 font-semibold').addClass('text-gray-900 font-medium');
                        if (line.length) line.removeClass('bg-gray-300').addClass('bg-green-600');
                    } else if (i === step) {
                        badge.removeClass('bg-gray-300 bg-green-600 text-gray-500').addClass('bg-blue-600 text-white').text(i);
                        text.removeClass('text-gray-500 text-gray-900').addClass('text-blue-600 font-semibold');
                        if (line.length) line.removeClass('bg-green-600').addClass('bg-gray-300');
                    } else {
                        badge.removeClass('bg-blue-600 bg-green-600 text-white').addClass('bg-gray-300 text-gray-500').text(i);
                        text.removeClass('text-blue-600 font-semibold text-gray-900').addClass('text-gray-500 font-medium');
                        if (line.length) line.removeClass('bg-green-600').addClass('bg-gray-300');
                    }
                }
            }

            // Update summary card
            function updateSummary() {
                const schoolIds = $('#school_ids').val();
                const internIds = $('#intern_ids').val();
                const startDate = $('#start_date').val();
                const endDate = $('#end_date').val();
                const shiftId = $('#shift_id').val();

                let hasData = false;

                if (schoolIds && schoolIds.length > 0) {
                    const schoolTexts = [];
                    $('#school_ids option:selected').each(function() {
                        schoolTexts.push($(this).text().trim());
                    });
                    $('#selected-schools').text(`${schoolTexts.length} sekolah dipilih`);
                    hasData = true;
                }

                if (internIds && internIds.length > 0) {
                    $('#selected-interns').text(`${internIds.length} intern dipilih`);
                    hasData = true;
                }

                if (startDate && endDate) {
                    $('#selected-period').text(`${startDate} s/d ${endDate}`);
                    hasData = true;
                }

                if (hasData) {
                    $('#summary-card').slideDown();
                } else {
                    $('#summary-card').slideUp();
                }
            }

            // Custom template functions for shift
            function formatShift(shift) {
                if (!shift.id) return shift.text;

                if ($(shift.element).data('start') && $(shift.element).data('end')) {
                    const startTime = $(shift.element).data('start');
                    const endTime = $(shift.element).data('end');
                    const shiftName = shift.text.split(' (')[0];

                    return $(
                        `<div class="shift-option">
                            <span class="shift-name">${shiftName}</span>
                            <span class="shift-time">${startTime} - ${endTime}</span>
                        </div>`
                    );
                }

                return shift.text;
            }

            function formatShiftSelection(shift) {
                return formatShift(shift);
            }

            // Clean tag representation for schools (remove the count suffix in chips)
            function formatSchoolSelection(school) {
                if (!school.id) return school.text;
                return school.text.replace(/\s*\([^)]*pemagang aktif\)/i, '').replace(/\s*\(Remote WFH - Terkunci\)/i, '').trim();
            }

            // Initialize Select2 School
            $('#school_ids').select2({
                placeholder: "🏫 Cari dan pilih sekolah (dapat memilih lebih dari satu)",
                width: '100%',
                closeOnSelect: false,
                allowClear: true,
                templateSelection: formatSchoolSelection
            }).on('change', function () {
                updateSummary();
                handleSchoolsChange();
            });

            // Initialize Select2 Intern (initial disabled state)
            const internSelect = $('#intern_ids').select2({
                placeholder: "👨‍🎓 Pilih sekolah terlebih dahulu...",
                width: '100%',
                closeOnSelect: false,
                allowClear: true
            }).on('change', function () {
                updateSummary();
                updateSelectAllCheckbox();
                const internIds = $(this).val();
                if (internIds && internIds.length > 0) {
                    setProgressStep(3);
                    $('.form-step[data-step="3"]').addClass('active');
                }
            });

            // Initialize Select2 Shift
            $('#shift_id').select2({
                templateResult: formatShift,
                templateSelection: formatShiftSelection,
                width: '100%',
                placeholder: "🕐 Pilih shift baru",
                allowClear: true
            }).on('change', updateSummary);

            // Handler ketika pilihan sekolah berubah
            function handleSchoolsChange() {
                const schoolIds = $('#school_ids').val();

                // Batalkan request sebelumnya jika user memilih/mengubah sekolah dengan cepat
                if (fetchAbortController) {
                    fetchAbortController.abort();
                }

                // Reset state intern
                $('#select-all-container').hide().removeClass('show');
                $('#select-all-interns').prop('checked', false).prop('indeterminate', false);
                $('#intern-empty-alert').addClass('hidden').hide();

                if (!schoolIds || schoolIds.length === 0) {
                    setProgressStep(1);
                    $('.form-step').removeClass('active');
                    $('.form-step[data-step="1"]').addClass('active');

                    // Bersihkan dan nonaktifkan select2 intern
                    if (internSelect.data('select2')) {
                        internSelect.select2('destroy');
                    }
                    internSelect.empty().prop('disabled', true);
                    internSelect.select2({
                        placeholder: "👨‍🎓 Pilih sekolah terlebih dahulu...",
                        width: '100%',
                        closeOnSelect: false,
                        allowClear: true
                    }).trigger('change');
                    return;
                }

                setProgressStep(2);
                $('.form-step[data-step="2"]').addClass('active');
                $('#intern-loading').removeClass('hidden').show();

                fetchAbortController = new AbortController();

                fetch("{{ route('admin.api.interns-by-schools') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ school_ids: schoolIds }),
                    signal: fetchAbortController.signal
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Gagal mengambil data dari server (status: ' + response.status + ')');
                    }
                    return response.json();
                })
                .then(data => {
                    $('#intern-loading').addClass('hidden').hide();

                    if (internSelect.data('select2')) {
                        internSelect.select2('destroy');
                    }
                    internSelect.empty();

                    totalInternsCount = data ? data.length : 0;

                    if (data && data.length > 0) {
                        internSelect.prop('disabled', false);

                        // Kelompokkan intern per sekolah jika memilih lebih dari 1 sekolah
                        const schoolGroups = {};
                        data.forEach(intern => {
                            const sName = intern.school_name || 'Sekolah Terpilih';
                            if (!schoolGroups[sName]) {
                                schoolGroups[sName] = [];
                            }
                            schoolGroups[sName].push(intern);
                        });

                        const schoolNames = Object.keys(schoolGroups);
                        if (schoolNames.length > 1) {
                            schoolNames.forEach(schoolName => {
                                const groupInterns = schoolGroups[schoolName];
                                const optgroup = $('<optgroup>').attr('label', `${schoolName} (${groupInterns.length} pemagang)`);
                                groupInterns.forEach(intern => {
                                    optgroup.append(new Option(intern.text, intern.id, false, false));
                                });
                                internSelect.append(optgroup);
                            });
                        } else {
                            data.forEach(intern => {
                                internSelect.append(new Option(intern.text, intern.id, false, false));
                            });
                        }

                        // Tampilkan tombol Pilih Semua
                        $('#total-interns-count').text(`(${data.length} intern)`);
                        $('#select-all-container').show().addClass('show');

                        internSelect.select2({
                            placeholder: "👨‍🎓 Cari dan pilih nama intern...",
                            width: '100%',
                            closeOnSelect: false,
                            allowClear: true
                        });
                    } else {
                        internSelect.prop('disabled', true);
                        internSelect.select2({
                            placeholder: "⚠️ Tidak ada pemagang aktif",
                            width: '100%',
                            closeOnSelect: false,
                            allowClear: true
                        });
                        $('#intern-empty-alert').removeClass('hidden').show();
                    }

                    internSelect.trigger('change');
                })
                .catch(error => {
                    if (error.name === 'AbortError') return;
                    console.error('Error fetching interns:', error);
                    $('#intern-loading').addClass('hidden').hide();

                    if (internSelect.data('select2')) {
                        internSelect.select2('destroy');
                    }
                    internSelect.empty().prop('disabled', true).select2({
                        placeholder: "❌ Gagal memuat data intern",
                        width: '100%',
                        closeOnSelect: false
                    });
                    $('#select-all-container').hide().removeClass('show');
                });
            }

            // Select All Checkbox functionality
            $('#select-all-interns').on('change', function() {
                const isChecked = $(this).is(':checked');
                
                if (isChecked) {
                    const allValues = [];
                    $('#intern_ids option').each(function() {
                        const val = $(this).val();
                        if (val) {
                            allValues.push(val);
                        }
                    });
                    internSelect.val(allValues).trigger('change');
                } else {
                    internSelect.val([]).trigger('change');
                }
            });

            // Update select all checkbox based on intern selection
            function updateSelectAllCheckbox() {
                const totalOptions = $('#intern_ids option').length;
                const selectedOptions = $('#intern_ids option:selected').length;
                
                if (totalOptions > 0) {
                    if (selectedOptions === totalOptions) {
                        $('#select-all-interns').prop('checked', true);
                        $('#select-all-interns').prop('indeterminate', false);
                    } else if (selectedOptions > 0) {
                        $('#select-all-interns').prop('checked', false);
                        $('#select-all-interns').prop('indeterminate', true);
                    } else {
                        $('#select-all-interns').prop('checked', false);
                        $('#select-all-interns').prop('indeterminate', false);
                    }
                } else {
                    $('#select-all-interns').prop('checked', false);
                    $('#select-all-interns').prop('indeterminate', false);
                }
            }

            // Date change handlers
            $('#start_date, #end_date, #shift_id').on('change', updateSummary);

            // Form validation & submission
            $('#bulk-update-form').on('submit', function (e) {
                const startDate = $('#start_date').val();
                const endDate = $('#end_date').val();
                const internIds = $('#intern_ids').val();
                const schoolIds = $('#school_ids').val();
                const shiftId = $('#shift_id').val();

                if (!schoolIds || schoolIds.length === 0) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Sekolah Belum Dipilih',
                        text: 'Harap pilih minimal satu sekolah terlebih dahulu.',
                        confirmButtonColor: '#3b82f6'
                    });
                    return false;
                }

                if (!internIds || internIds.length === 0) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Intern Belum Dipilih',
                        text: 'Harap pilih minimal satu intern dari sekolah yang dipilih.',
                        confirmButtonColor: '#3b82f6'
                    });
                    return false;
                }

                if (!shiftId) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Shift Baru Belum Dipilih',
                        text: 'Harap pilih shift baru terlebih dahulu.',
                        confirmButtonColor: '#3b82f6'
                    });
                    return false;
                }

                if (!startDate || !endDate) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Rentang Tanggal Belum Lengkap',
                        text: 'Harap tentukan tanggal mulai dan tanggal selesai.',
                        confirmButtonColor: '#3b82f6'
                    });
                    return false;
                }

                if (startDate > endDate) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Tanggal Tidak Valid',
                        text: 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
                        confirmButtonColor: '#3b82f6'
                    });
                    return false;
                }

                // Show confirmation dialog
                e.preventDefault();
                
                const schoolCount = schoolIds.length;
                const internCount = internIds.length;
                const shiftName = $('#shift_id option:selected').text();
                
                Swal.fire({
                    title: 'Konfirmasi Perubahan Shift',
                    html: `
                        <div class="text-left text-sm">
                            <p class="mb-3 text-gray-700">Anda akan mengubah jadwal shift dengan detail berikut:</p>
                            <div class="bg-gray-50 p-4 rounded-lg space-y-2 border border-gray-200">
                                <div><strong>🏫 Sekolah:</strong> ${schoolCount} sekolah dipilih</div>
                                <div><strong>👨‍🎓 Intern:</strong> ${internCount} intern dipilih</div>
                                <div><strong>🕐 Shift Baru:</strong> ${shiftName}</div>
                                <div><strong>📅 Periode:</strong> ${startDate} s/d ${endDate}</div>
                            </div>
                            <p class="mt-3 text-xs text-amber-700">⚠️ Perubahan akan diterapkan untuk semua hari aktif dalam rentang tanggal yang dipilih.</p>
                        </div>
                    `,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3b82f6',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: '<i class="fas fa-check mr-2"></i>Ya, Terapkan Perubahan',
                    cancelButtonText: '<i class="fas fa-times mr-2"></i>Batal',
                    reverseButtons: true,
                    customClass: {
                        popup: 'rounded-lg',
                        confirmButton: 'rounded-lg px-4 py-2 font-semibold',
                        cancelButton: 'rounded-lg px-4 py-2 font-semibold'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Menyimpan Perubahan...',
                            html: 'Mohon tunggu sebentar.',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            allowEnterKey: false,
                            showConfirmButton: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        
                        e.target.submit();
                    }
                });
            });

            // Set initial step
            setProgressStep(1);
        });
    </script>

    {{-- SweetAlert2 CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@endsection