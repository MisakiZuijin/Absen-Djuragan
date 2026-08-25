<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    @vite('resources/css/app.css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Include Font Awesome -->
</head>

<body class="h-screen bg-gray-100 flex items-center justify-center py-5">
    <div class="p-8">
        <!-- Back Icon -->
        <div class="absolute top-0 left-0 p-8">
            <a href="#" class="text-gray-700 hover:text-gray-900"><i class="fas fa-arrow-left fa-lg"></i>
                <!-- Back Icon --></a>
        </div>
        <!-- Heading -->
        <div class="text-center text-7xl mb-8"><i class="fa-solid fa-clock-rotate-left"></i></div>
        <p class="text-gray-800 mb-10 text-center ">Masukkan 4 digit kode OTP yang telah kami kirimkan ke email Anda.
        </p>
        <!-- Reset Password Form -->
        <form>
            <!-- Verification Code -->
            <div class="mb-6">
                <div class="flex justify-between mx-auto w-64">
                    <input class="w-14 h-14 text-center border border-gray-300 rounded-md mx-1 m" type="text"
                        maxlength="1" placeholder="">
                    <input class="w-14 h-14 text-center border border-gray-300 rounded-md mx-1" type="text"
                        maxlength="1" placeholder="">
                    <input class="w-14 h-14 text-center border border-gray-300 rounded-md mx-1" type="text"
                        maxlength="1" placeholder="">
                    <input class="w-14 h-14 text-center border border-gray-300 rounded-md mx-1" type="text"
                        maxlength="1" placeholder="">
                </div>
            </div>
            <!-- Submit Button -->
            <div class="flex items-center justify-center mt-10">
                <a href="pw-baru.html"
                    class="text-center bg-gray-900 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-full focus:outline-none focus:shadow-outline w-full sm:w-40"
                    type="submit">Verif Now</a>
            </div>

            <p class="text-gray-800 mt-6 text-center ">Belum menerima email? <a class="font-bold text-red-700">Kirim
                    Ulang</a></p>
        </form>
    </div>
</body>

</html>
