@extends('layouts.frontend')

@section('title', 'Report a Listing')

@push('scripts')
    <script>
        function reportListingForm(config = {}) {
            return {
                category: config.category || '',
                otherCategory: config.otherCategory || '',
                listingUrl: config.listingUrl || '',
                advertiserName: config.advertiserName || '',
                reporterEmail: config.reporterEmail || '',
                description: config.description || '',
                declarationAccuracy: !!config.declarationAccuracy,
                declarationContact: !!config.declarationContact,
                errors: {},
                touched: {
                    category: false,
                    otherCategory: false,
                    listingUrl: false,
                    advertiserName: false,
                    reporterEmail: false,
                    description: false,
                    declarationAccuracy: false,
                    declarationContact: false,
                },
                evidenceFiles: [],
                isDraggingEvidence: false,
                fieldOrder: [
                    'category',
                    'otherCategory',
                    'listingUrl',
                    'advertiserName',
                    'reporterEmail',
                    'description',
                    'declarationAccuracy',
                    'declarationContact',
                ],
                init() {
                    this.$nextTick(() => this.scrollToFirstServerError());
                },
                markAllTouched() {
                    Object.keys(this.touched).forEach((field) => {
                        this.touched[field] = true;
                    });
                },
                validateCategory() {
                    if (! this.category) {
                        this.errors.category = 'Please select a category.';

                        return;
                    }

                    delete this.errors.category;
                    this.validateOtherCategory();
                },
                validateOtherCategory() {
                    if (this.category === 'other' && ! this.otherCategory.trim()) {
                        this.errors.otherCategory = 'Please enter the other category.';

                        return;
                    }

                    delete this.errors.otherCategory;
                },
                validateListingUrl() {
                    if (! this.listingUrl.trim()) {
                        this.errors.listingUrl = 'Listing URL is required.';

                        return;
                    }

                    delete this.errors.listingUrl;
                },
                validateAdvertiserName() {
                    if (! this.advertiserName.trim()) {
                        this.errors.advertiserName = 'Profile / Advertiser Name is required.';

                        return;
                    }

                    delete this.errors.advertiserName;
                },
                validateReporterEmail() {
                    if (! this.reporterEmail.trim()) {
                        this.errors.reporterEmail = 'Email address is required.';

                        return;
                    }

                    delete this.errors.reporterEmail;
                },
                validateDescription() {
                    if (! this.description.trim()) {
                        this.errors.description = 'Please describe the issue.';

                        return;
                    }

                    delete this.errors.description;
                },
                validateDeclarationAccuracy() {
                    if (! this.declarationAccuracy) {
                        this.errors.declarationAccuracy = 'Please confirm the declaration accuracy statement.';

                        return;
                    }

                    delete this.errors.declarationAccuracy;
                },
                validateDeclarationContact() {
                    if (! this.declarationContact) {
                        this.errors.declarationContact = 'Please confirm the contact declaration statement.';

                        return;
                    }

                    delete this.errors.declarationContact;
                },
                validate() {
                    this.validateCategory();
                    this.validateOtherCategory();
                    this.validateListingUrl();
                    this.validateAdvertiserName();
                    this.validateReporterEmail();
                    this.validateDescription();
                    this.validateDeclarationAccuracy();
                    this.validateDeclarationContact();

                    return Object.keys(this.errors).length === 0;
                },
                getFieldRef(field) {
                    return this.$refs[field] || null;
                },
                scrollToField(field) {
                    const target = this.getFieldRef(field);

                    if (! target) {
                        return;
                    }

                    target.scrollIntoView({ behavior: 'smooth', block: 'center' });

                    if (typeof target.focus === 'function') {
                        setTimeout(() => target.focus({ preventScroll: true }), 150);
                    }
                },
                scrollToFirstServerError() {
                    const firstServerError = this.$el.querySelector('[data-server-error="true"]');

                    if (! firstServerError) {
                        return;
                    }

                    const field = firstServerError.dataset.field;

                    if (field) {
                        this.scrollToField(field);

                        return;
                    }

                    firstServerError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                },
                isEvidenceFileDuplicate(file) {
                    return this.evidenceFiles.some(
                        (f) => f.name === file.name && f.size === file.size && f.lastModified === file.lastModified
                    );
                },
                addEvidenceFiles(newFiles) {
                    Array.from(newFiles).forEach((file) => {
                        const allowed = ['image/jpeg', 'image/png', 'application/pdf'];
                        if (!allowed.includes(file.type)) return;
                        if (file.size > 10 * 1024 * 1024) return;
                        if (this.isEvidenceFileDuplicate(file)) return;
                        const isPdf = file.type === 'application/pdf';
                        const preview = isPdf ? 'pdf' : URL.createObjectURL(file);
                        this.evidenceFiles.push({ file, preview, name: file.name });
                    });
                },
                handleEvidenceFileSelect(event) {
                    this.addEvidenceFiles(event.target.files);
                    event.target.value = '';
                },
                handleEvidenceDrop(event) {
                    this.isDraggingEvidence = false;
                    this.addEvidenceFiles(event.dataTransfer.files);
                },
                removeEvidenceFile(index) {
                    const entry = this.evidenceFiles[index];
                    if (entry && entry.preview && entry.preview !== 'pdf') {
                        URL.revokeObjectURL(entry.preview);
                    }
                    this.evidenceFiles.splice(index, 1);
                },
                openEvidenceFilePicker() {
                    this.$refs.evidenceFileInput.click();
                },
                submitForm(event) {
                    this.markAllTouched();

                    if (this.validate()) {
                        if (this.evidenceFiles.length > 0 && typeof DataTransfer !== 'undefined') {
                            try {
                                const dt = new DataTransfer();
                                this.evidenceFiles.forEach((entry) => dt.items.add(entry.file));
                                this.$refs.evidenceInput.files = dt.files;
                            } catch (_) {}
                        }
                        return true;
                    }

                    event.preventDefault();

                    this.$nextTick(() => {
                        const firstInvalidField = this.fieldOrder.find((field) => this.errors[field]);

                        if (firstInvalidField) {
                            this.scrollToField(firstInvalidField);
                        }
                    });

                    return false;
                },
            };
        }
    </script>
