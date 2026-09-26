@extends('layouts.main')

@section('title', 'Dashboard')

@section('contents')
    <!-- Main Content -->
    <main class="ml-0 md:ml-64 mt-16 md:mt-20 p-3 sm:p-6 min-w-0">


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
        <section class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 md:gap-6">
            <!-- Card 1 -->
            <div
                class="hover:shadow-xl relative bg-blue-600 text-white p-3.5 sm:p-5 md:p-6 rounded-2xl shadow-lg flex items-start justify-start h-32 sm:h-44 md:h-60 overflow-hidden">
                <div class="absolute top-3 left-3 sm:top-4 sm:left-4 z-10">
                    <h2 class="text-xs sm:text-lg md:text-2xl font-semibold mb-1 sm:mb-2 md:mb-3 leading-tight line-clamp-1">Jumlah Pemagang</h2>
                    <p class="text-2xl sm:text-4xl md:text-5xl font-bold">{{ $internTotal }}</p>
                </div>
                <span class="absolute bottom-2 right-2 sm:bottom-3 sm:right-3 md:bottom-4 md:right-4 opacity-20"><i
                        class="fa-solid fa-users text-black text-4xl sm:text-6xl md:text-8xl"></i></span>
            </div>

            <!-- Card 2 -->
            <div
                class="hover:shadow-xl relative bg-green-600 text-white p-3.5 sm:p-5 md:p-6 rounded-2xl shadow-lg flex items-start justify-start h-32 sm:h-44 md:h-60 overflow-hidden">
                <div class="absolute top-3 left-3 sm:top-4 sm:left-4 z-10">
                    <h2 class="text-xs sm:text-lg md:text-2xl font-semibold mb-1 sm:mb-2 md:mb-3 leading-tight line-clamp-1">Jumlah Masuk</h2>
                    <p class="text-2xl sm:text-4xl md:text-5xl font-bold">{{ $attendanceTotal }}</p>
                </div>
                <span class="absolute bottom-2 right-2 sm:bottom-3 sm:right-3 md:bottom-4 md:right-4 opacity-20"><i
                        class="fa-solid fa-user-plus text-black text-4xl sm:text-6xl md:text-8xl"></i></span>
            </div>

            <!-- Card 3 -->
            <div
                class="hover:shadow-xl relative bg-red-700 text-white p-3.5 sm:p-5 md:p-6 rounded-2xl shadow-lg flex items-start justify-start h-32 sm:h-44 md:h-60 overflow-hidden">
                <div class="absolute top-3 left-3 sm:top-4 sm:left-4 z-10">
                    <h2 class="text-xs sm:text-lg md:text-2xl font-semibold mb-1 sm:mb-2 md:mb-3 leading-tight line-clamp-1">Jumlah Tidak Masuk</h2>
                    <p class="text-2xl sm:text-4xl md:text-5xl font-bold">{{ $absenceTotal }}</p>
                </div>
                <span class="absolute bottom-2 right-2 sm:bottom-3 sm:right-3 md:bottom-4 md:right-4 opacity-20"><i
                        class="fa-solid fa-user-minus text-black text-4xl sm:text-6xl md:text-8xl"></i></span>
            </div>

            <!-- Card 4 -->
            <div
                class="hover:shadow-xl relative bg-yellow-600 text-white p-3.5 sm:p-5 md:p-6 rounded-2xl shadow-lg flex items-start justify-start h-32 sm:h-44 md:h-60 overflow-hidden">
                <div class="absolute top-3 left-3 sm:top-4 sm:left-4 z-10">
                    <h2 class="text-xs sm:text-lg md:text-2xl font-semibold mb-1 sm:mb-2 md:mb-3 leading-tight line-clamp-1">Jumlah Izin</h2>
                    <p class="text-2xl sm:text-4xl md:text-5xl font-bold">{{ $permitTotal }}</p>
                </div>
                <span class="absolute bottom-2 right-2 sm:bottom-3 sm:right-3 md:bottom-4 md:right-4 opacity-20"><i
                        class="fa-solid fa-circle-info text-black text-4xl sm:text-6xl md:text-8xl"></i></span>
            </div>
        </section>

        <!-- Permission Cards Section -->
        <section class="grid grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 md:gap-6 mt-4 sm:mt-6">
            <!-- Kartu Izin Shalat -->
            <div
                class="hover:shadow-xl relative bg-purple-600 text-white p-3.5 sm:p-5 md:p-6 rounded-2xl shadow-lg flex items-start justify-start h-32 sm:h-44 md:h-60 overflow-hidden cursor-pointer"
                data-href="{{ route('admin.izinShalat.index') }}"
                onclick="window.location.href=this.dataset.href">
                <div class="absolute top-3 left-3 sm:top-4 sm:left-4 z-10">
                    <h2 class="text-xs sm:text-lg md:text-2xl font-semibold mb-1 sm:mb-2 md:mb-3 leading-tight line-clamp-1">Izin Shalat</h2>
                    <p class="text-2xl sm:text-4xl md:text-5xl font-bold">{{ $prayerRequestsCount ?? 0 }}</p>
                </div>
                <span class="absolute bottom-2 right-2 sm:bottom-3 sm:right-3 md:bottom-4 md:right-4 opacity-20"><i
                        class="fas fa-mosque text-black text-4xl sm:text-6xl md:text-8xl"></i></span>
            </div>

            <!-- Kartu Izin Keluar -->
            <div
                class="hover:shadow-xl relative bg-pink-600 text-white p-3.5 sm:p-5 md:p-6 rounded-2xl shadow-lg flex items-start justify-start h-32 sm:h-44 md:h-60 overflow-hidden cursor-pointer"
                data-href="{{ route('admin.izinKeluar.index') }}"
                onclick="window.location.href=this.dataset.href">
                <div class="absolute top-3 left-3 sm:top-4 sm:left-4 z-10">
                    <h2 class="text-xs sm:text-lg md:text-2xl font-semibold mb-1 sm:mb-2 md:mb-3 leading-tight line-clamp-1">Izin Keluar</h2>
                    <p class="text-2xl sm:text-4xl md:text-5xl font-bold">{{ $leaveRequestsCount ?? 0 }}</p>
                </div>
                <span class="absolute bottom-2 right-2 sm:bottom-3 sm:right-3 md:bottom-4 md:right-4 opacity-20"><i
                        class="fas fa-sign-out-alt text-black text-4xl sm:text-6xl md:text-8xl"></i></span>
            </div>

            <!-- Kartu Izin ke Toilet -->
            <div
                class="col-span-2 lg:col-span-1 hover:shadow-xl relative bg-teal-600 text-white p-3.5 sm:p-5 md:p-6 rounded-2xl shadow-lg flex items-start justify-start h-32 sm:h-44 md:h-60 overflow-hidden cursor-pointer"
                data-href="{{ route('admin.izinToilet.index') }}"
                onclick="window.location.href=this.dataset.href">
                <div class="absolute top-3 left-3 sm:top-4 sm:left-4 z-10">
                    <h2 class="text-xs sm:text-lg md:text-2xl font-semibold mb-1 sm:mb-2 md:mb-3 leading-tight line-clamp-1">Izin ke Toilet</h2>
                    <p class="text-2xl sm:text-4xl md:text-5xl font-bold">{{ $toiletPermitsCount ?? 0 }}</p>
                </div>
                <span class="absolute bottom-2 right-2 sm:bottom-3 sm:right-3 md:bottom-4 md:right-4 opacity-20"><i
                        class="fas fa-restroom text-black text-4xl sm:text-6xl md:text-8xl"></i></span>
            </div>
        </section>

        <section class="grid grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 md:gap-6 mt-4 sm:mt-6">
            <!-- Card 1 -->
            <div
                class="hover:shadow-xl relative bg-green-600 text-white p-3.5 sm:p-5 md:p-6 rounded-2xl shadow-lg flex items-start justify-start h-32 sm:h-44 md:h-60 overflow-hidden">
                <div class="absolute top-3 left-3 sm:top-4 sm:left-4 z-10">
                    <h2 class="text-xs sm:text-lg md:text-2xl font-semibold mb-1 sm:mb-2 md:mb-3 leading-tight line-clamp-1">Kantor 1</h2>
                    <p class="text-2xl sm:text-4xl md:text-5xl font-bold">{{ $totalAttendanceOffice1 }}</p>
                </div>
                <span class="absolute bottom-2 right-2 sm:bottom-3 sm:right-3 md:bottom-4 md:right-4 opacity-20"><i
                        class="fa-solid fa-user-plus text-black text-4xl sm:text-6xl md:text-8xl"></i></span>
            </div>

            <!-- Card 2 -->
            <div
                class="hover:shadow-xl relative bg-green-600 text-white p-3.5 sm:p-5 md:p-6 rounded-2xl shadow-lg flex items-start justify-start h-32 sm:h-44 md:h-60 overflow-hidden">
                <div class="absolute top-3 left-3 sm:top-4 sm:left-4 z-10">
                    <h2 class="text-xs sm:text-lg md:text-2xl font-semibold mb-1 sm:mb-2 md:mb-3 leading-tight line-clamp-1">Kantor 2</h2>
                    <p class="text-2xl sm:text-4xl md:text-5xl font-bold">{{ $totalAttendanceOffice2 }}</p>
                </div>
                <span class="absolute bottom-2 right-2 sm:bottom-3 sm:right-3 md:bottom-4 md:right-4 opacity-20"><i
                        class="fa-solid fa-user-plus text-black text-4xl sm:text-6xl md:text-8xl"></i></span>
            </div>

            <!-- Card 3 -->
            <div
                class="col-span-2 lg:col-span-1 hover:shadow-xl relative bg-green-600 text-white p-3.5 sm:p-5 md:p-6 rounded-2xl shadow-lg flex items-start justify-start h-32 sm:h-44 md:h-60 overflow-hidden">
                <div class="absolute top-3 left-3 sm:top-4 sm:left-4 z-10">
                    <h2 class="text-xs sm:text-lg md:text-2xl font-semibold mb-1 sm:mb-2 md:mb-3 leading-tight line-clamp-1">Kantor 4</h2>
                    <p class="text-2xl sm:text-4xl md:text-5xl font-bold">{{ $totalAttendanceOffice3 }}</p>
                </div>
                <span class="absolute bottom-2 right-2 sm:bottom-3 sm:right-3 md:bottom-4 md:right-4 opacity-20"><i
                        class="fa-solid fa-user-plus text-black text-4xl sm:text-6xl md:text-8xl"></i></span>
            </div>

        </section>
    </main>
@endsection
