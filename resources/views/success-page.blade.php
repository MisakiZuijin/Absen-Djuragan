<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Success</title>
    @vite('resources/css/app.css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body class="bg-gray-100 flex items-center justify-center h-screen">

    <div class="bg-white p-8 rounded-lg shadow-lg text-center max-w-lg">
        <!-- Icon -->
        <div class="flex justify-center mb-6">
            <i class="fa-regular fa-circle-check text-6xl text-green-600"></i>
        </div>
        <!-- Heading -->
        <h1 class="text-2xl font-bold text-green-600 mb-4">Password Reset Email Sent Successfully!</h1>
        <!-- Description -->
        <p class="text-gray-600 mb-6">Please check your email for further instructions to reset your password.</p>
        <!-- Button -->
        <a href="{{ Route('login.view') }}"
            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-full">Go to
            Login</a>
    </div>

</body>

</html>