@endpush

@section('content')
<div class="min-h-screen bg-[#f8fafc] py-10">
    <main class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="rounded-2xl bg-white p-6 shadow-md border border-gray-100 md:p-8">
            <button
                type="button"
                onclick="window.history.back()"
                class="mb-6 inline-flex cursor-pointer items-center border-0 bg-transparent text-sm font-medium text-pink-500 transition-colors hover:text-pink-600"
            >
                <span class="mr-1">&lt;</span> back
            </button>

            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">{{ $page?->title ?: 'Report a Listing' }}</h1>

                @if(!empty($page?->content))
                    <div class="mt-3 text-gray-600 leading-relaxed max-w-none [&_*]:text-gray-600 [&_h1]:text-gray-900 [&_h2]:text-gray-900 [&_h3]:text-gray-900 [&_h4]:text-gray-900 [&_h5]:text-gray-900 [&_h6]:text-gray-900 [&_h1]:font-bold [&_h2]:font-bold [&_h3]:font-semibold [&_h4]:font-semibold [&_h1]:text-2xl [&_h2]:text-xl [&_h3]:text-lg [&_h1]:mt-6 [&_h2]:mt-5 [&_h3]:mt-4 [&_h1]:mb-3 [&_h2]:mb-3 [&_h3]:mb-2 [&_p]:mb-4 [&_li]:mb-1 [&_ul]:list-disc [&_ul]:pl-6 [&_ol]:list-decimal [&_ol]:pl-6 [&_a]:text-pink-600 hover:[&_a]:text-pink-700 [&_img]:max-w-full [&_img]:h-auto [&_img]:rounded-lg [&_img]:my-4">
                        {!! $page->content !!}
                    </div>
                @else
                    <p class="mt-3 text-gray-600">
                        Use this form to report a listing, image, profile, advertiser, review, or content that may violate our policies, contain misleading information, involve non-consensual content, impersonation, scams, or other prohibited activity.
                    </p>
                    <p class="mt-3 text-sm font-semibold text-red-600">
                        If someone is in immediate danger, call 000. This form is not monitored as an emergency service.
                    </p>
                @endif
            </div>

            @if(session('success'))
                <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                    <p class="font-semibold">{{ session('success') }}</p>
                    <p class="mt-2">We prioritise reports involving:</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5">
                        <li>Inappropriate images</li>
                        <li>Non-consensual content</li>
                        <li>Underage concerns</li>
                        <li>Impersonation</li>
                        <li>Scams</li>
                        <li>Safety-related issues</li>
                    </ul>
                    <p class="mt-3">Our moderation team will review the report and take appropriate action.</p>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <p class="mb-1 font-semibold">Please correct the errors below.</p>
                    <ul class="list-disc space-y-1 pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-md md:p-8 lg:col-span-2">
                    <form
                        action="{{ route('report-a-listing.submit') }}"
                        method="POST"
                        enctype="multipart/form-data"
                        class="space-y-8"
                        x-data="reportListingForm({
                            category: @js(old('category', '')),
                            otherCategory: @js(old('other_category', '')),
                            listingUrl: @js(old('listing_url', $prefill['listing_url'] ?? '')),
                            advertiserName: @js(old('advertiser_name', $prefill['advertiser_name'] ?? '')),
                            reporterEmail: @js(old('reporter_email', '')),
                            description: @js(old('description', '')),
                            declarationAccuracy: @js((bool) old('declaration_accuracy')),
                            declarationContact: @js((bool) old('declaration_contact')),
                        })"
                        @submit="submitForm"
                        novalidate
                    >
                        @csrf

                        <section class="space-y-3">
                            <h2 class="text-xl font-bold text-gray-900">Report Category <span class="text-red-500">*</span></h2>
                            <select
                                name="category"
                                x-ref="category"
                                x-model="category"
                                @change="touched.category = true; validateCategory()"
                                :aria-invalid="errors.category ? 'true' : 'false'"
                                class="w-full px-4 py-3 border-2 rounded-xl bg-white text-gray-800 font-semibold focus:border-[#e04ecb] focus:ring-2 focus:ring-[#e04ecb]/20 transition"
                                :class="errors.category ? 'border-red-500 focus:ring-red-200' : 'border-gray-200'"
                            >
                                <option value="" disabled {{ old('category') ? '' : 'selected' }}>Select a category</option>
                                @foreach($categoryOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(old('category') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <div class="min-h-5">
                                @error('category')
                                    <p class="text-xs text-red-600" data-server-error="true" data-field="category">{{ $message }}</p>
                                @enderror
                                <template x-if="touched.category && errors.category">
                                    <p class="text-xs text-red-600" x-text="errors.category"></p>
                                </template>
                            </div>
                            <div x-show="category === 'other'" x-cloak>
                                <label class="block font-semibold text-gray-800 mb-1">Other Category <span class="text-red-500">*</span></label>
                                <input
                                    type="text"
                                    name="other_category"
                                    x-ref="otherCategory"
                                    :required="category === 'other'"
                                    x-model="otherCategory"
                                    @input="touched.otherCategory = true; validateOtherCategory()"
                                    @blur="touched.otherCategory = true; validateOtherCategory()"
                                    value="{{ old('other_category') }}"
                                    :aria-invalid="errors.otherCategory ? 'true' : 'false'"
                                    class="w-full px-4 py-3 border-2 rounded-xl focus:border-[#e04ecb] focus:ring-2 focus:ring-[#e04ecb]/20 transition text-gray-900 font-semibold"
                                    :class="errors.otherCategory ? 'border-red-500 focus:ring-red-200' : 'border-gray-200'"
                                    placeholder="Please enter category"
                                >
                                <div class="mt-1 min-h-5">
                                    @error('other_category')
                                        <p class="text-xs text-red-600" data-server-error="true" data-field="otherCategory">{{ $message }}</p>
                                    @enderror
                                    <template x-if="touched.otherCategory && errors.otherCategory">
                                        <p class="text-xs text-red-600" x-text="errors.otherCategory"></p>
                                    </template>
                                </div>
                            </div>
                        </section>

                        <section class="space-y-4">
                            <h2 class="text-xl font-bold text-gray-900">Listing Information</h2>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div class="md:col-span-2">
                                    <label class="block font-semibold text-gray-800 mb-1">Listing URL <span class="text-red-500">*</span></label>
                                    <input
                                        type="url"
                                        name="listing_url"
                                        x-ref="listingUrl"
                                        x-model="listingUrl"
                                        @input="touched.listingUrl = true; validateListingUrl()"
                                        @blur="touched.listingUrl = true; validateListingUrl()"
                                        value="{{ old('listing_url', $prefill['listing_url'] ?? '') }}"
                                        :aria-invalid="errors.listingUrl ? 'true' : 'false'"
                                        class="w-full px-4 py-3 border-2 rounded-xl focus:border-[#e04ecb] focus:ring-2 focus:ring-[#e04ecb]/20 transition text-gray-900 font-semibold"
                                        :class="errors.listingUrl ? 'border-red-500 focus:ring-red-200' : 'border-gray-200'"
                                        placeholder="https://hotescort.com.au/..."
                                    >
                                    <div class="mt-1 min-h-5">
                                        @error('listing_url')
                                            <p class="text-xs text-red-600" data-server-error="true" data-field="listingUrl">{{ $message }}</p>
                                        @enderror
                                        <template x-if="touched.listingUrl && errors.listingUrl">
                                            <p class="text-xs text-red-600" x-text="errors.listingUrl"></p>
                                        </template>
                                    </div>
                                </div>
                                <div>
                                    <label class="block font-semibold text-gray-800 mb-1">Profile / Advertiser Name <span class="text-red-500">*</span></label>
                                    <input
                                        type="text"
                                        name="advertiser_name"
                                        x-ref="advertiserName"
                                        x-model="advertiserName"
                                        @input="touched.advertiserName = true; validateAdvertiserName()"
                                        @blur="touched.advertiserName = true; validateAdvertiserName()"
                                        value="{{ old('advertiser_name', $prefill['advertiser_name'] ?? '') }}"
                                        :aria-invalid="errors.advertiserName ? 'true' : 'false'"
                                        class="w-full px-4 py-3 border-2 rounded-xl focus:border-[#e04ecb] focus:ring-2 focus:ring-[#e04ecb]/20 transition text-gray-900 font-semibold"
                                        :class="errors.advertiserName ? 'border-red-500 focus:ring-red-200' : 'border-gray-200'"
                                    >
                                    <div class="mt-1 min-h-5">
                                        @error('advertiser_name')
                                            <p class="text-xs text-red-600" data-server-error="true" data-field="advertiserName">{{ $message }}</p>
                                        @enderror
                                        <template x-if="touched.advertiserName && errors.advertiserName">
                                            <p class="text-xs text-red-600" x-text="errors.advertiserName"></p>
                                        </template>
                                    </div>
                                </div>
                                <div>
                                    <label class="block font-semibold text-gray-800 mb-1">Listing ID</label>
                                    <input type="text" name="listing_id" value="{{ old('listing_id', $prefill['listing_id'] ?? '') }}" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-[#e04ecb] focus:ring-2 focus:ring-[#e04ecb]/20 transition text-gray-900 font-semibold">
                                </div>
                                <div>
                                    <label class="block font-semibold text-gray-800 mb-1">Phone Number</label>
                                    <input type="text" name="listing_phone" value="{{ old('listing_phone') }}" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-[#e04ecb] focus:ring-2 focus:ring-[#e04ecb]/20 transition text-gray-900 font-semibold">
                                </div>
                                <div>
                                    <label class="block font-semibold text-gray-800 mb-1">City / Location</label>
                                    <input type="text" name="listing_location" value="{{ old('listing_location') }}" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-[#e04ecb] focus:ring-2 focus:ring-[#e04ecb]/20 transition text-gray-900 font-semibold">
                                </div>
                            </div>
                        </section>

                        <section class="space-y-4">
                            <h2 class="text-xl font-bold text-gray-900">Reporter Information</h2>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <label class="block font-semibold text-gray-800 mb-1">Full Name</label>
                                    <input type="text" name="reporter_name" value="{{ old('reporter_name') }}" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-[#e04ecb] focus:ring-2 focus:ring-[#e04ecb]/20 transition text-gray-900 font-semibold">
                                </div>
                                <div>
                                    <label class="block font-semibold text-gray-800 mb-1">Email Address <span class="text-red-500">*</span></label>
                                    <input
                                        type="email"
                                        name="reporter_email"
                                        x-ref="reporterEmail"
                                        x-model="reporterEmail"
                                        @input="touched.reporterEmail = true; validateReporterEmail()"
                                        @blur="touched.reporterEmail = true; validateReporterEmail()"
                                        value="{{ old('reporter_email') }}"
                                        :aria-invalid="errors.reporterEmail ? 'true' : 'false'"
                                        class="w-full px-4 py-3 border-2 rounded-xl focus:border-[#e04ecb] focus:ring-2 focus:ring-[#e04ecb]/20 transition text-gray-900 font-semibold"
                                        :class="errors.reporterEmail ? 'border-red-500 focus:ring-red-200' : 'border-gray-200'"
                                    >
                                    <div class="mt-1 min-h-5">
                                        @error('reporter_email')
                                            <p class="text-xs text-red-600" data-server-error="true" data-field="reporterEmail">{{ $message }}</p>
                                        @enderror
                                        <template x-if="touched.reporterEmail && errors.reporterEmail">
                                            <p class="text-xs text-red-600" x-text="errors.reporterEmail"></p>
                                        </template>
                                    </div>
                                </div>
                                <div>
                                    <label class="block font-semibold text-gray-800 mb-1">Phone Number</label>
                                    <input type="text" name="reporter_phone" value="{{ old('reporter_phone') }}" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-[#e04ecb] focus:ring-2 focus:ring-[#e04ecb]/20 transition text-gray-900 font-semibold">
                                </div>
                            </div>
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="is_anonymous" value="1" @checked(old('is_anonymous')) class="rounded border-gray-300 text-pink-600 focus:ring-pink-500">
                                Submit anonymously
                            </label>
                        </section>

                        <section class="space-y-3">
                            <h2 class="text-xl font-bold text-gray-900">Report Details</h2>
                            <label class="block font-semibold text-gray-800 mb-1">Describe the issue <span class="text-red-500">*</span></label>
                            <textarea
                                name="description"
                                rows="6"
                                x-ref="description"
                                x-model="description"
                                @input="touched.description = true; validateDescription()"
                                @blur="touched.description = true; validateDescription()"
                                :aria-invalid="errors.description ? 'true' : 'false'"
                                class="w-full px-4 py-3 border-2 rounded-xl focus:border-[#e04ecb] focus:ring-2 focus:ring-[#e04ecb]/20 transition text-gray-900 font-semibold"
                                :class="errors.description ? 'border-red-500 focus:ring-red-200' : 'border-gray-200'"
                                placeholder="Please explain what content you are reporting, why it violates our policies, and provide any relevant details that may assist our review."
                            >{{ old('description') }}</textarea>
                            <div class="min-h-5">
                                @error('description')
                                    <p class="text-xs text-red-600" data-server-error="true" data-field="description">{{ $message }}</p>
                                @enderror
                                <template x-if="touched.description && errors.description">
                                    <p class="text-xs text-red-600" x-text="errors.description"></p>
                                </template>
                            </div>
                        </section>

                        <section class="space-y-3">
                            <h2 class="text-xl font-bold text-gray-900">Evidence Upload</h2>
                            <p class="text-sm text-gray-600">Upload screenshots or images as supporting evidence. Accepted formats: JPG, PNG, PDF (max 10MB per file).</p>

                            {{-- Hidden inputs that will be populated with the managed files on submit --}}
                            <input x-ref="evidenceInput" type="file" name="evidence[]" multiple class="hidden">
                            <input x-ref="evidenceFileInput" type="file" multiple accept=".jpg,.jpeg,.png,.pdf" class="hidden" @change="handleEvidenceFileSelect($event)">

                            <div
                                class="rounded-xl border-2 border-dashed p-6 text-center transition"
                                :class="isDraggingEvidence ? 'border-[#e04ecb] bg-pink-50' : 'border-gray-300 bg-white'"
                                @dragenter.prevent="isDraggingEvidence = true"
                                @dragover.prevent="isDraggingEvidence = true"
                                @dragleave.prevent="isDraggingEvidence = false"
                                @drop.prevent="handleEvidenceDrop($event)"
                            >
                                <div class="mb-3 text-4xl">📎</div>
                                <p class="text-sm font-semibold text-gray-700">Drag &amp; drop files here or</p>
                                <button
                                    type="button"
                                    @click="openEvidenceFilePicker()"
                                    class="mt-3 inline-flex items-center rounded-full bg-pink-600 px-5 py-2 text-sm font-semibold text-white shadow transition hover:bg-pink-700"
                                >
                                    Browse files
                                </button>
                                <p class="mt-2 text-xs text-gray-400">JPG, PNG, PDF — max 10 MB each</p>
                            </div>

                            <template x-if="evidenceFiles.length > 0">
                                <div class="rounded-xl border border-gray-200 bg-white p-4">
                                    <div class="mb-3 flex items-center justify-between">
                                        <p class="text-sm font-semibold text-gray-700">
                                            Selected (<span x-text="evidenceFiles.length"></span>)
                                        </p>
                                        <button
                                            type="button"
                                            @click="evidenceFiles.forEach(e => { if (e.preview && e.preview !== 'pdf') URL.revokeObjectURL(e.preview); }); evidenceFiles = []"
                                            class="text-xs font-semibold text-red-500 hover:text-red-700"
                                        >
                                            Remove all
                                        </button>
                                    </div>
                                    <div class="grid grid-cols-3 gap-2 sm:grid-cols-4 md:grid-cols-5">
                                        <template x-for="(entry, index) in evidenceFiles" :key="entry.name + '-' + index">
                                            <div class="group relative aspect-square overflow-hidden rounded-lg border border-gray-200 bg-gray-50">
                                                <template x-if="entry.preview !== 'pdf'">
                                                    <img :src="entry.preview" :alt="'Evidence ' + (index + 1)" class="h-full w-full object-cover" loading="lazy">
                                                </template>
                                                <template x-if="entry.preview === 'pdf'">
                                                    <div class="flex h-full w-full flex-col items-center justify-center gap-1 px-1 text-center">
                                                        <svg class="h-8 w-8 text-red-500" fill="currentColor" viewBox="0 0 24 24">
                                                            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm-1 1.5L18.5 9H13V3.5zM8.5 17H8v1H7v-4h1.5c.83 0 1.5.67 1.5 1.5S9.33 17 8.5 17zm4.5-1h-1v3h-1v-4h2c.55 0 1 .45 1 1v1c0 .55-.45 1-1 1zm4-2h-2v4h-1v-4h-1v-1h4v1z"/>
                                                        </svg>
                                                        <span class="line-clamp-2 text-[10px] text-gray-600" x-text="entry.name"></span>
                                                    </div>
                                                </template>
                                                <button
                                                    type="button"
                                                    @click="removeEvidenceFile(index)"
                                                    class="absolute right-1 top-1 flex h-5 w-5 items-center justify-center rounded-full border border-red-200 bg-white/90 text-red-600 opacity-0 transition-opacity hover:bg-red-50 group-hover:opacity-100"
                                                    :aria-label="'Remove evidence ' + (index + 1)"
                                                >
                                                    <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </template>
                                        {{-- Add more button --}}
                                        <button
                                            type="button"
                                            @click="openEvidenceFilePicker()"
                                            class="flex aspect-square items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 text-gray-400 transition hover:border-pink-400 hover:text-pink-500"
                                            aria-label="Add more files"
                                        >
                                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </template>

                            @error('evidence')
                                <p class="text-xs text-red-600">{{ $message }}</p>
                            @enderror
                            @error('evidence.*')
                                <p class="text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </section>

                        <section class="space-y-3">
                            <h2 class="text-xl font-bold text-gray-900">Urgent Removal Request</h2>
                            <label class="flex items-start gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="is_urgent" value="1" @checked(old('is_urgent')) class="mt-0.5 rounded border-gray-300 text-pink-600 focus:ring-pink-500">
                                This content should be reviewed urgently
                            </label>
                            <label class="flex items-start gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="is_person_shown" value="1" @checked(old('is_person_shown')) class="mt-0.5 rounded border-gray-300 text-pink-600 focus:ring-pink-500">
                                I am the person shown in this listing/content
                            </label>
                        </section>

                        <section class="space-y-3">
                            <h2 class="text-xl font-bold text-gray-900">Declaration</h2>
                            <label class="flex items-start gap-2 text-sm text-gray-700">
                                <input
                                    type="checkbox"
                                    name="declaration_accuracy"
                                    x-ref="declarationAccuracy"
                                    x-model="declarationAccuracy"
                                    @change="touched.declarationAccuracy = true; validateDeclarationAccuracy()"
                                    value="1"
                                    @checked(old('declaration_accuracy'))
                                    class="mt-0.5 rounded border-gray-300 text-pink-600 focus:ring-pink-500"
                                >
                                I confirm the information provided is accurate to the best of my knowledge.
                            </label>
                            <div class="min-h-5">
                                @error('declaration_accuracy')
                                    <p class="text-xs text-red-600" data-server-error="true" data-field="declarationAccuracy">{{ $message }}</p>
                                @enderror
                                <template x-if="touched.declarationAccuracy && errors.declarationAccuracy">
                                    <p class="text-xs text-red-600" x-text="errors.declarationAccuracy"></p>
                                </template>
                            </div>
                            <label class="flex items-start gap-2 text-sm text-gray-700">
                                <input
                                    type="checkbox"
                                    name="declaration_contact"
                                    x-ref="declarationContact"
                                    x-model="declarationContact"
                                    @change="touched.declarationContact = true; validateDeclarationContact()"
                                    value="1"
                                    @checked(old('declaration_contact'))
                                    class="mt-0.5 rounded border-gray-300 text-pink-600 focus:ring-pink-500"
                                >
                                I understand HotEscort may contact me for additional information regarding this report.
                            </label>
                            <div class="min-h-5">
                                @error('declaration_contact')
                                    <p class="text-xs text-red-600" data-server-error="true" data-field="declarationContact">{{ $message }}</p>
                                @enderror
                                <template x-if="touched.declarationContact && errors.declarationContact">
                                    <p class="text-xs text-red-600" x-text="errors.declarationContact"></p>
                                </template>
                            </div>
                        </section>

                        <button type="submit" class="w-full bg-gradient-to-r from-[#e04ecb] to-[#c13ab0] text-white font-bold text-lg py-4 rounded-full shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition transform duration-200">
                            Submit Report
                        </button>
                    </form>
                </div>

                <div class="space-y-6">
                    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-md">
                        <h2 class="mb-2 text-xl font-semibold text-gray-900">Helpful Links</h2>
                        <ul class="space-y-2 text-pink-600">
                            <li><a href="{{ route('terms-and-conditions') }}" class="hover:text-pink-700">Terms & Conditions</a></li>
                            <li><a href="{{ route('privacy-policy') }}" class="hover:text-pink-700">Privacy Policy</a></li>
                            <li><a href="{{ route('content-moderation-policy') }}" class="hover:text-pink-700">Content Moderation Policy</a></li>
                            <li><a href="{{ route('age-and-consent-policy') }}" class="hover:text-pink-700">Age & Consent Policy</a></li>
                            <li><a href="{{ route('prohibited-content-policy') }}" class="hover:text-pink-700">Prohibited Content & Services Policy</a></li>
                            <li><a href="{{ route('contact-us') }}" class="hover:text-pink-700">Contact Support</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
@endsection
