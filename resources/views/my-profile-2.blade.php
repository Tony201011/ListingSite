@extends('layouts.frontend')

@section('content')
<div class="bg-white min-h-screen py-10 px-4">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-8 border-l-6 border-[#e04ecb] pl-4">
            Edit your profile
        </h1>

        <!-- Profile Form -->
        <form class="space-y-8">
            <!-- ===== BASIC INFO CARD ===== -->
            <div class="bg-white border border-gray-200 rounded-2xl p-6 md:p-8 shadow-sm">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">
                    Basic information
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block font-semibold text-[#e04ecb] mb-1">Your name</label>
                        <input type="text" value="Sourabh wadhwa" class="w-full px-4 py-3 border border-gray-400 rounded-lg text-gray-900 font-medium focus:ring-2 focus:ring-[#e04ecb] focus:border-transparent transition">
                    </div>
                    <div>
                        <label class="block font-semibold text-[#e04ecb] mb-1">Mobile number</label>
                        <input type="text" value="0415573077" class="w-full px-4 py-3 border border-gray-400 rounded-lg text-gray-900 font-medium focus:ring-2 focus:ring-[#e04ecb] focus:border-transparent transition">
                    </div>
                </div>

                <div class="mt-6">
                    <label class="block font-semibold text-[#e04ecb] mb-1">Introduction line</label>
                    <textarea rows="2" class="w-full px-4 py-3 border border-gray-400 rounded-lg text-gray-900 font-medium focus:ring-2 focus:ring-[#e04ecb] focus:border-transparent transition">I am Sourabh Wadhwa, a 24-year-old student from Mumbai...</textarea>
                </div>

                <div class="mt-6">
                    <label class="block font-semibold text-[#e04ecb] mb-1">Your suburb</label>
                    <input type="text" value="Melbourne VIC" class="w-full px-4 py-3 border border-gray-400 rounded-lg text-gray-900 font-medium focus:ring-2 focus:ring-[#e04ecb] focus:border-transparent transition">
                    <p class="text-sm text-gray-600 mt-1">Primary work suburb (select from list while typing)</p>
                </div>
            </div>

            <!-- ===== PROFILE TEXT CARD ===== -->
            <div class="bg-white border border-gray-200 rounded-2xl p-6 md:p-8 shadow-sm">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">
                    Your profile text
                </h2>

                <div class="bg-pink-50 border-l-4 border-[#e04ecb] p-4 text-sm text-gray-800 mb-4">
                    <p>It is illegal in Vic & QLD to describe your sexual services in details, you also cannot refer to the term massage. In QLD you cannot advertise 'doubles'. If you are in VIC please do not forget to mention your SWA Licence number</p>
                </div>

                <p class="text-gray-800 mb-3">
                    You can use our special features for
                    <a href="{{ url('/my-rate') }}" class="text-[#e04ecb] underline font-medium">my rates</a> and
                    <a href="{{ url('/my-availability') }}" class="text-[#e04ecb] underline font-medium">my availability</a>,
                    or you can type them down here.
                </p>

                <!-- Text editor toolbar (simplified) -->
                <div class="flex items-center gap-4 p-2 bg-gray-100 border border-gray-400 rounded-t-lg text-gray-700">
                    <span class="font-serif text-xl">✎</span>
                    <span class="font-bold">B</span>
                    <span class="italic">I</span>
                    <span class="underline">U</span>
                    <span class="line-through">S</span>
                    <span>16 ▼</span>
                    <span>▦</span>
                    <span>▤</span>
                </div>
                <textarea rows="6" class="w-full px-4 py-3 border border-t-0 border-gray-400 rounded-b-lg text-gray-900 font-medium focus:ring-2 focus:ring-[#e04ecb] focus:border-transparent transition" placeholder="Write your profile description here..."></textarea>
            </div>

            <!-- ===== STATS CARD ===== -->
            <div class="bg-white border border-gray-200 rounded-2xl p-6 md:p-8 shadow-sm">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">
                    Your stats
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <label class="block font-semibold text-[#e04ecb] mb-1">Age group</label>
                        <select class="w-full px-4 py-3 border border-gray-400 rounded-lg text-gray-900 font-medium focus:ring-2 focus:ring-[#e04ecb] focus:border-transparent bg-white">
                            <option>- Select age -</option>
                            @foreach(($ageGroupOptions ?? []) as $option)
                                <option>{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-[#e04ecb] mb-1">Hair color</label>
                        <select class="w-full px-4 py-3 border border-gray-400 rounded-lg text-gray-900 font-medium focus:ring-2 focus:ring-[#e04ecb] focus:border-transparent bg-white">
                            <option>- Select -</option>
                            @foreach(($hairColorOptions ?? []) as $option)
                                <option>{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-[#e04ecb] mb-1">Hair length</label>
                        <select class="w-full px-4 py-3 border border-gray-400 rounded-lg text-gray-900 font-medium focus:ring-2 focus:ring-[#e04ecb] focus:border-transparent bg-white">
                            <option>- Select -</option>
                            @foreach(($hairLengthOptions ?? []) as $option)
                                <option>{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-[#e04ecb] mb-1">Ethnicity</label>
                        <select class="w-full px-4 py-3 border border-gray-400 rounded-lg text-gray-900 font-medium focus:ring-2 focus:ring-[#e04ecb] focus:border-transparent bg-white">
                            <option>- Select -</option>
                            @foreach(($ethnicityOptions ?? []) as $option)
                                <option>{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-[#e04ecb] mb-1">Body type</label>
                        <select class="w-full px-4 py-3 border border-gray-400 rounded-lg text-gray-900 font-medium focus:ring-2 focus:ring-[#e04ecb] focus:border-transparent bg-white">
                            <option>- Select -</option>
                            @foreach(($bodyTypeOptions ?? []) as $option)
                                <option>{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-[#e04ecb] mb-1">Bust size</label>
                        <select class="w-full px-4 py-3 border border-gray-400 rounded-lg text-gray-900 font-medium focus:ring-2 focus:ring-[#e04ecb] focus:border-transparent bg-white">
                            <option>- Select -</option>
                            @foreach(($bustSizeOptions ?? []) as $option)
                                <option>{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-[#e04ecb] mb-1">Your length</label>
                        <select class="w-full px-4 py-3 border border-gray-400 rounded-lg text-gray-900 font-medium focus:ring-2 focus:ring-[#e04ecb] focus:border-transparent bg-white">
                            <option>- Select -</option>
                            @foreach(($yourLengthOptions ?? []) as $option)
                                <option>{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- ===== TAGS CARD ===== -->
            <div class="bg-white border border-gray-200 rounded-2xl p-6 md:p-8 shadow-sm">
                <h2 class="text-xl font-semibold text-gray-900 mb-2">
                    Tags that describe you
                </h2>
                <p class="text-gray-600 text-sm mb-6">These tags help clients find you. Click to select.</p>

                <div class="space-y-6">
                    <div>
                        <h3 class="font-semibold text-[#e04ecb] mb-3">Primary identity <span class="text-[#e04ecb] text-sm">(select one)</span></h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach($primaryTags as $tag)
                                <span class="tag-pill px-4 py-2 bg-gray-200 text-gray-800 rounded-full text-sm cursor-pointer hover:bg-[#e04ecb] hover:text-white transition">{{ $tag }}</span>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <h3 class="font-semibold text-[#e04ecb] mb-3">Attributes <span class="text-[#e04ecb] text-sm">(multiple allowed)</span></h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach($attrTags as $tag)
                                <span class="tag-pill px-4 py-2 bg-gray-200 text-gray-800 rounded-full text-sm cursor-pointer hover:bg-[#e04ecb] hover:text-white transition">{{ $tag }}</span>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <h3 class="font-semibold text-[#e04ecb] mb-3">Services & style <span class="text-[#e04ecb] text-sm">(up to 12)</span></h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach($styleTags as $tag)
                                <span class="tag-pill px-4 py-2 bg-gray-200 text-gray-800 rounded-full text-sm cursor-pointer hover:bg-[#e04ecb] hover:text-white transition">{{ $tag }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== SERVICES CARD ===== -->
            <div class="bg-white border border-gray-200 rounded-2xl p-6 md:p-8 shadow-sm">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">
                    Services you provide
                </h2>
                <p class="text-gray-600 text-sm mb-4">Check all that apply</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                    @foreach($services as $service)
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" class="w-5 h-5 text-[#e04ecb] rounded border-gray-400 focus:ring-[#e04ecb]">
                            <span class="text-gray-800 text-sm">{{ $service }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- ===== AVAILABILITY CARD ===== -->
            <div class="bg-white border border-gray-200 rounded-2xl p-6 md:p-8 shadow-sm">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">
                    Availability & contact
                </h2>

                <div class="space-y-4">
                    <div>
                        <label class="block font-semibold text-[#e04ecb] mb-2">Are you available for:</label>
                        <div class="flex flex-wrap gap-4">
                            @foreach(($availabilityOptions ?? []) as $option)
                                <label class="flex items-center gap-2">
                                    <input type="radio" name="availability" value="{{ $option }}" class="w-4 h-4 text-[#e04ecb] border-gray-400">
                                    <span class="text-gray-800">{{ $option }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-[#e04ecb] mb-2">How can people contact you?</label>
                        <p class="text-sm text-gray-600 mb-2">Email enquiries will be sent to: {{ $contactEmail ?? 's8813w@gmail.com' }}</p>
                        <div class="flex flex-wrap gap-4">
                            @foreach(($contactMethodOptions ?? []) as $option)
                                <label class="flex items-center gap-2">
                                    <input type="radio" name="contact_method" value="{{ $option }}" class="w-4 h-4 text-[#e04ecb] border-gray-400">
                                    <span class="text-gray-800">{{ $option }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-[#e04ecb] mb-2">Phone contact preferences</label>
                        <div class="flex flex-wrap gap-4">
                            @foreach(($phoneContactOptions ?? []) as $option)
                                <label class="flex items-center gap-2">
                                    <input type="radio" name="phone_contact" value="{{ $option }}" class="w-4 h-4 text-[#e04ecb] border-gray-400">
                                    <span class="text-gray-800">{{ $option }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-[#e04ecb] mb-2">Use time waster shield for SMS?</label>
                        <div class="flex gap-4">
                            @foreach(($timeWasterOptions ?? []) as $option)
                                <label class="flex items-center gap-2">
                                    <input type="radio" name="time_waster" value="{{ $option }}" class="w-4 h-4 text-[#e04ecb] border-gray-400">
                                    <span class="text-gray-800">{{ $option }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== OPTIONAL SOCIAL LINKS ===== -->
            <div class="bg-white border border-gray-200 rounded-2xl p-6 md:p-8 shadow-sm">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">
                    Optional links
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block font-semibold text-[#e04ecb] mb-1">Twitter handle</label>
                        <input type="text" value="@yourtwittername" class="w-full px-4 py-3 border border-gray-400 rounded-lg text-gray-900 font-medium focus:ring-2 focus:ring-[#e04ecb] focus:border-transparent">
                    </div>
                    <div>
                        <label class="block font-semibold text-[#e04ecb] mb-1">Website</label>
                        <input type="text" value="https://example.com" class="w-full px-4 py-3 border border-gray-400 rounded-lg text-gray-900 font-medium focus:ring-2 focus:ring-[#e04ecb] focus:border-transparent">
                    </div>
                    <div>
                        <label class="block font-semibold text-[#e04ecb] mb-1">OnlyFans username</label>
                        <input type="text" value="@onlyfansusername" class="w-full px-4 py-3 border border-gray-400 rounded-lg text-gray-900 font-medium focus:ring-2 focus:ring-[#e04ecb] focus:border-transparent">
                    </div>
                </div>
            </div>

            <!-- ===== SAVE BUTTON ===== -->
            <div class="pt-4">
                <button type="submit" class="w-full md:w-auto px-10 py-4 bg-gradient-to-r from-[#e04ecb] to-[#c13ab0] text-white font-bold text-lg rounded-full shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition focus:outline-none focus:ring-2 focus:ring-[#e04ecb] focus:ring-offset-2">
                    Save your profile
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    /* Selected state for tag pills */
    .tag-pill.selected {
        background-color: #e04ecb !important;
        color: white !important;
    }
</style>

<script>
    // Simple script to toggle tag selection
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.tag-pill').forEach(pill => {
            pill.addEventListener('click', function() {
                this.classList.toggle('selected');
            });
        });
    });
</script>
@endsection
