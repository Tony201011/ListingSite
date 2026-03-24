<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Referrals</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<div class="max-w-2xl mx-auto mt-10 bg-white shadow-lg rounded-xl p-6">

    <!-- Title -->
    <h2 class="text-2xl font-bold mb-4 text-gray-800">
        Invite & Earn 🎉
    </h2>

    <!-- Referral Link -->
    <div class="mb-4">
        <label class="block text-sm text-gray-600 mb-1">
            Your Referral Link
        </label>

        <div class="flex gap-2">
            <input id="referralInput"
                   type="text"
                   value="{{ $referralLink }}"
                   readonly
                   class="flex-1 px-3 py-2 border rounded-lg bg-gray-50 text-gray-700">

            <button onclick="copyLink()"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Copy
            </button>
        </div>
    </div>

    <!-- Social Share -->
    <div class="mb-5">
        <p class="text-sm text-gray-600 mb-2">Share your link</p>

        <div class="flex gap-2">
            <!-- WhatsApp -->
            <a href="https://wa.me/?text={{ urlencode($referralLink) }}"
               target="_blank"
               class="flex-1 text-center px-3 py-2 bg-green-50 text-green-700 rounded-lg hover:bg-green-100">
                WhatsApp
            </a>

            <!-- Facebook -->
            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($referralLink) }}"
               target="_blank"
               class="flex-1 text-center px-3 py-2 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100">
                Facebook
            </a>

            <!-- Twitter -->
            <a href="https://twitter.com/intent/tweet?url={{ urlencode($referralLink) }}"
               target="_blank"
               class="flex-1 text-center px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                Twitter
            </a>
        </div>
    </div>

    <!-- Social Profiles -->
    <div class="mb-5">
        <p class="text-sm text-gray-600 mb-2">Follow us</p>

        <div class="flex gap-2">
            <a href="https://instagram.com/yourprofile"
               target="_blank"
               class="flex-1 text-center px-3 py-2 bg-pink-50 text-pink-700 rounded-lg hover:bg-pink-100">
                Instagram
            </a>

            <a href="https://linkedin.com/in/yourprofile"
               target="_blank"
               class="flex-1 text-center px-3 py-2 bg-blue-50 text-blue-800 rounded-lg hover:bg-blue-100">
                LinkedIn
            </a>

            <a href="https://youtube.com/@yourchannel"
               target="_blank"
               class="flex-1 text-center px-3 py-2 bg-red-50 text-red-700 rounded-lg hover:bg-red-100">
                YouTube
            </a>
        </div>
    </div>

    <!-- Referral Stats (Optional) -->
    <div class="mt-6">
        <h3 class="text-lg font-semibold mb-2 text-gray-800">
            Your Stats
        </h3>

        <div class="grid grid-cols-2 gap-4">
            <div class="bg-gray-50 p-4 rounded-lg text-center">
                <p class="text-xl font-bold">{{ $totalReferrals ?? 0 }}</p>
                <p class="text-sm text-gray-500">Total Referrals</p>
            </div>

            <div class="bg-gray-50 p-4 rounded-lg text-center">
                <p class="text-xl font-bold">{{ $earned ?? 0 }}</p>
                <p class="text-sm text-gray-500">Earnings</p>
            </div>
        </div>
    </div>

</div>

<!-- Copy Script -->
<script>
    function copyLink() {
        const input = document.getElementById('referralInput');
        input.select();
        input.setSelectionRange(0, 99999);
        document.execCommand('copy');

        alert('Referral link copied!');
    }
</script>

</body>
</html>
