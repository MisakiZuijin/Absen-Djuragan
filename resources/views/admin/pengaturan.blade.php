@extends('layouts.main')

@section('title', 'Pengaturan Quotes')

@section('contents')
    @include('layouts.sidebar')

    @include('layouts.sidebar-pengaturan')

    @include('layouts.navbar')

    <!-- Main Content -->
    <main class="ml-[32rem] mt-24 p-6">
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
        <div class="flex gap-6">

            <!-- Quotes List -->
            <div class="flex-1">

                <h2 class="text-xl font-semibold mb-4">Daftar Quotes</h2>

                <ul class="bg-white p-4 rounded shadow">
                    @foreach ($quotes as $quote)
                        <li class="flex justify-between items-center border-b border-gray-200 py-2">
                            <span>{{ $quote->quote }}</span>
                            <form action="{{ route('quotes.delete', $quote->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                        </li>
                    @endforeach
                </ul>

                <form action="{{ route('quotes.store') }}" method="POST" class="mt-6">
                    @csrf
                    <h3 class="text-lg font-semibold mb-2">Tambah Quote</h3>
                    <div class="flex flex-col gap-4">
                        <input type="text" name="quote" placeholder="Masukkan kutipan baru"
                            class="border border-gray-300 p-2 rounded" required>
                        <input type="hidden" name="kategori" placeholder="" class="border border-gray-300 p-2 rounded"
                            value="quote">
                        <button type="submit"
                            class="bg-gray-800 text-white py-2 px-4 rounded hover:bg-gray-600">Tambahkan</button>
                    </div>
                </form>
            </div>

            <!-- Quotes Ulang Tahun -->
            <div class="w-1/3">
                <h2 class="text-xl font-semibold mb-4">Quotes Ulang Tahun</h2>
                <ul class="bg-white p-4 rounded shadow mb-2">
                    @foreach ($quotesultah as $quote)
                        <li class="flex justify-between items-center border-b border-gray-200 py-2">
                            <span>{{ $quote->quote }}</span>
                            <form action="{{ route('quotes.delete', $quote->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                        </li>
                    @endforeach
                </ul>
                <form action="{{ route('quotes.ultah.store') }}" method="POST">
                    @csrf
                    <div class="flex flex-col gap-4">
                        <input type="text" name="quote" placeholder="Masukkan kutipan ulang tahun"
                            class="border border-gray-300 p-2 rounded" required>
                        <input type="hidden" name="kategori" placeholder="" class="border border-gray-300 p-2 rounded"
                            value="ultah">
                        <button type="submit"
                            class="bg-gray-800 text-white py-2 px-4 rounded hover:bg-gray-600">Tambahkan</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
    <script src="{{ asset('js/admin/seeting.js') }}"></script>
    <script>
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
