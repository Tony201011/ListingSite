{{-- @extends('layouts.frontend')

@section('content')
<div class="bg-white min-h-screen py-10 px-4"
     x-data="{
        // Basic info
        name: @js(old('name', $user->name ?? '')),
        mobile: @js(old('mobile', $user->mobile ?? '')),
        introduction_line: @js(old('introduction_line', $profile->introduction_line ?? '')),
        suburb: @js(old('suburb', $user->suburb ?? '')),
        profile_text: @js(old('profile_text', $profile->profile_text ?? '')),

        // Stats selects
        age_group: @js(old('age_group', $selected['age_group'] ?? '')),
        hair_color: @js(old('hair_color', $selected['hair_color'] ?? '')),
        hair_length: @js(old('hair_length', $selected['hair_length'] ?? '')),
        ethnicity: @js(old('ethnicity', $selected['ethnicity'] ?? '')),
        body_type: @js(old('body_type', $selected['body_type'] ?? '')),
        bust_size: @js(old('bust_size', $selected['bust_size'] ?? '')),
        your_length: @js(old('your_length', $selected['your_length'] ?? '')),

        // Tags
        primaryIdentity: @js(old('primary_identity', $selected['primary_identity'] ?? [])),
        attributes: @js(old('attributes', $selected['attributes'] ?? [])),
        servicesStyle: @js(old('services_style', $selected['services_style'] ?? [])),

        // Services provided
        services_provided: @js(old('services_provided', $selected['services_provided'] ?? [])),

        // Availability radios
        availability: @js(old('availability', $selected['availability'] ?? '')),
        contact_method: @js(old('contact_method', $selected['contact_method'] ?? '')),
        phone_contact: @js(old('phone_contact', $selected['phone_contact'] ?? '')),
        time_waster: @js(old('time_waster', $selected['time_waster'] ?? '')),

        // Social links
        twitter_handle: @js(old('twitter_handle', $profile->twitter_handle ?? '')),
        website: @js(old('website', $profile->website ?? '')),
        onlyfans_username: @js(old('onlyfans_username', $profile->onlyfans_username ?? '')),

        // Suburb autocomplete state
        searchResults: [],
        showResults: false,
        searching: false,
        debounceTimer: null,
        suburbSelected: {{ old('suburb', $user->suburb ?? '') ? 'true' : 'false' }},

        // UI state
        submitting: false,
        errors: @js($errors->all()),

        // Methods
        toggleTag(group, tag, event) {
            if (group === 'primaryIdentity') {
                this.primaryIdentity = [tag];
            } else if (group === 'attributes') {
                if (this.attributes.includes(tag)) {
                    this.attributes = this.attributes.filter(t => t !== tag);
                } else {
                    this.attributes.push(tag);
                }
            } else if (group === 'servicesStyle') {
                if (this.servicesStyle.includes(tag)) {
                    this.servicesStyle = this.servicesStyle.filter(t => t !== tag);
                } else if (this.servicesStyle.length < 12) {
                    this.servicesStyle.push(tag);
                } else {
                    const el = event.currentTarget;
                    el.classList.add('shake');
                    setTimeout(() => el.classList.remove('shake'), 300);
                }
            }
        },

        toggleService(service) {
            if (this.services_provided.includes(service)) {
                this.services_provided = this.services_provided.filter(s => s !== service);
            } else {
                this.services_provided.push(service);
            }
        },

        handleSuburbInput() {
            this.suburbSelected = false;
            this.searchSuburbs();
        },

        handleSuburbBlur() {
            setTimeout(() => {
                this.showResults = false;
            }, 200);
        },

        searchSuburbs() {
            if (!this.suburb || this.suburb.trim().length < 2) {
                this.searchResults = [];
                this.showResults = false;
                return;
            }

            clearTimeout(this.debounceTimer);

            this.debounceTimer = setTimeout(() => {
                this.searching = true;

                fetch(`/api/suburbs/search?q=${encodeURIComponent(this.suburb.trim())}`)
                    .then(res => {
                        if (!res.ok) {
                            throw new Error('Failed to fetch suburbs');
                        }
                        return res.json();
                    })
                    .then(data => {
                        this.searchResults = Array.isArray(data) ? data : [];
                        this.showResults = this.searchResults.length > 0;
                    })
                    .catch(error => {
                        console.error('Suburb search error:', error);
                        this.searchResults = [];
                        this.showResults = false;
                    })
                    .finally(() => {
                        this.searching = false;
                    });
            }, 300);
        },

        selectSuburb(item) {
            this.suburb = `${item.suburb}, ${item.state} ${item.postcode}`;
            this.suburbSelected = true;
            this.showResults = false;
            this.searchResults = [];
        },

        validate() {
            let errors = [];
            if (!this.name.trim()) errors.push('Name is required.');
            if (!this.mobile.trim()) errors.push('Mobile number is required.');
            if (!this.suburb.trim()) {
                errors.push('Suburb is required.');
            } else if (!this.suburbSelected) {
                errors.push('Please choose a location from the dropdown list, which appears while typing.');
            }
            if (!this.introduction_line.trim()) errors.push('Introduction line is required.');
            if (!this.profile_text.trim()) errors.push('Profile text is required.');
            if (!this.age_group) errors.push('Age group is required.');
            if (!this.hair_color) errors.push('Hair color is required.');
            if (!this.hair_length) errors.push('Hair length is required.');
            if (!this.ethnicity) errors.push('Ethnicity is required.');
            if (!this.body_type) errors.push('Body type is required.');
            if (!this.bust_size) errors.push('Bust size is required.');
            if (!this.your_length) errors.push('Your length is required.');
            if (this.primaryIdentity.length === 0) errors.push('Primary identity is required.');
            if (this.attributes.length === 0) errors.push('Attributes are required.');
            if (this.servicesStyle.length === 0) errors.push('Services & style are required.');
            if (this.services_provided.length === 0) errors.push('Services provided are required.');
            if (!this.availability) errors.push('Availability is required.');
            if (!this.contact_method) errors.push('Contact method is required.');
            if (!this.phone_contact) errors.push('Phone contact preference is required.');
            if (!this.time_waster) errors.push('Time waster shield preference is required.');
            return errors;
        },

        async submitForm() {
            this.errors = this.validate();

            if (this.errors.length > 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation errors',
                    html: `<ul style='text-align:left; margin:0; padding-left:1.2rem;'>${this.errors.map(e => `<li>${e}</li>`).join('')}</ul>`
                });
                return;
            }

            this.submitting = true;

            const formData = new FormData();
            formData.append('name', this.name);
            formData.append('mobile', this.mobile);
            formData.append('introduction_line', this.introduction_line);
            formData.append('suburb', this.suburb);
            formData.append('profile_text', this.profile_text);
            formData.append('age_group', this.age_group);
            formData.append('hair_color', this.hair_color);
            formData.append('hair_length', this.hair_length);
            formData.append('ethnicity', this.ethnicity);
            formData.append('body_type', this.body_type);
            formData.append('bust_size', this.bust_size);
            formData.append('your_length', this.your_length);

            this.primaryIdentity.forEach(tag => formData.append('primary_identity[]', tag));
            this.attributes.forEach(tag => formData.append('attributes[]', tag));
            this.servicesStyle.forEach(tag => formData.append('services_style[]', tag));
            this.services_provided.forEach(service => formData.append('services_provided[]', service));

            formData.append('availability', this.availability);
            formData.append('contact_method', this.contact_method);
            formData.append('phone_contact', this.phone_contact);
            formData.append('time_waster', this.time_waster);

            formData.append('twitter_handle', this.twitter_handle);
            formData.append('website', this.website);
            formData.append('onlyfans_username', this.onlyfans_username);

            formData.append('_token', @js(csrf_token()));

            try {
                const response = await fetch(@js(url()->current()), {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                let data = {};
                const contentType = response.headers.get('content-type') || '';

                if (contentType.includes('application/json')) {
                    data = await response.json();
                } else {
                    const text = await response.text();
                    data = { message: text };
                }

                if (response.ok) {
                    this.errors = [];
                    Swal.fire({
                        icon: 'success',
                        title: 'Saved',
                        text: data.message || 'Profile updated successfully.',
                        timer: 2500,
                        timerProgressBar: true,
                        showConfirmButton: false
                    });
                } else if (response.status === 422) {
                    const messages = Object.values(data.errors || {}).flat();
                    this.errors = messages.length ? messages : ['Validation failed.'];
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation errors',
                        html: `<ul style='text-align:left; margin:0; padding-left:1.2rem;'>${this.errors.map(m => `<li>${m}</li>`).join('')}</ul>`
                    });
                } else {
                    this.errors = [data.message || 'Unable to save profile. Please try again later.'];
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Unable to save profile. Please try again later.'
                    });
                }
            } catch (error) {
                this.errors = ['Unable to save profile. Please check your connection and try again.'];
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Unable to save profile. Please check your connection and try again.'
                });
            } finally {
                this.submitting = false;
            }
        }
     }"
