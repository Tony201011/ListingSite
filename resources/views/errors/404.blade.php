<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <style>
        [x-cloak] { display: none !important; }
        .hero-gradient { background: linear-gradient(180deg, rgba(17,24,39,0.7) 0%, rgba(17,24,39,1) 100%); }
    </style>
</head>
<body class="bg-gray-900 text-gray-100 font-sans min-h-screen flex flex-col">
    <div class="flex-1 flex flex-col justify-center items-center">
        <div class="bg-gray-800/90 rounded-3xl shadow-2xl p-6 sm:p-10 max-w-lg w-full text-center mx-4">
            <div class="flex flex-col items-center">
                <svg class="w-16 h-16 sm:w-24 sm:h-24 text-pink-500 mb-4 sm:mb-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 48 48">
                    <circle cx="24" cy="24" r="22" stroke="currentColor" stroke-width="4" fill="#fff"/>
                    <path d="M16 32l16-16M32 32L16 16" stroke-linecap="round"/>
                </svg>
                <h1 class="text-4xl sm:text-6xl font-extrabold text-pink-400 mb-2">404</h1>
                <h2 class="text-xl sm:text-2xl font-bold text-white mb-4">Page Not Found</h2>
                <p class="text-xs sm:text-base text-gray-300 mb-6 sm:mb-8">The page you are looking for is unavailable.<br>It may have been moved, renamed, or deleted.</p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-2 sm:gap-3">
                    <button onclick="window.history.back()" class="inline-block px-4 sm:px-6 py-2 sm:py-3 border border-pink-400 text-pink-200 rounded-full font-semibold text-xs sm:text-base hover:bg-pink-500/10 transition w-full sm:w-auto text-center">Go Back</button>
                    <a href="{{ url('/') }}" class="inline-block px-4 sm:px-6 py-2 sm:py-3 bg-gradient-to-r from-pink-600 to-pink-500 text-white rounded-full font-semibold text-xs sm:text-base shadow-lg hover:from-pink-500 hover:to-pink-600 transition w-full sm:w-auto text-center">Back to Home</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
