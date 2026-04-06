@extends('layouts.frontend')

@section('content')
<div
    class="bg-white min-h-screen py-10 px-4"
    x-data="editProfileForm({
        initial: {
            name: @js(old('name', $user->name ?? '')),
            mobile: @js(old('mobile', $user->mobile ?? '')),
            introduction_line: @js(old('introduction_line', $profile->introduction_line ?? '')),
            suburb: @js(old('suburb', $user->suburb ?? '')),
            profile_text: @js(old('profile_text', $profile->profile_text ?? '')),

            age_group: @js(old('age_group', $selected['age_group'] ?? '')),
            hair_color: @js(old('hair_color', $selected['hair_color'] ?? '')),
            hair_length: @js(old('hair_length', $selected['hair_length'] ?? '')),
            ethnicity: @js(old('ethnicity', $selected['ethnicity'] ?? '')),
            body_type: @js(old('body_type', $selected['body_type'] ?? '')),
            bust_size: @js(old('bust_size', $selected['bust_size'] ?? '')),
            your_length: @js(old('your_length', $selected['your_length'] ?? '')),

            primaryIdentity: @js(old('primary_identity', $selected['primary_identity'] ?? [])),
            attributes: @js(old('attributes', $selected['attributes'] ?? [])),
            servicesStyle: @js(old('services_style', $selected['services_style'] ?? [])),
            services_provided: @js(old('services_provided', $selected['services_provided'] ?? [])),

            availability: @js(old('availability', $selected['availability'] ?? '')),
            contact_method: @js(old('contact_method', $selected['contact_method'] ?? '')),
            phone_contact: @js(old('phone_contact', $selected['phone_contact'] ?? '')),
            time_waster: @js(old('time_waster', $selected['time_waster'] ?? '')),

            twitter_handle: @js(old('twitter_handle', $profile->twitter_handle ?? '')),
            website: @js(old('website', $profile->website ?? '')),
            onlyfans_username: @js(old('onlyfans_username', $profile->onlyfans_username ?? '')),

            suburbSelected: @js((bool) old('suburb', $user->suburb ?? '')),
            serverErrors: @js($errors->all()),
        },
        submitUrl: @js(url()->current()),
        csrfToken: @js(csrf_token())
    })"
