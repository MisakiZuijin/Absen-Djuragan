@extends('layouts.main')

@section('title', 'Anggota Sekolah')

@section('contents')
    <!-- Main Content -->
    <main class="ml-0 md:ml-64 mt-16 md:mt-20 p-3 sm:p-6 min-w-0">
        <!-- Header -->
        <h1 class="text-2xl font-bold mb-2">Pengaturan Jadwal</h1>
        <p class="mb-6 text-gray-600">Menentukan jadwal shift dari setiap pengguna</p>

        <!-- Search Field -->
        <div class="mb-6">
            <div class="flex items-center border border-gray-300 rounded-lg">
                <div class="bg-white p-3 rounded-l-lg">
                    <i class="ml-2 fa-solid fa-search text-gray-800"></i>
                </div>

                <input type="text" id="searchInput"
                    class="w-full px-4 py-3 pl-3 rounded-r-lg text-gray-800 focus:outline-none" placeholder="Cari Nama">
            </div>
        </div>

        @if (session('success'))
            <div id="success-message"
                class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative transition-opacity duration-500"
                role="alert">
                <strong class="font-bold">Success!</strong>
                <span class="block sm:inline">{{ session('success') }}</span>
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer"
                    onclick="removeMessage('success-message')">
                    <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 20 20">
                        <title>Close</title>
                        <path
                            d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z" />
                    </svg>
                </span>
            </div>
        @endif

        @if (session('error'))
            <div id="error-message"
                class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative transition-opacity duration-500"
                role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="block sm:inline">{{ session('error') }}</span>
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer"
                    onclick="removeMessage('error-message')">
                    <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 20 20">
                        <title>Close</title>
                        <path
                            d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z" />
                    </svg>
                </span>
            </div>
        @endif

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white shadow-md rounded-lg overflow-hidden">
                <thead>
                    <tr class="bg-gray-200 text-gray-700">
                        <th class="py-3 px-6 text-left">No</th>
                        <th class="py-3 px-6 text-left">Nama</th>
                        <th class="py-3 px-6 text-left">Divisi</th>
                        <th class="py-3 px-6 text-left">Kantor</th>
                        <th class="py-3 px-6 text-left">Sistem Shift</th>
                        <th class="py-3 px-6 text-left">Aksi</th>
                        <th class="py-3 px-6 text-left">Edit</th>

                    </tr>
                </thead>

                @if ($teamData->isEmpty())
                    <tr>
                        <td colspan="7" class="py-4 px-6 text-center text-gray-500">Not Available</td>
                    </tr>
                @else
                    @foreach ($teamData as $key => $data)
                        <tbody id="teamTableBody">
                            <tr class="border-b border-gray-200">
                                <td class="py-4 px-6">{{ $key + 1 }}</td>
                                <td class="py-4 px-6">{{ $data->user->profile->full_name }}</td>
                                <td class="py-4 px-6">{{ $data->division->name ?? 'Not Available' }}</td>
                                <td class="py-4 px-6">
                                    {{ $data->schedules->pluck('office.name')->implode(', ') ?? 'Not Available' }}</td>
                                <td class="py-4 px-6">
                                    {{ $data->schedules->pluck('type')->implode(', ') ?? 'Not Available' }}</td>
                                <td class="py-4 px-6">
                                    <button id="openModalButton"
                                        data-id="{{ $data->id }}"
                                        data-name="{{ $data->user->profile->full_name }}"
                                        data-division="{{ $data->division->name ?? 'dont have division' }}"
                                        data-office="{{ $data->schedules->pluck('office.name')->implode(', ') }}"
                                        onclick="openModal(this.dataset.id, this.dataset.name, this.dataset.division, this.dataset.office)"
                                        class="px-4 py-2 bg-blue-500 text-white rounded-lg shadow-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        Buat
                                    </button>
                                </td>
                                <td class="py-4 px-6">
                                    <a target="blank"
                                        href="{{ Route('admin.shift.schedule.update', ['internId' => $data->id]) }}">
                                        <button id="edit-btn"
                                            class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600">
                                            Edit Schedule
                                        </button>
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    @endforeach
                @endif

            </table>
        </div>

        <form id="shiftForm" action="{{ Route('create.Schedule') }}" method="POST">
            @csrf
            <div id="shiftModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 hidden">
                <div class="bg-white rounded-lg shadow-lg max-w-4xl w-full p-6">
                    <h2 class="text-xl font-bold mb-4">Atur Shift</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <input type="hidden" id="intern-id" name="intern_id">

                        <div>
                            <label for="internName" class="block text-gray-700">Nama</label>
                            <input id="internName" type="text" value="Raihan Ahmad Hafidz" disabled
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500">
                        </div>
                        <div>
                            <label for="type" class="block text-gray-700">Sistem Shift</label>
                            <select id="type" name="type"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500"
                                required>
                                <option value="1">Tetap</option>
                                <option value="2">Bergantian</option>
                            </select>
                        </div>
                        <div>
                            <label for="division" class="block text-gray-700">Divisi</label>
                            <input id="division" type="text" value="UI/UX" disabled
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500">
                        </div>
                        <div>
                            <label for="shift_id" class="block text-gray-700">Shift</label>
                            <select id="shift_id" name="shift_id"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500"
                                required>
                                <option value="" disabled selected>--Pilih Shift--</option>
                                @foreach ($shifts as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="office_id" class="block text-gray-700">Kantor</label>
                            <select id="office_id" name="office_id"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500"
                                required>
                                <option value="" disabled selected>--Pilih Kantor--</option>
                                @foreach ($offices as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="start_period" class="block text-gray-700">Periode Magang</label>
                            <div class="flex space-x-2">
                                <input id="start_period" name="start_period" type="date"
                                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500"
                                    required>
                                <p class="flex items-center justify-center">s/d</p>
                                <input id="end_period" name="end_period" type="date"
                                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500"
                                    required>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end mt-6">
                        <button type="button" id="cancelButton" onclick="closeModal()"
                            class="px-4 py-2 bg-red-600 text-white rounded-lg mr-2">Batal</button>
                        <button type="submit" id="saveButton"
                            class="px-4 py-2 bg-green-600 text-white rounded-lg">Simpan</button>
                    </div>
                </div>
            </div>
        </form>
        <script src="{{ asset('js/admin/schooll.js') }}"></script>
    </main>
@endsection