>
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-8 border-l-6 border-[#e04ecb] pl-4">
            Edit your profile
        </h1>

        <button onclick="window.history.back()" type="button" class="inline-flex items-center text-[#e04ecb] hover:text-[#c13ab0] transition-colors mb-6 text-sm font-medium bg-transparent border-0 cursor-pointer">
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
                        <input name="name" type="text" x-model="name" readonly class="w-full px-4 py-3 border border-gray-400 rounded-lg text-gray-900 font-medium bg-gray-100 cursor-not-allowed transition">
                    </div>
                    <div>
                        <label class="block font-semibold text-[#e04ecb] mb-1">Mobile number</label>
                        <input name="mobile" type="text" x-model="mobile" readonly class="w-full px-4 py-3 border border-gray-400 rounded-lg text-gray-900 font-medium bg-gray-100 cursor-not-allowed transition">
                    </div>
                </div>

                <div class="mt-6">
                    <label class="block font-semibold text-[#e04ecb] mb-1">Introduction line</label>
                    <textarea name="introduction_line" x-model="introduction_line" rows="2" class="w-full px-4 py-3 border border-gray-400 rounded-lg text-gray-900 font-medium focus:ring-2 focus:ring-[#e04ecb] focus:border-transparent transition"></textarea>
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
                    <p>It is illegal in Vic & QLD to describe your sexual services in details, you also cannot refer to the term massage. In QLD you cannot advertise 'doubles'. If you are in VIC please do not forget to mention your SWA Licence number</p>
                </div>

                <p class="text-gray-800 mb-3">
                    You can use our special features for
                    <a href="{{ url('/my-rate') }}" class="text-[#e04ecb] underline font-medium">my rates</a> and
                    <a href="{{ url('/my-availability') }}" class="text-[#e04ecb] underline font-medium">my availability</a>,
                    or you can type them down here.
                </p>

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
                <textarea name="profile_text" x-model="profile_text" rows="6" class="w-full px-4 py-3 border border-t-0 border-gray-400 rounded-b-lg text-gray-900 font-medium focus:ring-2 focus:ring-[#e04ecb] focus:border-transparent transition" placeholder="Write your profile description here..."></textarea>
            </div>

            <div class="bg-white border border-gray-200 rounded-2xl p-6 md:p-8 shadow-sm">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Your stats</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <label class="block font-semibold text-[#e04ecb] mb-1">Age group</label>
                        <select name="age_group" x-model="age_group" class="w-full px-4 py-3 border border-gray-400 rounded-lg text-gray-900 font-medium focus:ring-2 focus:ring-[#e04ecb] focus:border-transparent bg-white">
                            <option value="">- Select age -</option>
                            @foreach($ageGroupOptions ?? [] as $id => $label)
                                <option value="{{ $id }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-[#e04ecb] mb-1">Hair color</label>
                        <select name="hair_color" x-model="hair_color" class="w-full px-4 py-3 border border-gray-400 rounded-lg text-gray-900 font-medium focus:ring-2 focus:ring-[#e04ecb] focus:border-transparent bg-white">
                            <option value="">- Select -</option>
                            @foreach($hairColorOptions ?? [] as $id => $label)
                                <option value="{{ $id }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-[#e04ecb] mb-1">Hair length</label>
                        <select name="hair_length" x-model="hair_length" class="w-full px-4 py-3 border border-gray-400 rounded-lg text-gray-900 font-medium focus:ring-2 focus:ring-[#e04ecb] focus:border-transparent bg-white">
                            <option value="">- Select -</option>
                            @foreach($hairLengthOptions ?? [] as $id => $label)
                                <option value="{{ $id }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-[#e04ecb] mb-1">Ethnicity</label>
                        <select name="ethnicity" x-model="ethnicity" class="w-full px-4 py-3 border border-gray-400 rounded-lg text-gray-900 font-medium focus:ring-2 focus:ring-[#e04ecb] focus:border-transparent bg-white">
                            <option value="">- Select -</option>
                            @foreach($ethnicityOptions ?? [] as $id => $label)
                                <option value="{{ $id }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-[#e04ecb] mb-1">Body type</label>
                        <select name="body_type" x-model="body_type" class="w-full px-4 py-3 border border-gray-400 rounded-lg text-gray-900 font-medium focus:ring-2 focus:ring-[#e04ecb] focus:border-transparent bg-white">
                            <option value="">- Select -</option>
                            @foreach($bodyTypeOptions ?? [] as $id => $label)
                                <option value="{{ $id }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-[#e04ecb] mb-1">Bust size</label>
                        <select name="bust_size" x-model="bust_size" class="w-full px-4 py-3 border border-gray-400 rounded-lg text-gray-900 font-medium focus:ring-2 focus:ring-[#e04ecb] focus:border-transparent bg-white">
                            <option value="">- Select -</option>
                            @foreach($bustSizeOptions ?? [] as $id => $label)
                                <option value="{{ $id }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-[#e04ecb] mb-1">Your length</label>
                        <select name="your_length" x-model="your_length" class="w-full px-4 py-3 border border-gray-400 rounded-lg text-gray-900 font-medium focus:ring-2 focus:ring-[#e04ecb] focus:border-transparent bg-white">
                            <option value="">- Select -</option>
                            @foreach($yourLengthOptions ?? [] as $id => $label)
                                <option value="{{ $id }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-2xl p-6 md:p-8 shadow-sm">
                <h2 class="text-xl font-semibold text-gray-900 mb-2">Tags that describe you</h2>
                <p class="text-gray-600 text-sm mb-6">These tags help clients find you. Click to select.</p>

                <div class="space-y-6">
                    <div>
                        <h3 class="font-semibold text-[#e04ecb] mb-3">Primary identity <span class="text-[#e04ecb] text-sm">(select one)</span></h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach($primaryTags as $tag)
                                <span class="tag-pill px-4 py-2 bg-gray-200 text-gray-800 rounded-full text-sm cursor-pointer hover:bg-[#e04ecb] hover:text-white transition"
                                      :class="{ 'selected': primaryIdentity.includes(@js($tag)) }"
                                      @click="toggleTag('primaryIdentity', @js($tag), $event)">
                                    {{ $tag }}
                                </span>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <h3 class="font-semibold text-[#e04ecb] mb-3">Attributes <span class="text-[#e04ecb] text-sm">(multiple allowed)</span></h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach($attrTags as $tag)
                                <span class="tag-pill px-4 py-2 bg-gray-200 text-gray-800 rounded-full text-sm cursor-pointer hover:bg-[#e04ecb] hover:text-white transition"
                                      :class="{ 'selected': attributes.includes(@js($tag)) }"
                                      @click="toggleTag('attributes', @js($tag), $event)">
                                    {{ $tag }}
                                </span>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <h3 class="font-semibold text-[#e04ecb] mb-3">Services & style <span class="text-[#e04ecb] text-sm">(up to 12)</span></h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach($styleTags as $tag)
                                <span class="tag-pill px-4 py-2 bg-gray-200 text-gray-800 rounded-full text-sm cursor-pointer hover:bg-[#e04ecb] hover:text-white transition"
                                      :class="{ 'selected': servicesStyle.includes(@js($tag)) }"
                                      @click="toggleTag('servicesStyle', @js($tag), $event)">
                                    {{ $tag }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-2xl p-6 md:p-8 shadow-sm">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Services you provide</h2>
                <p class="text-gray-600 text-sm mb-4">Check all that apply</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                    @foreach($services as $service)
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox"
                                   name="services_provided[]"
                                   value="{{ $service }}"
                                   class="w-5 h-5 text-[#e04ecb] rounded border-gray-400 focus:ring-[#e04ecb]"
                                   :checked="services_provided.includes(@js($service))"
                                   @change="toggleService(@js($service))">
                            <span class="text-gray-800 text-sm">{{ $service }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-2xl p-6 md:p-8 shadow-sm">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Availability & contact</h2>

                <div class="space-y-4">
                    <div>
                        <label class="block font-semibold text-[#e04ecb] mb-2">Are you available for:</label>
                        <div class="flex flex-wrap gap-4">
                            @foreach($availabilityOptions ?? [] as $option)
                                <label class="flex items-center gap-2">
                                    <input type="radio" name="availability" value="{{ $option }}" class="w-4 h-4 text-[#e04ecb] border-gray-400" x-model="availability">
                                    <span class="text-gray-800">{{ $option }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-[#e04ecb] mb-2">How can people contact you?</label>
                        <p class="text-sm text-gray-600 mb-2">Email enquiries will be sent to: {{ $contactEmail ?? 's8813w@gmail.com' }}</p>
                        <div class="flex flex-wrap gap-4">
                            @foreach($contactMethodOptions ?? [] as $option)
                                <label class="flex items-center gap-2">
                                    <input type="radio" name="contact_method" value="{{ $option }}" class="w-4 h-4 text-[#e04ecb] border-gray-400" x-model="contact_method">
                                    <span class="text-gray-800">{{ $option }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-[#e04ecb] mb-2">Phone contact preferences</label>
                        <div class="flex flex-wrap gap-4">
                            @foreach($phoneContactOptions ?? [] as $option)
                                <label class="flex items-center gap-2">
                                    <input type="radio" name="phone_contact" value="{{ $option }}" class="w-4 h-4 text-[#e04ecb] border-gray-400" x-model="phone_contact">
                                    <span class="text-gray-800">{{ $option }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-[#e04ecb] mb-2">Use time waster shield for SMS?</label>
                        <div class="flex gap-4">
                            @foreach($timeWasterOptions ?? [] as $option)
                                <label class="flex items-center gap-2">
                                    <input type="radio" name="time_waster" value="{{ $option }}" class="w-4 h-4 text-[#e04ecb] border-gray-400" x-model="time_waster">
                                    <span class="text-gray-800">{{ $option }}</span>
                                </label>
                            @endforeach
                        </div>
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
                    <div>
                        <label class="block font-semibold text-[#e04ecb] mb-1">Website</label>
                        <input name="website" type="text" x-model="website" class="w-full px-4 py-3 border border-gray-400 rounded-lg text-gray-900 font-medium focus:ring-2 focus:ring-[#e04ecb] focus:border-transparent">
                    </div>
                    <div>
                        <label class="block font-semibold text-[#e04ecb] mb-1">OnlyFans username</label>
                        <input name="onlyfans_username" type="text" x-model="onlyfans_username" class="w-full px-4 py-3 border border-gray-400 rounded-lg text-gray-900 font-medium focus:ring-2 focus:ring-[#e04ecb] focus:border-transparent">
                    </div>
                </div>
            </div>

            <div class="pt-4">
                <button type="submit"
                        :disabled="submitting"
                        class="w-full md:w-auto px-10 py-4 bg-gradient-to-r from-[#e04ecb] to-[#c13ab0] text-white font-bold text-lg rounded-full shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition focus:outline-none focus:ring-2 focus:ring-[#e04ecb] focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span x-show="!submitting">Save your profile</span>
                    <span x-show="submitting">Saving...</span>
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }

    .tag-pill.selected {
        background-color: #e04ecb !important;
        color: white !important;
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

<script src="https://unpkg.com/alpinejs" defer></script>
@endsection --}}


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
                        rows="2"
                        class="w-full px-4 py-3 border border-gray-400 rounded-lg text-gray-900 font-medium focus:ring-2 focus:ring-[#e04ecb] focus:border-transparent transition"
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

                <div x-ref="profileTextEditor" class="prose max-w-none"></div>
                <input type="hidden" name="profile_text" x-model="profile_text">
            </div>

            <div class="bg-white border border-gray-200 rounded-2xl p-6 md:p-8 shadow-sm">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Your stats</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <label class="block font-semibold text-[#e04ecb] mb-1">Age group</label>
                        <select name="age_group" x-model="age_group" class="w-full px-4 py-3 border border-gray-400 rounded-lg text-gray-900 font-medium focus:ring-2 focus:ring-[#e04ecb] focus:border-transparent bg-white">
                            <option value="">- Select age -</option>
                            @foreach($ageGroupOptions ?? [] as $id => $label)
                                <option value="{{ $id }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-[#e04ecb] mb-1">Hair color</label>
                        <select name="hair_color" x-model="hair_color" class="w-full px-4 py-3 border border-gray-400 rounded-lg text-gray-900 font-medium focus:ring-2 focus:ring-[#e04ecb] focus:border-transparent bg-white">
                            <option value="">- Select -</option>
                            @foreach($hairColorOptions ?? [] as $id => $label)
                                <option value="{{ $id }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-[#e04ecb] mb-1">Hair length</label>
                        <select name="hair_length" x-model="hair_length" class="w-full px-4 py-3 border border-gray-400 rounded-lg text-gray-900 font-medium focus:ring-2 focus:ring-[#e04ecb] focus:border-transparent bg-white">
                            <option value="">- Select -</option>
                            @foreach($hairLengthOptions ?? [] as $id => $label)
                                <option value="{{ $id }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-[#e04ecb] mb-1">Ethnicity</label>
                        <select name="ethnicity" x-model="ethnicity" class="w-full px-4 py-3 border border-gray-400 rounded-lg text-gray-900 font-medium focus:ring-2 focus:ring-[#e04ecb] focus:border-transparent bg-white">
                            <option value="">- Select -</option>
                            @foreach($ethnicityOptions ?? [] as $id => $label)
                                <option value="{{ $id }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-[#e04ecb] mb-1">Body type</label>
                        <select name="body_type" x-model="body_type" class="w-full px-4 py-3 border border-gray-400 rounded-lg text-gray-900 font-medium focus:ring-2 focus:ring-[#e04ecb] focus:border-transparent bg-white">
                            <option value="">- Select -</option>
                            @foreach($bodyTypeOptions ?? [] as $id => $label)
                                <option value="{{ $id }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-[#e04ecb] mb-1">Bust size</label>
                        <select name="bust_size" x-model="bust_size" class="w-full px-4 py-3 border border-gray-400 rounded-lg text-gray-900 font-medium focus:ring-2 focus:ring-[#e04ecb] focus:border-transparent bg-white">
                            <option value="">- Select -</option>
                            @foreach($bustSizeOptions ?? [] as $id => $label)
                                <option value="{{ $id }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-[#e04ecb] mb-1">Your length</label>
                        <select name="your_length" x-model="your_length" class="w-full px-4 py-3 border border-gray-400 rounded-lg text-gray-900 font-medium focus:ring-2 focus:ring-[#e04ecb] focus:border-transparent bg-white">
                            <option value="">- Select -</option>
                            @foreach($yourLengthOptions ?? [] as $id => $label)
                                <option value="{{ $id }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-2xl p-6 md:p-8 shadow-sm">
                <h2 class="text-xl font-semibold text-gray-900 mb-2">Tags that describe you</h2>
                <p class="text-gray-600 text-sm mb-6">These tags help clients find you. Click to select.</p>

                <div class="space-y-6">
                    <div>
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
                    </div>

                    <div>
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
                    </div>

                    <div>
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
                    </div>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-2xl p-6 md:p-8 shadow-sm">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Services you provide</h2>
                <p class="text-gray-600 text-sm mb-4">Check all that apply</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
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
            </div>

            <div class="bg-white border border-gray-200 rounded-2xl p-6 md:p-8 shadow-sm">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Availability & contact</h2>

                <div class="space-y-4">
                    <div>
                        <label class="block font-semibold text-[#e04ecb] mb-2">Are you available for:</label>
                        <div class="flex flex-wrap gap-4">
                            @foreach($availabilityOptions ?? [] as $option)
                                <label class="flex items-center gap-2">
                                    <input type="radio" name="availability" value="{{ $option }}" class="w-4 h-4 text-[#e04ecb] border-gray-400" x-model="availability">
                                    <span class="text-gray-800">{{ $option }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-[#e04ecb] mb-2">How can people contact you?</label>
                        <p class="text-sm text-gray-600 mb-2">Email enquiries will be sent to: {{ $contactEmail ?? 's8813w@gmail.com' }}</p>
                        <div class="flex flex-wrap gap-4">
                            @foreach($contactMethodOptions ?? [] as $option)
                                <label class="flex items-center gap-2">
                                    <input type="radio" name="contact_method" value="{{ $option }}" class="w-4 h-4 text-[#e04ecb] border-gray-400" x-model="contact_method">
                                    <span class="text-gray-800">{{ $option }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-[#e04ecb] mb-2">Phone contact preferences</label>
                        <div class="flex flex-wrap gap-4">
                            @foreach($phoneContactOptions ?? [] as $option)
                                <label class="flex items-center gap-2">
                                    <input type="radio" name="phone_contact" value="{{ $option }}" class="w-4 h-4 text-[#e04ecb] border-gray-400" x-model="phone_contact">
                                    <span class="text-gray-800">{{ $option }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-[#e04ecb] mb-2">Use time waster shield for SMS?</label>
                        <div class="flex gap-4">
                            @foreach($timeWasterOptions ?? [] as $option)
                                <label class="flex items-center gap-2">
                                    <input type="radio" name="time_waster" value="{{ $option }}" class="w-4 h-4 text-[#e04ecb] border-gray-400" x-model="time_waster">
                                    <span class="text-gray-800">{{ $option }}</span>
                                </label>
                            @endforeach
                        </div>
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

                    <div>
                        <label class="block font-semibold text-[#e04ecb] mb-1">Website</label>
                        <input name="website" type="text" x-model="website" class="w-full px-4 py-3 border border-gray-400 rounded-lg text-gray-900 font-medium focus:ring-2 focus:ring-[#e04ecb] focus:border-transparent">
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

@push('styles')
<style>
    [x-cloak] { display: none !important; }

    .ck-editor__editable {
        min-height: 200px;
        font-size: 1rem !important;
        line-height: 1.6 !important;
        color: #1f2937 !important;
        background-color: #ffffff !important;
        font-family: inherit !important;
    }

    .ck-editor__editable.ck-placeholder::before {
        color: #9ca3af !important;
        font-style: normal !important;
        opacity: 1;
    }

    .ck-content h1 {
        font-size: 2em !important;
        font-weight: 700 !important;
        margin-bottom: 0.5em !important;
    }

    .ck-content h2 {
        font-size: 1.5em !important;
        font-weight: 600 !important;
        margin-bottom: 0.5em !important;
    }

    .ck-content h3 {
        font-size: 1.25em !important;
        font-weight: 600 !important;
        margin-bottom: 0.5em !important;
    }

    .ck-content a {
        color: #e04ecb !important;
        text-decoration: underline !important;
    }

    .ck-content a:hover {
        color: #c13ab0 !important;
    }

    .ck-content ul,
    .ck-content ol {
        padding-left: 2em !important;
        margin-bottom: 1em !important;
    }

    .ck-content blockquote {
        border-left: 4px solid #e04ecb !important;
        padding-left: 1em !important;
        margin-left: 0 !important;
        font-style: italic !important;
        color: #4b5563 !important;
    }

    .tag-pill.selected {
        background-color: #e04ecb !important;
        color: white !important;
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
<script src="https://cdn.ckeditor.com/ckeditor5/40.2.0/classic/ckeditor.js"></script>
<script src="https://unpkg.com/alpinejs" defer></script>
<script src="{{ asset('profile/js/edit-profile-form.js') }}"></script>
@endpush
