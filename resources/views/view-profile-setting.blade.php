@extends('layouts.frontend')

@section('content')
<div class="min-h-screen bg-gray-50 py-10 px-4 sm:px-6 lg:px-8" x-data="profileSettingPage()">
    <div class="max-w-6xl mx-auto">
        <button onclick="window.history.back()" class="inline-flex items-center text-[#e04ecb] hover:text-[#c13ab0] transition-colors mb-4 text-sm font-medium bg-transparent border-0 cursor-pointer">
            <span class="mr-1">&lt;</span> To dashboard
        </button>

        <div class="bg-red-500 text-white rounded-xl p-4 mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <p class="text-sm sm:text-base">Your profile is not yet verified. Verified profiles get a verified badge.</p>
            <a href="#" class="inline-flex items-center px-4 py-2 rounded-lg bg-white/20 hover:bg-white/30 text-sm font-semibold transition">Verify now</a>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6 mb-6">
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2.5 sm:gap-3">
                @php
                    $actions = [
                        ['label' => 'Edit profile', 'url' => url('/my-profile-2')],
                        ['label' => 'Hide profile', 'url' => url('/hide-profile')],
                        ['label' => 'Photos', 'url' => url('/photos')],
                        ['label' => 'Add photos', 'url' => url('/add-photo')],
                        ['label' => 'My videos', 'url' => url('/my-videos')],
                        ['label' => 'My rates', 'url' => url('/my-rate'), 'isNew' => true],
                        ['label' => 'My tours', 'url' => url('/my-tours')],
                        ['label' => 'Availability', 'url' => url('/my-availability')],
                        ['label' => 'Short URL', 'url' => url('/short-url')],
                        ['label' => 'Online now', 'url' => url('/online-now')],
                        ['label' => 'Available now', 'url' => url('/available-now')],
                        ['label' => 'Set & Forget', 'url' => url('/set-and-forget')],
                        ['label' => 'My Babe Rank', 'url' => url('/my-babe-rank')],
                        ['label' => 'Profile message', 'url' => url('/profile-message')],
                        ['label' => 'Help & FAQ', 'url' => route('faq'), 'isPrimary' => true],
                    ];
                @endphp

                @foreach($actions as $action)
                    <a href="{{ $action['url'] }}" class="inline-flex items-center justify-center px-3 py-2.5 rounded-lg border text-sm font-medium transition {{ !empty($action['isPrimary']) ? 'bg-pink-600 text-white border-pink-600 hover:bg-pink-700' : 'bg-white text-gray-700 border-gray-200 hover:border-pink-300 hover:text-pink-700 hover:bg-pink-50' }}">
                        {{ $action['label'] }}
                        @if(!empty($action['isNew']))
                            <span class="ml-1 text-[10px] uppercase font-bold text-red-500 align-super">new</span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8">
                    <div class="flex flex-wrap items-end gap-3 mb-1">
                        <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 tracking-tight">Sourabh Wadhwa</h1>
                        <span class="text-gray-500 font-medium">Melbourne VIC</span>
                    </div>
                    <p class="text-pink-600 font-medium mb-6">Sourabh Wadhwa</p>

                    <section class="mb-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-2">About me</h2>
                        <p class="text-gray-600 leading-relaxed">
                            It is illegal in Vic & QLD to describe your sexual services in details, you also cannot refer to the term massage.
                            In QLD you cannot advertise doubles. If you are in VIC please do not forget to mention your SWA Licence number.
                        </p>
                    </section>

                    <section class="mb-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-3">My stats</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm text-gray-700">
                            <div><span class="font-semibold text-pink-700">Age group:</span> 25 - 29</div>
                            <div><span class="font-semibold text-pink-700">Ethnicity:</span> Arabian</div>
                            <div><span class="font-semibold text-pink-700">Hair color:</span> Dark</div>
                            <div><span class="font-semibold text-pink-700">Hair length:</span> Short</div>
                            <div><span class="font-semibold text-pink-700">Body type:</span> Curvy</div>
                            <div><span class="font-semibold text-pink-700">Bust size:</span> Busty</div>
                            <div><span class="font-semibold text-pink-700">Length:</span> Average (164cm - 176cm)</div>
                        </div>

                        <div class="flex flex-wrap gap-2 mt-4">
                            <span class="px-3 py-1 rounded-full bg-pink-100 text-pink-700 text-xs font-semibold">milf</span>
                            <span class="px-3 py-1 rounded-full bg-pink-100 text-pink-700 text-xs font-semibold">heterosexual</span>
                            <span class="px-3 py-1 rounded-full bg-pink-100 text-pink-700 text-xs font-semibold">outfit requests welcome</span>
                        </div>
                    </section>

                    <section>
                        <h2 class="text-xl font-bold text-gray-900 mb-2">Contact me for</h2>
                        <ul class="space-y-1 text-gray-600">
                            <li>• Incalls only</li>
                            <li>• Social, Netflix, lunch & dinner dates</li>
                            <li>• Extended or overnight bookings</li>
                        </ul>
                    </section>
                </div>

                <div class="rounded-xl border border-pink-200 bg-pink-50 p-4 text-pink-700 font-semibold">
                    Verification pending
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <div class="flex items-start justify-between gap-4 mb-4">
                        <div>
                            <p class="text-sm text-gray-500">Phone</p>
                            <p class="text-2xl font-bold text-gray-900 leading-tight">0415 573 077</p>
                            <p class="text-sm text-gray-500 mt-1">I accept phone calls & SMS</p>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-pink-100 text-pink-700 flex items-center justify-center text-lg">📞</div>
                    </div>

                    <button type="button" @click="bookingOpen = true" class="w-full px-4 py-2.5 rounded-lg bg-pink-600 hover:bg-pink-700 text-white font-semibold transition">
                        Booking enquiries
                    </button>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <h3 class="text-lg font-bold text-gray-900 mb-3">My photos</h3>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="aspect-[3/4] rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-center text-xs text-gray-400">Photo 1</div>
                        <div class="aspect-[3/4] rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-center text-xs text-gray-400">Photo 2</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div x-show="bookingOpen" x-cloak x-transition.opacity class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" @click.self="bookingOpen = false">
        <div class="w-full max-w-xl bg-white rounded-2xl shadow-2xl p-5 sm:p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-900">Email booking enquiry</h2>
                <button type="button" @click="bookingOpen = false" class="text-gray-500 hover:text-gray-700 text-2xl leading-none">&times;</button>
            </div>

            <form class="space-y-3">
                <input type="text" placeholder="Your name" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                <input type="email" placeholder="Your email (required)" required class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                <input type="tel" placeholder="Your phone" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                <input type="text" placeholder="When / what time would you like to book" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                <input type="text" placeholder="What services are you interested in" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                <input type="text" placeholder="How long would you like to book" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                <input type="text" placeholder="Where would you like to meet" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                <textarea rows="3" placeholder="Any other comments" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:ring-2 focus:ring-pink-500 focus:border-transparent"></textarea>

                <div class="pt-2 flex gap-3">
                    <button type="submit" class="flex-1 px-4 py-2.5 rounded-lg bg-pink-600 hover:bg-pink-700 text-white font-semibold transition">Submit</button>
                    <button type="button" @click="bookingOpen = false" class="px-4 py-2.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold transition">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function profileSettingPage() {
        return {
            bookingOpen: false,
        };
    }
</script>
@endsection
