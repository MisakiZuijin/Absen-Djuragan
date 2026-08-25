@extends('layouts.main')

@section('title', 'Dashboard')

@section('contents')
    @include('layouts.sidebar')

    @include('layouts.navbar', ['user' => $user])

    <!-- Main Content -->
    {{-- PERBAIKAN FINAL DI SINI: Menggunakan padding-top untuk memberi ruang bagi navbar --}}
    <main class="ml-64 p-6" style="padding-top: 7rem;">


        <!-- Notification Box -->
        @if (session('success'))
            <div class="floating-box hidden" id="notification-box">
                <i class="fa-solid fa-check-circle"></i>
                {{ session('success') ?? ($errors->first() ?? 'Gagal Login') }}
            </div>
        @else
            <div class="floating-box hidden" id="notification-box">
                <i class="fa-solid fa-triangle-exclamation"></i>
                {{ session('success') ?? ($errors->first() ?? 'Gagal Login') }}
            </div>
        @endif

        <!-- Success Notification Handling -->
        @if (session('success') || ($errors->any() && $errors->first() != null))
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    function showBox() {
                        var box = document.getElementById('notification-box');
                        box.classList.remove('hidden');
                        box.classList.add('show');

                        if ("{{ session('success') }}") {
                            box.classList.add('bg-green-500');
                        } else {
                            box.classList.add('bg-red-500');
                        }
                    }

                    function hideBox() {
                        var box = document.getElementById('notification-box');
                        box.classList.remove('show');
                        box.classList.add('hidden');
                    }

                    showBox();
                    setTimeout(hideBox, 3000);
                });
            </script>
        @endif

        <!-- Cards Section -->
        <section class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
            <!-- Card 1 -->
            <div
                class="hover:shadow-xl relative bg-blue-600 text-white p-6 rounded-lg shadow-lg flex items-start justify-start h-60">
                <div class="absolute top-4 left-4">
                    <h2 class="text-2xl font-semibold mb-3">Jumlah Pemagang</h2>
                    <p class="text-5xl font-bold">{{ $internTotal }}</p>
                </div>
                <span class="absolute bottom-4 right-4 opacity-20"><i
                        class="fa-solid fa-users text-black text-8xl"></i></span>
            </div>

            <!-- Card 2 -->
            <div
                class="hover:shadow-xl relative bg-green-600 text-white p-6 rounded-lg shadow-lg flex items-start justify-start h-60">
                <div class="absolute top-4 left-4">
                    <h2 class="text-2xl font-semibold mb-3">Jumlah Masuk</h2>
                    <p class="text-5xl font-bold">{{ $attendanceTotal }}</p>
                </div>
                <span class="absolute bottom-4 right-4 opacity-20"><i
                        class="fa-solid fa-user-plus text-black text-8xl"></i></span>
            </div>

            <!-- Card 3 -->
            <div
                class="hover:shadow-xl relative bg-red-700 text-white p-6 rounded-lg shadow-lg flex items-start justify-start h-60">
                <div class="absolute top-4 left-4">
                    <h2 class="text-2xl font-semibold mb-3">Jumlah Tidak Masuk</h2>
                    <p class="text-5xl font-bold">{{ $absenceTotal }}</p>
                </div>
                <span class="absolute bottom-4 right-4 opacity-20"><i
                        class="fa-solid fa-user-minus text-black text-8xl"></i></span>
            </div>

            <!-- Card 4 -->
            <div
                class="hover:shadow-xl relative bg-yellow-600 text-white p-6 rounded-lg shadow-lg flex items-start justify-start h-60">
                <div class="absolute top-4 left-4">
                    <h2 class="text-2xl font-semibold mb-3">Jumlah Izin</h2>
                    <p class="text-5xl font-bold">{{ $permitTotal }}</p>
                </div>
                <span class="absolute bottom-4 right-4 opacity-20"><i
                        class="fa-solid fa-circle-info text-black text-8xl"></i></span>
            </div>
        </section>

        <!-- Permission Cards Section -->
        <section class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3 mt-6">
            <!-- Kartu Izin Shalat -->
            <div
                class="hover:shadow-xl relative bg-purple-600 text-white p-6 rounded-lg shadow-lg flex items-start justify-start h-60 cursor-pointer"
                onclick="window.location.href='{{ route('admin.izinShalat.index') }}'">
                <div class="absolute top-4 left-4">
                    <h2 class="text-2xl font-semibold mb-3">Izin Shalat</h2>
                    <p class="text-5xl font-bold">{{ $prayerRequestsCount ?? 0 }}</p>
                </div>
                <span class="absolute bottom-4 right-4 opacity-20"><i
                        class="fas fa-mosque text-black text-8xl"></i></span>
            </div>

            <!-- Kartu Izin Keluar -->
            <div
                class="hover:shadow-xl relative bg-pink-600 text-white p-6 rounded-lg shadow-lg flex items-start justify-start h-60 cursor-pointer"
                onclick="window.location.href='{{ route('admin.izinKeluar.index') }}'">
                <div class="absolute top-4 left-4">
                    <h2 class="text-2xl font-semibold mb-3">Izin Keluar</h2>
                    <p class="text-5xl font-bold">{{ $leaveRequestsCount ?? 0 }}</p>
                </div>
                <span class="absolute bottom-4 right-4 opacity-20"><i
                        class="fas fa-sign-out-alt text-black text-8xl"></i></span>
            </div>

            <!-- Kartu Izin ke Toilet -->
            <div
                class="hover:shadow-xl relative bg-teal-600 text-white p-6 rounded-lg shadow-lg flex items-start justify-start h-60 cursor-pointer"
                onclick="window.location.href='{{ route('admin.izinToilet.index') }}'">
                <div class="absolute top-4 left-4">
                    <h2 class="text-2xl font-semibold mb-3">Izin ke Toilet</h2>
                    <p class="text-5xl font-bold">{{ $toiletPermitsCount ?? 0 }}</p>
                </div>
                <span class="absolute bottom-4 right-4 opacity-20"><i
                        class="fas fa-restroom text-black text-8xl"></i></span>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3 mt-6">
            <!-- Card 1 -->
            <div
                class="hover:shadow-xl relative bg-green-600 text-white p-6 rounded-lg shadow-lg flex items-start justify-start h-60">
                <div class="absolute top-4 left-4">
                    <h2 class="text-2xl font-semibold mb-3">Kantor 1</h2>
                    <p class="text-5xl font-bold">{{ $totalAttendanceOffice1 }}</p>
                </div>
                <span class="absolute bottom-4 right-4 opacity-20"><i
                        class="fa-solid fa-user-plus text-black text-8xl"></i></span>
            </div>

            <!-- Card 2 -->
            <div
                class="hover:shadow-xl relative bg-green-600 text-white p-6 rounded-lg shadow-lg flex items-start justify-start h-60">
                <div class="absolute top-4 left-4">
                    <h2 class="text-2xl font-semibold mb-3">Kantor 2</h2>
                    <p class="text-5xl font-bold">{{ $totalAttendanceOffice2 }}</p>
                </div>
                <span class="absolute bottom-4 right-4 opacity-20"><i
                        class="fa-solid fa-user-plus text-black text-8xl"></i></span>
            </div>

            <!-- Card 3 -->
            <div
                class="hover:shadow-xl relative bg-green-600 text-white p-6 rounded-lg shadow-lg flex items-start justify-start h-60">
                <div class="absolute top-4 left-4">
                    <h2 class="text-2xl font-semibold mb-3">Kantor 4</h2>
                    <p class="text-5xl font-bold">{{ $totalAttendanceOffice3 }}</p>
                </div>
                <span class="absolute bottom-4 right-4 opacity-20"><i
                        class="fa-solid fa-user-plus text-black text-8xl"></i></span>
            </div>

        </section>
    </main>
@endsection
