@extends('layouts.main')

@section('title', 'Edit Profile')

@section('contents')
    <!-- Main Content -->
    <main class="ml-0 md:ml-64 mt-16 md:mt-20 p-3 sm:p-6 min-w-0">

        <div class="flex flex-col lg:flex-row justify-center items-stretch bg-gray-100 p-3 sm:p-6 gap-6">
            <!-- Left Section -->
            <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-sm flex flex-col h-full mx-auto lg:mx-0">
                <div class="flex flex-col items-center">
                    <img src="{{ asset('img/profile.jpg') }}" alt="Profile Photo" class="rounded-full w-32 h-32 mb-4">
                    <h2 class="text-xl font-semibold mb-1">{{ $user->profile->full_name }}</h2>
                    <p class="text-gray-600 mb-4">{{ $user->email }}</p>
                    <h3 class="text-lg font-semibold mb-2">About</h3>
                    <p class="text-gray-600 text-center">
                        Memiliki akses penuh ke semua fitur dan pengaturan sistem.
                    </p>
                </div>
            </div>

            <!-- Right Section -->
            <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-2xl flex flex-col mx-auto lg:mx-0">

                <h3 class="text-lg font-semibold mb-4">Personal Details</h3>

                @if (session('success'))
                    <div id="success-message"
                        class="mb-2 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative transition-opacity duration-500"
                        role="alert">
                        <strong class="font-bold">Success!</strong>
                        <span class="block sm:inline">{{ session('success') }}</span>
                        <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer" onclick="removeMessage()">
                            <svg class="fill-current h-6 w-6 text-green-500" role="button"
                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <title>Close</title>
                                <path
                                    d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z" />
                            </svg>
                        </span>
                    </div>
                @endif
                <div class="mb-6">
                    <form action="{{ route('profiles.update', ['id' => $user->profile->id]) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label for="nama" class="block text-sm font-medium text-gray-700">Nama Lengkap<span
                                    class="text-red-500">*</span></label>
                                <input type="text" id="nama" name="nama"
                                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                    value="{{ $user->profile->full_name }}" required>
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                <input type="email" id="email"
                                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed"
                                    value="{{ $user->email }}" disabled>
                            </div>
                            <div>
                                <label for="nohp" class="block text-sm font-medium text-gray-700">No HP<span
                                    class="text-red-500">*</span></label>
                                <input type="text" id="nohp" name="nohp"
                                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                    value="{{ $user->profile->phone }}" required>
                            </div>
                            <div>
                                <label for="alamat" class="block text-sm font-medium text-gray-700">Alamat<span
                                    class="text-red-500">*</span></label>
                                <input type="text" id="alamat" name="alamat"
                                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                    value="{{ $user->profile->address }}" required>
                            </div>
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700">Password (opsional)</label>
                                <input type="text" id="password" name="password"
                                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>

                        <div class="flex justify-end mt-6">
                            <button class="px-6 py-2 text-white bg-gray-800 hover:bg-gray-600 rounded-lg"
                                type="submit">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const message = document.getElementById('success-message');
            if (message) {
                setTimeout(() => {
                    message.style.opacity = 0;
                    setTimeout(() => message.remove(), 600); // Hapus elemen setelah transisi selesai
                }, 3000); // Ubah 3000 untuk durasi tampil dalam milidetik (3 detik)
            }
        });
    </script>
@endsection