>
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-8 border-l-6 border-[#e04ecb] pl-4">
            Edit your profile
        </h1>

        <button
            type="button"
            onclick="window.history.back()"
            class="inline-flex items-center text-[#e04ecb] hover:text-[#c13ab0] transition-colors mb-6 text-sm font-medium bg-transparent border-0 cursor-pointer"
        >
            <span class="mr-1">&lt;</span> back
        </button>

        <form method="POST" @submit.prevent="submitForm" id="editProfileForm" class="space-y-8">
            @csrf

            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-800 rounded-2xl p-4">
                    <p class="font-semibold">Please fix the following errors:</p>
                    <ul class="mt-2 list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <template x-if="errors.length > 0">
                <div class="bg-red-50 border border-red-200 text-red-800 rounded-2xl p-4">
                    <p class="font-semibold">Please fix the following errors:</p>
                    <ul class="mt-2 list-disc list-inside text-sm">
                        <template x-for="(error, index) in errors" :key="index">
                            <li x-text="error"></li>
                        </template>
                    </ul>
                </div>
            </template>

            <div class="bg-white border border-gray-200 rounded-2xl p-6 md:p-8 shadow-sm">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Basic information</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block font-semibold text-[#e04ecb] mb-1">Your name</label>
                        <input
                            name="name"
                            type="text"
                            x-model="name"
                            readonly
                            class="w-full px-4 py-3 border border-gray-400 rounded-lg text-gray-900 font-medium bg-gray-100 cursor-not-allowed transition"
                        >
                    </div>

                    <div>
                        <label class="block font-semibold text-[#e04ecb] mb-1">Mobile number</label>
                        <input
                            name="mobile"
                            type="text"
                            x-model="mobile"
                            readonly
                            class="w-full px-4 py-3 border border-gray-400 rounded-lg text-gray-900 font-medium bg-gray-100 cursor-not-allowed transition"
                        >
                    </div>
                </div>

                <div class="mt-6">
                    <label class="block font-semibold text-[#e04ecb] mb-1">Introduction line</label>
                    <textarea
                        name="introduction_line"
                        x-model="introduction_line"
                        rows="4"
                        class="w-full px-4 py-3 border border-gray-400 rounded-lg text-gray-900 focus:ring-2 focus:ring-[#e04ecb] focus:border-transparent transition"
                        placeholder="Write your introduction line here..."
                    ></textarea>
                </div>

                <div class="mt-6 relative">
                    <label class="block font-semibold text-[#e04ecb] mb-1">Your suburb</label>
                    <input
                        name="suburb"
                        type="text"
                        x-model="suburb"
                        @input="handleSuburbInput()"
                        @blur="handleSuburbBlur()"
                        @focus="if (suburb.length >= 2 && searchResults.length > 0) showResults = true"
                        autocomplete="off"
                        placeholder="Start typing your suburb..."
                        class="w-full px-4 py-3 border border-gray-400 rounded-lg text-gray-900 font-medium focus:ring-2 focus:ring-[#e04ecb] focus:border-transparent transition"
                    >

                    <div
                        x-show="showResults && searchResults.length > 0"
                        x-cloak
                        x-transition
                        class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg max-h-60 overflow-y-auto"
                        style="display: none;"
                    >
                        <template x-for="(item, index) in searchResults" :key="`${item.suburb}-${item.state}-${item.postcode}-${index}`">
                            <div
                                @mousedown.prevent="selectSuburb(item)"
                                class="px-4 py-2 hover:bg-pink-50 cursor-pointer text-gray-800"
                            >
                                <span x-text="`${item.suburb}, ${item.state} ${item.postcode}`"></span>
                            </div>
                        </template>
                    </div>

                    <div
                        x-show="showResults && searching"
                        x-cloak
                        class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg p-4 text-center text-gray-500"
                        style="display: none;"
                    >
                        Searching...
                    </div>

                    <p class="text-sm text-gray-600 mt-1">Primary work suburb (select from list while typing)</p>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-2xl p-6 md:p-8 shadow-sm">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Your profile text</h2>

                <div class="bg-pink-50 border-l-4 border-[#e04ecb] p-4 text-sm text-gray-800 mb-4">
                    <p>
                        It is illegal in Vic & QLD to describe your sexual services in details, you also cannot refer to the term massage.
                        In QLD you cannot advertise 'doubles'. If you are in VIC please do not forget to mention your SWA Licence number
                    </p>
                </div>

                <p class="text-gray-800 mb-3">
                    You can use our special features for
                    <a href="{{ url('/my-rate') }}" class="text-[#e04ecb] underline font-medium">my rates</a> and
                    <a href="{{ url('/my-availability') }}" class="text-[#e04ecb] underline font-medium">my availability</a>,
                    or you can type them down here.
                </p>

                <textarea
                    name="profile_text"
                    x-model="profile_text"
                    rows="10"
                    class="w-full px-4 py-3 border border-gray-400 rounded-lg text-gray-900 focus:ring-2 focus:ring-[#e04ecb] focus:border-transparent transition"
                    placeholder="Write your profile description here..."
                ></textarea>
            </div>

            {{-- keep the rest of your form exactly the same --}}

            <div class="pt-4">
                <button
                    type="submit"
                    :disabled="submitting"
                    class="w-full md:w-auto px-10 py-4 bg-gradient-to-r from-[#e04ecb] to-[#c13ab0] text-white font-bold text-lg rounded-full shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition focus:outline-none focus:ring-2 focus:ring-[#e04ecb] focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span x-show="!submitting">Save your profile</span>
                    <span x-show="submitting">Saving...</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
    [x-cloak] { display: none !important; }

    .tag-pill.selected {
        background-color: #e04ecb !important;
        color: #ffffff !important;
    }

    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-3px); }
        75% { transform: translateX(3px); }
    }

    .shake {
        animation: shake 0.3s ease-in-out;
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/alpinejs" defer></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('profile/js/edit-profile-form.js') }}"></script>
@endpush
