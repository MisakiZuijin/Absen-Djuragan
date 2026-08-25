<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Halaman @yield('title') | User</title>
    @vite('resources/css/app.css')
    <link rel="stylesheet" href="{{ asset('css/style.css') }}" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    @livewireStyles
    @livewireScripts
</head>

<body class="h-screen bg-gray-100">


    <aside id="success-notif"
        class="fixed z-50 hidden flex items-center justify-center px-5 py-2 text-white bg-green-500 rounded-lg top-4 right-4">
        <i class="fa-solid fa-check-circle"></i>
        <span id="success-message" class="ml-2 text-xl font-medium hover:opacity-75">

        </span>
    </aside>

    <aside id="error-notif"
        class="fixed z-50 hidden flex items-center justify-center px-5 py-2 text-white bg-red-500 rounded-lg top-4 right-4">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <span id="error-message" class="ml-2 text-xl font-medium hover:opacity-75">
            failed
        </span>
    </aside>


    <!-- Success Notification Box -->
    <div class="floating-box hidden bg-green-500" id="success-box">
        <i class="fa-solid fa-check-circle"></i>
        {{ session('success') ?? '' }}
    </div>

    <!-- Error Notification Box -->
    <div class="floating-box hidden" id="error-box">
        <i class="fa-solid fa-triangle-exclamation"></i>
        {{ $error ?? '' }}
    </div>

    <div class="relative h-full flex flex-col">
        <!-- Background Image -->
        <div class="relative h-[250px]">
            @php
                $birth_date = $user->profile->date_of_birth;
                $today = now()->format('m-d');
                $userBirth = \Carbon\Carbon::parse($birth_date)->format('m-d');
            @endphp

            @if ($today === $userBirth)
                <img src="{{ asset('img/bg2.jpg') }}" alt="Background Image"
                    class="w-full h-full object-cover md:rounded-br-[40px] no-select">
            @else
                <img src="{{ asset('img/bg.jpg') }}" alt="Background Image"
                    class="w-full h-full object-cover md:rounded-br-[40px] no-select">
            @endif

            <!-- Welcome Message -->
            <div class="absolute inset-0 flex items-center justify-center z-10 p-2 md:p-4">
                <div class="typewriter text-xl md:text-3xl font-bold text-white text-center italic">
                    <h1 id="typewriter-text"></h1>
                </div>

                <!-- Profile Info and Logout Button -->
                <div
                    class="absolute bottom-4 left-4 md:left-10 flex items-center space-x-2 md:space-x-4 text-white z-20 bg-black p-1 md:p-3 bg-opacity-50 rounded-3xl">
                    <i class="fas fa-user-circle text-2xl md:text-3xl"></i>
                    <div class="text-xs md:text-sm">
                        <div class="font-bold text-xs md:text-sm">{{ $user->profile->full_name }}</div>
                        <div class="text-xs md:text-sm">{{ $user->profile->NIP }}</div>
                    </div>
                </div>

                <div class="absolute bottom-4 right-4 md:right-10 text-white z-20 p-2 md:p-4">
                    <button class="logoutModal">
                        <i class="fas fa-sign-out-alt text-xl md:text-3xl cursor-pointer"></i>
                    </button>
                </div>
            </div>

            <!-- Date Icon and Date -->
            <div
                class="absolute top-4 left-4 md:left-10 flex items-center space-x-1 md:space-x-2 text-white z-20 text-xs md:text-2xl p-1 md:p-3">
                <i class="fas fa-calendar-day text-xs md:text-2xl"></i>
                <span class="text-xs md:text-2xl">{{ $day_now }}, {{ $date_now }}</span>
            </div>

            <!-- Real-time Clock -->
            <div class="absolute top-4 right-4 md:right-10 text-white z-20 text-xs md:text-2xl p-1 md:p-3 rounded-xl">
                <span id="current-time" class="text-xs md:text-2xl">14:30:00</span>
            </div>
            @yield('contents')
        </div>
        <!-- Modal Logout -->
        <div id="logout-modal"
            class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg p-6 w-4/5 md:w-1/3">
                <h2 class="text-xl font-bold mb-4">Konfirmasi Keluar</h2>
                <p>Apakah Anda yakin ingin keluar halaman ini?</p>
                <div class="flex justify-end mt-4">
                    <button type="button" id="closeLogout"
                        class="px-4 py-2 bg-gray-600 text-white rounded-lg mr-2">Batal</button>
                    <a href="{{ url('/logout') }}" id="logoutConfirm"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg">Keluar</a>
                </div>
            </div>
        </div>

        <script>
            function updateClock() {
                const now = new Date();
                const hours = String(now.getHours()).padStart(2, "0");
                const minutes = String(now.getMinutes()).padStart(2, "0");
                const seconds = String(now.getSeconds()).padStart(2, "0");

                const currentTime = `${hours}:${minutes}:${seconds}`;
                const timeElement = document.getElementById("current-time");

                if (timeElement) {
                    timeElement.textContent = currentTime;
                } else {
                    console.error("Element with ID 'current-time' not found.");
                }
            }

            setInterval(updateClock, 1000);

            updateClock();

            document.addEventListener('DOMContentLoaded', () => {

                const typewriterTextElement = document.getElementById('typewriter-text');
                const texts = @json($quotes);
                const delayBeforeChange = 10000;

                function getRandomIndex(max) {
                    return Math.floor(Math.random() * max);
                }

                function formatTextWithLineBreaks(text) {
                    const words = text.split(' ');
                    let formattedText = '';
                    for (let i = 0; i < words.length; i++) {
                        formattedText += words[i];
                        if ((i + 1) % 4 === 0 && i !== words.length - 1) {
                            formattedText += '<br>';
                        } else {
                            formattedText += ' ';
                        }
                    }
                    return formattedText.trim();
                }

                function changeText() {
                    const randomIndex = getRandomIndex(texts.length);
                    const formattedText = formatTextWithLineBreaks(texts[randomIndex]);

                    // Remove animation class, trigger reflow, and then add it back to reset the animation
                    typewriterTextElement.innerHTML = formattedText;
                    typewriterTextElement.classList.remove('typing-animation'); // Remove animation class
                    void typewriterTextElement.offsetWidth; // Trigger reflow
                    typewriterTextElement.classList.add('typing-animation'); // Add animation class back
                }

                changeText();

                setInterval(() => {
                    changeText();
                }, delayBeforeChange);
            });


            $(document).ready(function() {

                $('.logoutModal').on('click', function(event) {
                    event.preventDefault();

                    $('#logout-modal').removeClass('hidden');
                });

                $('#closeLogout').on('click', function() {
                    $('#logout-modal').addClass('hidden');
                });

                $(window).on('click', function(event) {
                    if ($(event.target).is('#logout-modal')) {
                        $('#logout-modal').addClass('hidden');
                    }
                });
            });

            document.addEventListener('DOMContentLoaded', function() {
                function showBox(id) {
                    var box = document.getElementById(id);
                    box.classList.remove('hidden');
                    box.classList.add('show');
                }

                function hideBox(id) {
                    var box = document.getElementById(id);
                    box.classList.remove('show');
                    box.classList.add('hidden');
                }

                @if (isset($error))
                    showBox('error-box');
                    setTimeout(function() {
                        hideBox('error-box');
                    }, 3000);
                @endif

                @if (session('success'))
                    showBox('success-box');
                    setTimeout(function() {
                        hideBox('success-box');
                    }, 3000);
                @endif
            });
        </script>
</body>

</html>
