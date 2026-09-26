@extends('layouts.main')

@section('title', 'Sekolah')

@section('contents')
    <!-- Main Content -->
    <main class="ml-0 md:ml-64 mt-16 md:mt-20 p-3 sm:p-6 min-w-0">

        <div class="min-h-screen">
            <div class="max-w-7xl mx-auto">
                <h1 class="text-xl sm:text-2xl font-bold mb-1 sm:mb-2">Daftar Sekolah/Universitas</h1>
                <p class="mb-4 sm:mb-6 text-xs sm:text-sm text-gray-600">List sekolah/universitas yang sudah terdaftar</p>

                <!-- Search Field -->
                <div class="mb-4 sm:mb-6">
                    <div class="flex items-center border border-gray-300 rounded-lg">
                        <div class="bg-white p-2.5 sm:p-3 rounded-l-lg">
                            <i class="ml-2 fa-solid fa-search text-gray-800 text-sm"></i>
                        </div>

                        <input type="text" id="searchInput"
                            class="w-full px-3 sm:px-4 py-2 sm:py-3 pl-2 sm:pl-3 rounded-r-lg text-gray-800 focus:outline-none text-xs sm:text-base"
                            placeholder="Cari Nama">
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-6" id="schoolListContainer">
                    @foreach ($schoolList as $item)
                        <a href="{{ route('admin.school.team.view', ['schoolId' => $item->id]) }}"
                            class="division-item bg-gray-700 text-white rounded-xl overflow-hidden shadow hover:shadow-lg flex flex-col justify-between h-full">
                            <div class="p-3 sm:p-4 flex-grow">
                                <div class="text-xs sm:text-lg font-semibold text-center line-clamp-2">{{ $item->name }}</div>
                                <div class="flex items-center justify-between mt-4 sm:mt-8">
                                    <i class="fa-solid fa-users text-2xl sm:text-4xl text-gray-300"></i>
                                    <span class="ml-auto text-right text-lg sm:text-2xl font-bold">{{ $item->intern_total }}</span>
                                </div>
                            </div>
                            <div class="bg-white text-gray-800 px-3 sm:px-4 py-2 flex justify-between items-center">
                                <button class="text-xs sm:text-sm font-medium">View Detail</button>
                                <i class="fa-solid fa-arrow-right text-xs"></i>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <script src="{{ asset('js/admin/schooll.js') }}"></script>

    </main>
@endsection
