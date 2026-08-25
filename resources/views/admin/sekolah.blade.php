@extends('layouts.main')

@section('title', 'Sekolah')

@section('contents')
    @include('layouts.sidebar')

    @include('layouts.navbar', ['user' => $user])

    <!-- Main Content -->
    <main class="ml-64 mt-24 p-6 md:ml-48 lg:ml-64">

        <div class="min-h-screen">
            <div class="max-w-7xl mx-auto">
                <h1 class="text-2xl font-bold mb-2">Daftar Sekolah/Universitas</h1>
                <p class="mb-6 text-gray-600">List sekolah/universitas yang sudah terdaftar</p>

                <!-- Search Field -->
                <div class="mb-6">
                    <div class="flex items-center border border-gray-300 rounded-lg">
                        <div class="bg-white p-3 rounded-l-lg">
                            <i class="ml-2 fa-solid fa-search text-gray-800"></i>
                        </div>

                        <input type="text" id="searchInput"
                            class="w-full px-4 py-3 pl-3 rounded-r-lg text-gray-800 focus:outline-none"
                            placeholder="Cari Nama">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6" id="schoolListContainer">
                    @foreach ($schoolList as $item)
                        <a href="{{ route('admin.school.team.view', ['schoolId' => $item->id]) }}"
                            class="division-item bg-gray-700 text-white rounded-lg overflow-hidden shadow hover:shadow-lg flex flex-col justify-between h-full">
                            <div class="p-4 flex-grow">
                                <div class="text-lg font-semibold text-center">{{ $item->name }}</div>
                                <div class="flex items-center justify-between mt-8">
                                    <i class="fa-solid fa-users text-4xl"></i>
                                    <span class="ml-auto text-right text-2xl">{{ $item->intern_total }}</span>
                                </div>
                            </div>
                            <div class="bg-white text-gray-800 px-4 py-2 flex justify-between items-center">
                                <button class="text-sm font-medium">View Detail</button>
                                <i class="fa-solid fa-arrow-right"></i>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <script src="{{ asset('js/admin/schooll.js') }}"></script>

    </main>
@endsection
