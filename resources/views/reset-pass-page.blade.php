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
        <!-- Heading -->
        <h1 class="text-3xl font-bold text-red-800 mb-4 text-center">Buat Password Baru</h1>
        <!-- Description -->
        <!-- Reset Password Form -->
        <form action="{{ route('change-password.action', ['jwt' => $key]) }}" method="POST" id="passwordForm">
            @csrf
            <!-- Password -->
            <div class="relative p-3">
                <input
                    class="flex w-full shadow appearance-none border border-gray-800 rounded-xl py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 pr-10"
                    id="password" name='password' type="password" placeholder="Masukkan password baru">
                <i class="fa fa-eye absolute inset-y-0 right-3 mt-5 flex items-center pr-3 cursor-pointer text-gray-500"
                    id="togglePassword1"></i>
            </div>
            <div class="relative p-3 mb-4">
                <input
                    class="flex w-full shadow appearance-none border border-gray-800 rounded-xl py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 pr-10"
                    id="confirmPassword" type="password" placeholder="Konfirmasi password">
                <i class="fa fa-eye absolute inset-y-0 right-3 flex mt-5 items-center pr-3 cursor-pointer text-gray-500"
                    id="togglePassword2"></i>
            </div>
            <p id="error-message" class="text-red-500 text-sm p-3 hidden">Password dan Konfirmasi Password tidak sama.
            </p>
            <!-- Submit Button -->
            <div class="flex items-center justify-center">
                <button
                    class="text-center bg-red-900 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-full focus:outline-none focus:shadow-outline w-full sm:w-40"
                    type="submit">Reset Password</button>
            </div>
        </form>
    </div>

    <script src="{{ asset('js/index/reset-password.js') }}"></script>
</body>

</html>
