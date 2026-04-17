@extends('layouts.frontend')

@section('content')
<div class="bg-[#f8fafc] min-h-screen py-10">
    <div class="max-w-3xl lg:max-w-4xl mx-auto px-5">

        <a href="{{ url('/my-profile') }}" class="inline-flex items-center text-[#e04ecb] hover:text-[#c13ab0] transition-colors mb-4 text-sm font-medium">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to dashboard
        </a>

        <div class="mb-8">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800 pl-4">
                Change Your Email
            </h2>
        </div>

        <p class="text-gray-600 mb-8 text-lg">
            Update the email address associated with your account. You will need to verify your new email after saving.
        </p>

        <div
            x-data="emailForm({
                updateUrl: @js(route('change-email.update')),
                csrfToken: @js(csrf_token()),
                currentEmail: @js(auth()->user()->email)
            })"
            class="bg-white rounded-2xl p-6 md:p-10 shadow-md border border-gray-100"
        >
            <form @submit.prevent="submitForm">
                @csrf

                <div class="mb-6">
                    <label class="block font-semibold text-gray-800 mb-1">
                        Current email
                    </label>
                    <p class="px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl text-gray-700 text-base" x-text="currentEmail"></p>
                </div>

                <div class="mb-6">
                    <label class="block font-semibold text-gray-800 mb-1">
                        New email address <span class="text-red-600">*</span>
                    </label>
                    <input
                        type="email"
                        x-model="form.new_email"
                        @input="clearFieldError('new_email')"
                        :class="{ 'border-red-500 ring-red-200': errors.new_email }"
                        placeholder="Enter your new email address"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-[#e04ecb] focus:ring-2 focus:ring-[#e04ecb]/20 transition bg-white text-gray-900 placeholder-gray-500 text-base"
                        required
                    >
                    <template x-if="errors.new_email">
                        <p class="text-red-600 text-sm mt-2" x-text="Array.isArray(errors.new_email) ? errors.new_email[0] : errors.new_email"></p>
                    </template>
                </div>

                <div class="mb-8">
                    <label class="block font-semibold text-gray-800 mb-1">
                        Current password <span class="text-red-600">*</span>
                    </label>
                    <input
                        type="password"
                        x-model="form.current_password"
                        @input="clearFieldError('current_password')"
                        :class="{ 'border-red-500 ring-red-200': errors.current_password }"
                        placeholder="Enter your current password to confirm"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-[#e04ecb] focus:ring-2 focus:ring-[#e04ecb]/20 transition bg-white text-gray-900 placeholder-gray-500 text-base"
                        required
                    >
                    <template x-if="errors.current_password">
                        <p class="text-red-600 text-sm mt-2" x-text="Array.isArray(errors.current_password) ? errors.current_password[0] : errors.current_password"></p>
                    </template>
                </div>

                <button
                    type="submit"
                    :disabled="loading"
                    class="w-full bg-gradient-to-r from-[#e04ecb] to-[#c13ab0] text-white font-bold text-xl py-4 rounded-full shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition transform duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span x-show="!loading" x-cloak>UPDATE EMAIL</span>
                    <span x-show="loading" x-cloak class="flex items-center justify-center gap-2">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Updating...
                    </span>
                </button>
            </form>

            <template x-if="message">
                <div
                    class="mt-6 rounded-xl p-4"
                    :class="message.type === 'success'
                        ? 'bg-green-50 text-green-800 border border-green-200'
                        : 'bg-red-50 text-red-800 border border-red-200'"
                >
                    <p class="text-sm" x-text="message.text"></p>
                </div>
            </template>

            <div class="text-center border-t border-gray-200 mt-8 pt-6">
                <p class="text-gray-500 text-sm">
                    <a href="{{ url('/my-profile') }}" class="text-[#e04ecb] font-medium border-b border-dotted border-[#e04ecb] hover:text-[#c13ab0] hover:border-[#c13ab0] transition">
                        Return to dashboard
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script src="{{ asset('auth/js/change-email.js') }}"></script>
@endpush
@endsection
