<!-- Main Content -->
<main class="ml-64 mt-24 p-6 md:ml-48 lg:ml-64">
    <div class="bg-gray-700 text-white p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Left Column -->
            <div class="flex flex-col space-y-4">
                <div class="text-4xl font-bold">Laporan Data Presensi</div>
                <div class="text-lg">Data per tanggal {{ now()->format('d-m-Y') }}</div>
            </div>

            <!-- Right Column -->
            <div class="flex flex-col space-y-2">
                <label for="search-student" class="text-lg font-medium">Cari Mahasiswa</label>
                <div class="relative">
                    <input type="text" id="search-student" name="student_name" wire:model="student_name"
                        class="pl-10 p-2 rounded text-gray-800 border-gray-300 focus:outline-none focus:border-blue-500 w-full"
                        placeholder="Masukkan nama mahasiswa">
                    <i class="fa-solid fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white grid grid-cols-1 lg:grid-cols-2 gap-6 w-full">
        <!-- Right Side: Date Input dan Filter -->
        <div class="px-6 py-4 rounded-lg flex items-center gap-4 lg:justify-end lg:col-start-2 w-full">
            <!-- Date Inputs for Range -->
            <div class="flex items-center gap-4">
                <div class="relative">
                    <input type="date" name="date_start" wire:model="date_start"
                        class="p-3 pl-10 w-full border border-gray-800 rounded-md focus:outline-none focus:border-blue-500"
                        placeholder="Start Date">
                </div>
                <span class="text-gray-700">s/d</span>
                <div class="relative">
                    <input type="date" name="date_end" wire:model="date_end"
                        class="p-3 pl-10 w-full border border-gray-800 rounded-md focus:outline-none focus:border-blue-500"
                        placeholder="End Date">
                </div>
            </div>

            <!-- Filter Select -->
            <select wire:model="filter_option"
                class="p-3 w-40 border border-gray-800 rounded-md focus:outline-none focus:border-blue-500">
                <option value="">Filter</option>
                <!-- Tambahkan opsi lainnya di sini -->
            </select>

            <!-- Search Button -->
            <button wire:click="searchInternAttdReport"
                class="bg-gray-800 text-white p-3 rounded-md hover:bg-gray-600 flex items-center gap-2">
                <i class="fas fa-search"></i>
                Search
            </button>
        </div>
    </div>

    <div>
        <table class="min-w-full bg-white shadow-md rounded-lg mt-2">
            <thead>
                <tr class="bg-gray-200 text-gray-700 text-sm uppercase leading-normal text-center">
                    <th rowspan="2" class="px-4 py-2 border-r">
                        <input type="checkbox" id="select-all" class="form-checkbox h-5 w-5 text-blue-600">
                    </th>
                    <th rowspan="2" class="px-4 py-2 border-r">No</th>
                    <th rowspan="2" class="px-4 py-2 border-r">Nama</th>
                    <th rowspan="2" class="px-4 py-2 border-r">NIP</th>
                    <th rowspan="2" class="px-4 py-2 border-r">Total Kehadiran</th>
                    <th rowspan="2" class="px-4 py-2 border-r">Total Izin</th>
                    <th rowspan="2" class="px-4 py-2 border-r">Total Ketidakhadiran</th>
                </tr>
            </thead>

            <tbody class="text-sm font-normal text-gray-800">
                @foreach ($reportData as $key => $data)
                    <tr class="border-b border-gray-300 text-center">
                        <td class="px-4 py-2">
                            <input type="checkbox" class="form-checkbox h-5 w-5">
                        </td>
                        <td class="px-4 py-2">{{ ($currentPage - 1) * $perPage + $key + 1 }}</td>
                        <td class="px-4 py-2">{{ $data['user']->profile->full_name }}</td>
                        <td class="px-4 py-2">{{ $data['user']->profile->NIP }}</td>
                        <td class="px-4 py-2">
                            {{ $data['submitted'] }}
                            <a href="#" class="inline-block ml-2">
                                <i class="fas fa-info-circle"></i>
                            </a>
                        </td>
                        <td class="px-4 py-2">
                            {{ $data['permits'] }}
                            <a href="#" class="inline-block ml-2">
                                <i class="fas fa-info-circle"></i>
                            </a>
                        </td>
                        <td class="px-4 py-2">
                            {{ $data['absence'] }}
                            <a href="#" class="inline-block ml-2">
                                <i class="fas fa-info-circle"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Pagination Links -->
        <div class="mt-4">
            @if ($totalPages > 1)
                <nav>
                    <ul class="flex justify-center">
                        @for ($i = 1; $i <= $totalPages; $i++)
                            <li class="mx-1">
                                <button wire:click="goToPage({{ $i }})"
                                    class="px-4 py-2 border rounded {{ $i == $currentPage ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700' }}">
                                    {{ $i }}
                                </button>
                            </li>
                        @endfor
                    </ul>
                </nav>
            @endif
        </div>
    </div>

    <!-- Download PDF Button -->
    <div class="mt-4 flex justify-start">
        <button
            class="px-3 py-2 text-sm font-medium text-center text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800">
            <i class="fa-solid fa-download"></i>
            <span>Download PDF</span>
        </button>
    </div>
</main>
