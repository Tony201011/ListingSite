<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Read-Only Access</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="bg-gray-900 text-gray-100 font-sans min-h-screen flex flex-col">
    <div class="flex-1 flex flex-col justify-center items-center">
        <div class="bg-gray-800/90 rounded-3xl shadow-2xl p-6 sm:p-10 max-w-lg w-full text-center mx-4">
            <div class="flex flex-col items-center">
                <div class="flex h-20 w-20 items-center justify-center rounded-full border-2 border-amber-400 bg-amber-500/20 text-amber-300 mb-6">
                    <i class="fa-solid fa-eye text-3xl"></i>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-amber-400 mb-2">Read-Only Access</h1>
                <h2 class="text-lg sm:text-xl font-bold text-white mb-4">Action Not Permitted</h2>
                <p class="text-sm text-gray-300 mb-6">
                    Your reviewer account has <strong class="text-amber-300">read-only access</strong>.
                    This action is not permitted for reviewer accounts.
                </p>
                <a href="javascript:history.back()"
                   class="inline-block px-6 py-2 bg-amber-500 text-white rounded-full font-semibold text-sm hover:bg-amber-600 transition">
                    &larr; Go Back
                </a>
            </div>
        </div>
    </div>
</body>
</html>
