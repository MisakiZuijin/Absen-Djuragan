@extends('layouts.main')

@section('title', 'Pengaturan Shift')

@section('contents')
    @include('layouts.sidebar')

    @include('layouts.sidebar-pengaturan')

    @include('layouts.navbar')

    <!-- Main Content -->
    <main class="ml-[32rem] mt-24 p-6">

        <!-- Header -->
        <h1 class="text-2xl font-bold mb-2">Shift</h1>
        <p class="mb-6 text-gray-600">Menentukan pengaturan shift</p>

        <div class="flex items-center mb-6">
            <button id="openAddShiftModal"
                class="flex items-center px-4 py-2 bg-gray-800 text-white rounded-lg shadow-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500">
                <i class="fas fa-plus mr-2"></i> Tambahkan Shift
            </button>

            <!-- Search Input -->
            <div class="flex items-center ml-auto">
                <input type="text" id="searchInput" placeholder="Cari Shift"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500">
            </div>
        </div>

        @if (session('success'))
            <div id="success-message"
                class="mb-2 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative transition-opacity duration-500"
                role="alert">
                <strong class="font-bold">Success!</strong>
                <span class="block sm:inline">{{ session('success') }}</span>
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer" onclick="removeMessage()">
                    <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 20 20">
                        <title>Close</title>
                        <path
                            d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z" />
                    </svg>
                </span>
            </div>
        @endif

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white shadow-md rounded-lg overflow-hidden">
                <thead>
                    <tr class="bg-gray-200 text-gray-700">
                        <th class="py-3 px-6 text-left">No</th>
                        <th class="py-3 px-6 text-left">Nama Shift</th>
                        <th class="py-3 px-6 text-left">Jam Mulai</th>
                        <th class="py-3 px-6 text-left">Jam Berakhir</th>
                        <th class="py-3 px-6 text-left">Durasi Istirahat</th>
                        <th class="py-3 px-6 text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody id="shift-tbody">
                </tbody>
            </table>
        </div>

        <div class="mt-4 flex justify-center items-center space-x-2 border rounded-md p-2">
            <button id="prev-page"
                class="cursor-pointer bg-white text-blue-600 px-4 py-2 rounded-md border hover:bg-gray-100" disabled>
                Previous
            </button>
            <div id="page-numbers" class="flex space-x-2"></div>
            <button id="next-page" class="bg-white text-blue-600 px-4 py-2 rounded-md border hover:bg-gray-100">
                Next
            </button>
        </div>
    </main>

    <!-- Modal Tambahkan -->
    <div id="showAddShiftModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg p-6 w-1/3">
            <h2 class="text-xl font-bold mb-4">Tambahkan Shift</h2>
            <form id="addShiftForm" action="{{ route('shifts.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="addNamaShift" class="block text-gray-700">Nama Shift<span
                            class="text-red-500">*</span></label>
                    <input type="text" id="addNamaShift" name="addNamaShift"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500"
                        required>
                </div>
                <div class="mb-4">
                    <label for="addJamMulai" class="block text-gray-700">Jam Mulai<span
                            class="text-red-500">*</span></label>
                    <input type="time" id="addJamMulai" name="addJamMulai"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500"
                        required>
                </div>
                <div class="mb-4">
                    <label for="addJamBerakhir" class="block text-gray-700">Jam Berakhir<span
                            class="text-red-500">*</span></label>
                    <input type="time" id="addJamBerakhir" name="addJamBerakhir"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500"
                        required>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label for="start_break_time" class="block text-gray-700">Jam Mulai Istirahat<span
                                class="text-red-500">*</span></label>
                        <input type="time" id="start_break_time" name="start_break_time"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500"
                            required>
                    </div>

                    <div class="mb-4">
                        <label for="end_break_time" class="block text-gray-700">Jam Selesai Istirahat<span
                                class="text-red-500">*</span></label>
                        <input type="time" id="end_break_time" name="end_break_time"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500"
                            required>
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="button" id="closeAddShiftModal"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg mr-2">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Shift -->
    <div id="editShiftModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg p-6 w-1/3">
            <h2 class="text-xl font-bold mb-4">Update Shift</h2>
            <form id="topupForm" action="" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" id="shiftId" name="shiftId">
                <div class="mb-4">
                    <label for="nama_Shift" class="block text-gray-700">Nama Shift<span
                            class="text-red-500">*</span></label>
                    <input type="text" id="nama_Shift" name="nama_Shift"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500"
                        required>
                </div>
                <div class="mb-4">
                    <label for="jamMulai" class="block text-gray-700">Jam Mulai<span
                            class="text-red-500">*</span></label>
                    <input type="time" id="jamMulai" name="jamMulai"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500"
                        required>
                </div>
                <div class="mb-4">
                    <label for="jamBerakhir" class="block text-gray-700">Jam Berakhir<span
                            class="text-red-500">*</span></label>
                    <input type="time" id="jamBerakhir" name="jamBerakhir"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500"
                        required>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label for="start_break_time" class="block text-gray-700">Jam Mulai Istirahat<span
                                class="text-red-500">*</span></label>
                        <input type="time" id="edit_start_break_time" name="edit_start_break_time"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500"
                            required>
                    </div>

                    <div class="mb-4">
                        <label for="end_break_time" class="block text-gray-700">Jam Selesai Istirahat<span
                                class="text-red-500">*</span></label>
                        <input type="time" id="edit_end_break_time" name="edit_end_break_time"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500"
                            required>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="button" id="closeEditModal"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg mr-2">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Hapus Shift -->
    <div id="deleteShiftModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg p-6 w-1/3">
            <h2 class="text-xl font-bold mb-4">Hapus Shift</h2>
            <p>Apakah Anda yakin ingin menghapus shift ini?</p>
            <form id="deleteForm" action="" method="POST">
                @csrf
                @method('DELETE')
                <input type="hidden" id="deleteShiftId" name="shiftId">
                <div class="flex justify-end mt-4">
                    <button type="button" id="closeDeleteModal"
                        class="px-4 py-2 bg-gray-600 text-white rounded-lg mr-2">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg">Hapus</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const shifts = @json($shift);
            const perPage = 5;
            let currentPage = 1;
            const shiftTbody = document.querySelector('#shift-tbody');
            const prevPageButton = document.querySelector('#prev-page');
            const nextPageButton = document.querySelector('#next-page');
            const pageNumbersContainer = document.querySelector('#page-numbers');
            const searchInput = document.querySelector('#searchInput'); // Select the search input
            let totalPages = Math.ceil(shifts.length / perPage);
            let filteredShifts = shifts; // Variable to store filtered shifts

            const renderTable = () => {
                shiftTbody.innerHTML = '';
                const start = (currentPage - 1) * perPage;
                const end = start + perPage;
                const currentData = filteredShifts.slice(start, end); // Use filtered shifts

                currentData.forEach((shift, index) => {
                    const row = document.createElement('tr');
                    row.className = 'border-b border-gray-200';
                    row.innerHTML = `
                        <td class="py-4 px-6">${start + index + 1}</td>
                        <td class="py-4 px-6">${shift.name}</td>
                        <td class="py-4 px-6">${shift.start_time}</td>
                        <td class="py-4 px-6">${shift.end_time}</td>
                        <td class="py-4 px-6">${shift.break_time_in_minute} menit</td>
                        <td class="py-4 px-6">
                            <button class="editShiftModal px-4 py-2 bg-blue-500 text-white rounded-lg shadow-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500" data-id="${shift.id}" data-nama="${shift.name}" data-mulai="${shift.start_time}" data-berakhir="${shift.end_time}" data-start-break="${shift.start_break_time}" data-end-break="${shift.end_break_time}" data-adt-start-break="${shift.adt_start_break_time}" data-adt-end-break="${shift.adt_end_break_time}">Edit</button>
                            <button class="deleteShiftModal px-4 py-2 bg-red-500 text-white rounded-lg shadow-md hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500" data-id="${shift.id}">Hapus</button>
                        </td>
                    `;
                    shiftTbody.appendChild(row);
                });

                addEventListenersToButtons();
                updatePaginationButtons();
                renderPageNumbers();
            };

            const filterShifts = () => {
                const searchTerm = searchInput.value.toLowerCase();
                filteredShifts = shifts.filter(shift => shift.name.toLowerCase().includes(searchTerm));
                totalPages = Math.ceil(filteredShifts.length / perPage);
                currentPage = 1;
                renderTable();
            };

            searchInput.addEventListener('input', filterShifts);

            const updatePaginationButtons = () => {
                prevPageButton.disabled = currentPage <= 1;
                nextPageButton.disabled = currentPage >= totalPages;
            };

            const renderPageNumbers = () => {
                pageNumbersContainer.innerHTML = '';
                for (let i = 1; i <= totalPages; i++) {
                    const pageButton = document.createElement('button');
                    pageButton.textContent = i;
                    pageButton.className = 'cursor-pointer px-4 py-2 rounded-md border';

                    if (i === currentPage) {
                        pageButton.classList.add('bg-blue-600', 'text-white');
                    } else {
                        pageButton.classList.add('text-blue-600', 'bg-white', 'hover:bg-gray-100');
                    }

                    pageButton.addEventListener('click', () => {
                        if (i !== currentPage) {
                            currentPage = i;
                            renderTable();
                        }
                    });

                    pageNumbersContainer.appendChild(pageButton);
                }
            };

            const addEventListenersToButtons = () => {
                document.querySelectorAll('.editShiftModal').forEach(button => {
                    button.addEventListener('click', function() {
                        const shiftId = this.getAttribute('data-id');
                        const shiftName = this.getAttribute('data-nama');
                        const startTime = this.getAttribute('data-mulai');
                        const endTime = this.getAttribute('data-berakhir');
                        const startBreak = this.getAttribute('data-start-break');
                        const endBreak = this.getAttribute('data-end-break');
                        const adtstartBreak = this.getAttribute('data-adt-start-break');
                        const adtendBreak = this.getAttribute('data-adt-end-break');

                        // Fungsi untuk mengonversi format waktu 12 jam ke 24 jam
                        const convertTo24HourFormat = (time) => {
                            const [hours, minutes, seconds] = time.split(':');
                            const period = time.slice(-2).toLowerCase();
                            let hours24 = parseInt(hours);
                            if (period === 'pm' && hours24 !== 12) {
                                hours24 += 12; // Jika PM dan bukan jam 12, tambahkan 12
                            } else if (period === 'am' && hours24 === 12) {
                                hours24 = 0; // Jika AM dan jam 12, set menjadi 00
                            }
                            return `${hours24.toString().padStart(2, '0')}:${minutes}:${seconds}`;
                        };

                        // Mengonversi waktu yang diterima ke format 24 jam
                        const startTime24 = convertTo24HourFormat(startTime);
                        const endTime24 = convertTo24HourFormat(endTime);
                        const startBreak24 = convertTo24HourFormat(startBreak);
                        const endBreak24 = convertTo24HourFormat(endBreak);
                        const adtstartBreak24 = convertTo24HourFormat(adtstartBreak);
                        const adtendBreak24 = convertTo24HourFormat(adtendBreak);

                        // Menetapkan nilai ke form inputs
                        $('#shiftId').val(shiftId);
                        $('#nama_Shift').val(shiftName);
                        $('#jamMulai').val(startTime24);
                        $('#jamBerakhir').val(endTime24);
                        $('#edit_start_break_time').val(startBreak24);
                        $('#edit_end_break_time').val(endBreak24);
                        $('#edit_adt_start_break_time').val(adtstartBreak24);
                        $('#edit_adt_end_break_time').val(adtendBreak24);

                        // Memperbarui action URL form untuk update
                        var actionUrl = '{{ route('shifts.update', ':id') }}';
                        actionUrl = actionUrl.replace(':id', shiftId);
                        $('#topupForm').attr('action', actionUrl);

                        $('#editShiftModal').removeClass('hidden');
                    });
                });

                // Event listener untuk deleteShiftModal
                document.querySelectorAll('.deleteShiftModal').forEach(button => {
                    button.addEventListener('click', function() {
                        const shiftId = this.getAttribute('data-id');

                        var actionUrl = '{{ route('shifts.delete', ':id') }}';
                        actionUrl = actionUrl.replace(':id', shiftId);
                        $('#deleteForm').attr('action', actionUrl);
                        $('#deleteForm').find('input[name="shiftId"]').val(shiftId);

                        $('#deleteShiftModal').removeClass('hidden');
                    });
                });
            };


            prevPageButton.addEventListener('click', () => {
                if (currentPage > 1) {
                    currentPage--;
                    renderTable();
                }
            });

            nextPageButton.addEventListener('click', () => {
                if (currentPage < totalPages) {
                    currentPage++;
                    renderTable();
                }
            });

            renderTable();

            $(document).ready(function() {
                $('#openAddShiftModal').on('click', function() {
                    $('#showAddShiftModal').removeClass('hidden');
                });

                $('#closeAddShiftModal, #closeEditModal, #closeDeleteModal').on('click', function() {
                    $(this).closest('.modal').addClass('hidden');
                });

                $('#closeAddShiftModal').on('click', function() {
                    $('#showAddShiftModal').addClass('hidden');
                });

                $(window).on('click', function(event) {
                    if ($(event.target).closest('.modal').length === 0) {
                        $('.modal').addClass('hidden');
                    }
                });
            });
        });

        $(window).on('click', function(event) {
            if ($(event.target).is('#showAddShiftModal')) {
                $('#showAddShiftModal').addClass('hidden');
            }
            if ($(event.target).is('#editShiftModal')) {
                $('#editShiftModal').addClass('hidden');
            }
            if ($(event.target).is('#deleteShiftModal')) {
                $('#deleteShiftModal').addClass('hidden');
            }
        });

        $('#closeEditModal').on('click', function() {
            $('#editShiftModal').addClass('hidden');
        });

        $('#closeDeleteModal').on('click', function() {
            $('#deleteShiftModal').addClass('hidden');
        });

        // Display success message temporarily
        document.addEventListener('DOMContentLoaded', function() {
            const message = document.getElementById('success-message');
            if (message) {
                setTimeout(() => {
                    message.style.opacity = 0;
                    setTimeout(() => message.remove(), 600);
                }, 3000);
            }
        });
    </script>
@endsection
