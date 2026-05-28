@extends('layouts.frontend')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css">
<link rel="stylesheet" href="{{ asset('css/quill-editor.css') }}">
<link rel="stylesheet" href="{{ asset('profile/css/edit-profile.css') }}">
@endpush

@section('content')
<div
    class="bg-white min-h-screen py-10 px-4"
    x-data="editProfileForm({
        initial: {
            name: @js(old('name', $profile_name ?? '')),
            email: @js(old('email', $user->email ?? '')),
            phone: @js(old('phone', $profile_phone ?? '')),
            introduction_line: @js(old('introduction_line', $profile->introduction_line ?? '')),
            suburb: @js(old('suburb', $profile->suburb ?? '')),
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

            suburbSelected: @js((bool) old('suburb', $profile->suburb ?? '')),
            serverErrors: @js($errors->messages()),
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

        <form method="POST" @submit.prevent="submitForm" id="editProfileForm" autocomplete="off" class="space-y-8">
            @csrf


            <div class="bg-white border border-gray-200 rounded-2xl p-6 md:p-8 shadow-sm">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Basic information</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div data-field="name">
                        <label class="block font-semibold text-[#e04ecb] mb-1">Profile name</label>
                        <input
                            name="name"
                            type="text"
                            x-model="name"
                            :class="fieldErrors.name ? 'border-red-500 focus:ring-red-500' : 'border-gray-400 focus:ring-[#e04ecb]'"
                            class="w-full px-4 py-3 border rounded-lg text-gray-900 font-medium focus:ring-2 focus:border-transparent transition"
                            placeholder="e.g. Jenny"
                        >
                        <p x-show="fieldErrors.name" x-text="fieldErrors.name" class="mt-1 text-sm text-red-600"></p>
                    </div>

                    <div>
                        <label class="block font-semibold text-[#e04ecb] mb-1">Email</label>
                        <input
                            name="email"
                            type="email"
                            x-model="email"
                            readonly
                            class="w-full px-4 py-3 border border-gray-400 rounded-lg text-gray-900 font-medium bg-gray-100 cursor-not-allowed transition"
                        >
                    </div>

                    <div class="relative" data-field="suburb">
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
                            :class="fieldErrors.suburb ? 'border-red-500 focus:ring-red-500' : 'border-gray-400 focus:ring-[#e04ecb]'"
                            class="w-full px-4 py-3 border rounded-lg text-gray-900 font-medium focus:ring-2 focus:border-transparent transition"
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

                        <p x-show="fieldErrors.suburb" x-text="fieldErrors.suburb" class="mt-1 text-sm text-red-600"></p>
                        <p x-show="!fieldErrors.suburb" class="text-sm text-gray-600 mt-1">Primary work suburb (select from list while typing)</p>
                    </div>

                    <div>
                        <label class="block font-semibold text-[#e04ecb] mb-1">Mobile number <span class="text-gray-400 font-normal">(optional)</span></label>
                        <input
                            name="phone"
                            type="tel"
                            x-model="phone"
                            class="w-full px-4 py-3 border border-gray-400 rounded-lg text-gray-900 font-medium focus:ring-2 focus:ring-[#e04ecb] focus:border-transparent transition"
                            placeholder="e.g. 0400 000 000"
                        >
                    </div>
                </div>

                <div class="mt-6" data-field="introduction_line">
                    <label class="block font-semibold text-[#e04ecb] mb-1">Introduction line</label>

                    <input
                        id="introduction_line_input"
                        type="hidden"
                        name="introduction_line"
                        :value="introduction_line"
                        x-ref="introductionLineInput"
                    >

                    <div
                        id="introduction_line_editor"
                        :class="fieldErrors.introduction_line ? 'border border-red-500 rounded-lg' : ''"
                        class="w-full"
                    ></div>
                    <p x-show="fieldErrors.introduction_line" x-text="fieldErrors.introduction_line" class="mt-1 text-sm text-red-600"></p>
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

                <input
                    id="profile_text_input"
                    type="hidden"
                    name="profile_text"
                    :value="profile_text"
                    x-ref="profileTextInput"
                >

                <div
                    id="profile_text_editor"
                    data-field="profile_text"
                    :class="fieldErrors.profile_text ? 'border border-red-500 rounded-lg' : ''"
                    class="w-full"
                ></div>
                <p x-show="fieldErrors.profile_text" x-text="fieldErrors.profile_text" class="mt-1 text-sm text-red-600"></p>
            </div>

            <div class="bg-white border border-gray-200 rounded-2xl p-6 md:p-8 shadow-sm">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Your stats</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div data-field="age_group">
                        <label class="block font-semibold text-[#e04ecb] mb-1">Age group</label>
                        <select name="age_group" x-model="age_group"
                            :class="fieldErrors.age_group ? 'border-red-500 focus:ring-red-500' : 'border-gray-400 focus:ring-[#e04ecb]'"
                            class="w-full px-4 py-3 border rounded-lg text-gray-900 font-medium focus:ring-2 focus:border-transparent bg-white">
                            <option value="">- Select age -</option>
                            @foreach($ageGroupOptions ?? [] as $id => $label)
                                <option value="{{ $id }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <p x-show="fieldErrors.age_group" x-text="fieldErrors.age_group" class="mt-1 text-sm text-red-600"></p>
                    </div>

                    <div data-field="hair_color">
                        <label class="block font-semibold text-[#e04ecb] mb-1">Hair color</label>
                        <select name="hair_color" x-model="hair_color"
                            :class="fieldErrors.hair_color ? 'border-red-500 focus:ring-red-500' : 'border-gray-400 focus:ring-[#e04ecb]'"
                            class="w-full px-4 py-3 border rounded-lg text-gray-900 font-medium focus:ring-2 focus:border-transparent bg-white">
                            <option value="">- Select -</option>
                            @foreach($hairColorOptions ?? [] as $id => $label)
                                <option value="{{ $id }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <p x-show="fieldErrors.hair_color" x-text="fieldErrors.hair_color" class="mt-1 text-sm text-red-600"></p>
                    </div>

                    <div data-field="hair_length">
                        <label class="block font-semibold text-[#e04ecb] mb-1">Hair length</label>
                        <select name="hair_length" x-model="hair_length"
                            :class="fieldErrors.hair_length ? 'border-red-500 focus:ring-red-500' : 'border-gray-400 focus:ring-[#e04ecb]'"
                            class="w-full px-4 py-3 border rounded-lg text-gray-900 font-medium focus:ring-2 focus:border-transparent bg-white">
                            <option value="">- Select -</option>
                            @foreach($hairLengthOptions ?? [] as $id => $label)
                                <option value="{{ $id }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <p x-show="fieldErrors.hair_length" x-text="fieldErrors.hair_length" class="mt-1 text-sm text-red-600"></p>
                    </div>

                    <div data-field="ethnicity">
                        <label class="block font-semibold text-[#e04ecb] mb-1">Ethnicity</label>
                        <select name="ethnicity" x-model="ethnicity"
                            :class="fieldErrors.ethnicity ? 'border-red-500 focus:ring-red-500' : 'border-gray-400 focus:ring-[#e04ecb]'"
                            class="w-full px-4 py-3 border rounded-lg text-gray-900 font-medium focus:ring-2 focus:border-transparent bg-white">
                            <option value="">- Select -</option>
                            @foreach($ethnicityOptions ?? [] as $id => $label)
                                <option value="{{ $id }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <p x-show="fieldErrors.ethnicity" x-text="fieldErrors.ethnicity" class="mt-1 text-sm text-red-600"></p>
                    </div>

                    <div data-field="body_type">
                        <label class="block font-semibold text-[#e04ecb] mb-1">Body type</label>
                        <select name="body_type" x-model="body_type"
                            :class="fieldErrors.body_type ? 'border-red-500 focus:ring-red-500' : 'border-gray-400 focus:ring-[#e04ecb]'"
                            class="w-full px-4 py-3 border rounded-lg text-gray-900 font-medium focus:ring-2 focus:border-transparent bg-white">
                            <option value="">- Select -</option>
                            @foreach($bodyTypeOptions ?? [] as $id => $label)
                                <option value="{{ $id }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <p x-show="fieldErrors.body_type" x-text="fieldErrors.body_type" class="mt-1 text-sm text-red-600"></p>
                    </div>

                    <div data-field="bust_size">
                        <label class="block font-semibold text-[#e04ecb] mb-1">Bust size</label>
                        <select name="bust_size" x-model="bust_size"
                            :class="fieldErrors.bust_size ? 'border-red-500 focus:ring-red-500' : 'border-gray-400 focus:ring-[#e04ecb]'"
                            class="w-full px-4 py-3 border rounded-lg text-gray-900 font-medium focus:ring-2 focus:border-transparent bg-white">
                            <option value="">- Select -</option>
                            @foreach($bustSizeOptions ?? [] as $id => $label)
                                <option value="{{ $id }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <p x-show="fieldErrors.bust_size" x-text="fieldErrors.bust_size" class="mt-1 text-sm text-red-600"></p>
                    </div>

                    <div data-field="your_length">
                        <label class="block font-semibold text-[#e04ecb] mb-1">Your length</label>
                        <select name="your_length" x-model="your_length"
                            :class="fieldErrors.your_length ? 'border-red-500 focus:ring-red-500' : 'border-gray-400 focus:ring-[#e04ecb]'"
                            class="w-full px-4 py-3 border rounded-lg text-gray-900 font-medium focus:ring-2 focus:border-transparent bg-white">
                            <option value="">- Select -</option>
                            @foreach($yourLengthOptions ?? [] as $id => $label)
                                <option value="{{ $id }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <p x-show="fieldErrors.your_length" x-text="fieldErrors.your_length" class="mt-1 text-sm text-red-600"></p>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-2xl p-6 md:p-8 shadow-sm">
                <h2 class="text-xl font-semibold text-gray-900 mb-2">Tags that describe you</h2>
                <p class="text-gray-600 text-sm mb-6">These tags help clients find you. Click to select.</p>

                <div class="space-y-6">
                    <div data-field="primary_identity">
                        <h3 class="font-semibold text-[#e04ecb] mb-3">Primary identity <span class="text-[#e04ecb] text-sm">(select one)</span></h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach($primaryTags as $tag)
                                <span
                                    class="tag-pill px-4 py-2 bg-gray-200 text-gray-800 rounded-full text-sm cursor-pointer hover:bg-[#e04ecb] hover:text-white transition"
                                    :class="{ 'selected': primaryIdentity.includes(@js($tag)) }"
                                    @click="toggleTag('primaryIdentity', @js($tag), $event)"
                                >
                                    {{ $tag }}
                                </span>
                            @endforeach
                        </div>
                        <p x-show="fieldErrors.primary_identity" x-text="fieldErrors.primary_identity" class="mt-2 text-sm text-red-600"></p>
                    </div>

                    <div data-field="attributes">
                        <h3 class="font-semibold text-[#e04ecb] mb-3">Attributes <span class="text-[#e04ecb] text-sm">(multiple allowed)</span></h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach($attrTags as $tag)
                                <span
                                    class="tag-pill px-4 py-2 bg-gray-200 text-gray-800 rounded-full text-sm cursor-pointer hover:bg-[#e04ecb] hover:text-white transition"
                                    :class="{ 'selected': attributes.includes(@js($tag)) }"
                                    @click="toggleTag('attributes', @js($tag), $event)"
                                >
                                    {{ $tag }}
                                </span>
                            @endforeach
                        </div>
                        <p x-show="fieldErrors.attributes" x-text="fieldErrors.attributes" class="mt-2 text-sm text-red-600"></p>
                    </div>

                    <div data-field="services_style">
                        <h3 class="font-semibold text-[#e04ecb] mb-3">Services & style <span class="text-[#e04ecb] text-sm">(up to 12)</span></h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach($styleTags as $tag)
                                <span
                                    class="tag-pill px-4 py-2 bg-gray-200 text-gray-800 rounded-full text-sm cursor-pointer hover:bg-[#e04ecb] hover:text-white transition"
                                    :class="{ 'selected': servicesStyle.includes(@js($tag)) }"
                                    @click="toggleTag('servicesStyle', @js($tag), $event)"
                                >
                                    {{ $tag }}
                                </span>
                            @endforeach
                        </div>
                        <p x-show="fieldErrors.services_style" x-text="fieldErrors.services_style" class="mt-2 text-sm text-red-600"></p>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-2xl p-6 md:p-8 shadow-sm">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Services you provide</h2>
                <p class="text-gray-600 text-sm mb-4">Check all that apply</p>

                <div data-field="services_provided" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                    @foreach($services as $service)
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                type="checkbox"
                                name="services_provided[]"
                                value="{{ $service }}"
                                class="w-5 h-5 text-[#e04ecb] rounded border-gray-400 focus:ring-[#e04ecb]"
                                :checked="services_provided.includes(@js($service))"
                                @change="toggleService(@js($service))"
                            >
                            <span class="text-gray-800 text-sm">{{ $service }}</span>
                        </label>
                    @endforeach
                </div>
                <p x-show="fieldErrors.services_provided" x-text="fieldErrors.services_provided" class="mt-2 text-sm text-red-600"></p>
            </div>

            <div class="bg-white border border-gray-200 rounded-2xl p-6 md:p-8 shadow-sm">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Availability & contact</h2>

                <div class="space-y-4">
                    <div data-field="availability">
                        <label class="block font-semibold text-[#e04ecb] mb-2">Are you available for:</label>
                        <div class="flex flex-wrap gap-4">
                            @foreach($availabilityOptions ?? [] as $option)
                                <label class="flex items-center gap-2">
                                    <input type="radio" name="availability" value="{{ $option }}" class="w-4 h-4 text-[#e04ecb] border-gray-400" x-model="availability">
                                    <span class="text-gray-800">{{ $option }}</span>
                                </label>
                            @endforeach
                        </div>
                        <p x-show="fieldErrors.availability" x-text="fieldErrors.availability" class="mt-1 text-sm text-red-600"></p>
                    </div>

                    <div data-field="contact_method">
                        <label class="block font-semibold text-[#e04ecb] mb-2">How can people contact you?</label>
                        <p class="text-sm text-gray-600 mb-2">Email enquiries will be sent to: {{ $contactEmail ?? 'Not configured' }}</p>
                        <div class="flex flex-wrap gap-4">
                            @foreach($contactMethodOptions ?? [] as $option)
                                <label class="flex items-center gap-2">
                                    <input type="radio" name="contact_method" value="{{ $option }}" class="w-4 h-4 text-[#e04ecb] border-gray-400" x-model="contact_method">
                                    <span class="text-gray-800">{{ $option }}</span>
                                </label>
                            @endforeach
                        </div>
                        <p x-show="fieldErrors.contact_method" x-text="fieldErrors.contact_method" class="mt-1 text-sm text-red-600"></p>
                    </div>

                    <div data-field="phone_contact">
                        <label class="block font-semibold text-[#e04ecb] mb-2">Phone contact preferences</label>
                        <div class="flex flex-wrap gap-4">
                            @foreach($phoneContactOptions ?? [] as $option)
                                <label class="flex items-center gap-2">
                                    <input type="radio" name="phone_contact" value="{{ $option }}" class="w-4 h-4 text-[#e04ecb] border-gray-400" x-model="phone_contact">
                                    <span class="text-gray-800">{{ $option }}</span>
                                </label>
                            @endforeach
                        </div>
                        <p x-show="fieldErrors.phone_contact" x-text="fieldErrors.phone_contact" class="mt-1 text-sm text-red-600"></p>
                    </div>

                    <div data-field="time_waster">
                        <label class="block font-semibold text-[#e04ecb] mb-2">Use time waster shield for SMS?</label>
                        <div class="flex gap-4">
                            @foreach($timeWasterOptions ?? [] as $option)
                                <label class="flex items-center gap-2">
                                    <input type="radio" name="time_waster" value="{{ $option }}" class="w-4 h-4 text-[#e04ecb] border-gray-400" x-model="time_waster">
                                    <span class="text-gray-800">{{ $option }}</span>
                                </label>
                            @endforeach
                        </div>
                        <p x-show="fieldErrors.time_waster" x-text="fieldErrors.time_waster" class="mt-1 text-sm text-red-600"></p>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-2xl p-6 md:p-8 shadow-sm">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Optional links</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block font-semibold text-[#e04ecb] mb-1">Twitter handle</label>
                        <input name="twitter_handle" type="text" x-model="twitter_handle" class="w-full px-4 py-3 border border-gray-400 rounded-lg text-gray-900 font-medium focus:ring-2 focus:ring-[#e04ecb] focus:border-transparent">
                    </div>

                    <div data-field="website">
                        <label class="block font-semibold text-[#e04ecb] mb-1">Website</label>
                        <input name="website" type="text" x-model="website"
                            :class="fieldErrors.website ? 'border-red-500 focus:ring-red-500' : 'border-gray-400 focus:ring-[#e04ecb]'"
                            class="w-full px-4 py-3 border rounded-lg text-gray-900 font-medium focus:ring-2 focus:border-transparent">
                        <p x-show="fieldErrors.website" x-text="fieldErrors.website" class="mt-1 text-sm text-red-600"></p>
                    </div>

                    <div>
                        <label class="block font-semibold text-[#e04ecb] mb-1">OnlyFans username</label>
                        <input name="onlyfans_username" type="text" x-model="onlyfans_username" class="w-full px-4 py-3 border border-gray-400 rounded-lg text-gray-900 font-medium focus:ring-2 focus:ring-[#e04ecb] focus:border-transparent">
                    </div>
                </div>
            </div>

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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<script src="{{ asset('profile/js/edit-profile-form.js') }}"></script>
@endpush
