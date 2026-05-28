<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Site Under Maintenance</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-900 text-gray-100 font-sans min-h-screen flex flex-col">
    <div class="flex-1 flex flex-col justify-center items-center px-6">
        <div class="bg-gray-800/90 rounded-3xl shadow-2xl p-10 max-w-xl w-full text-center">
            <h1 class="text-5xl font-extrabold text-pink-400 mb-3">500</h1>
            <h2 class="text-2xl font-bold text-white mb-4">Site Under Maintenance</h2>
            <p class="text-gray-300 mb-8">{{ $fatalMessage }}</p>
            <a href="{{ url('/') }}" class="inline-block px-6 py-3 bg-gradient-to-r from-pink-600 to-pink-500 text-white rounded-full font-semibold shadow-lg hover:from-pink-500 hover:to-pink-600 transition">Back to Home</a>
        </div>
    </div>
</body>
</html>
