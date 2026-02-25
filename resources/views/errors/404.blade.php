<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 Not Found</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        [x-cloak] { display: none !important; }
        .hero-gradient { background: linear-gradient(180deg, rgba(17,24,39,0.7) 0%, rgba(17,24,39,1) 100%); }
    </style>
</head>
<body class="bg-gray-900 text-gray-100 font-sans min-h-screen flex flex-col">
    <div class="flex-1 flex flex-col justify-center items-center">
        <div class="bg-gray-800/90 rounded-3xl shadow-2xl p-10 max-w-lg w-full text-center">
            <div class="flex flex-col items-center">
                <svg class="w-24 h-24 text-pink-500 mb-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 48 48">
                    <circle cx="24" cy="24" r="22" stroke="currentColor" stroke-width="4" fill="#fff"/>
                    <path d="M16 32l16-16M32 32L16 16" stroke-linecap="round"/>
                </svg>
                <h1 class="text-6xl font-extrabold text-purple-400 mb-2">404</h1>
                <h2 class="text-2xl font-bold text-white mb-4">Page Not Found</h2>
                <p class="text-gray-300 mb-8">Sorry, the page you are looking for could not be found.<br>It might have been removed, renamed, or did not exist in the first place.</p>
                <a href="/" class="inline-block px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-500 text-white rounded-full font-semibold shadow-lg hover:from-pink-500 hover:to-purple-600 transition">Go Home</a>
            </div>
        </div>
    </div>
</body>
</html>
