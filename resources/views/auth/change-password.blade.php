@extends('layouts.frontend')

@section('content')
<div class="bg-[#f8fafc] min-h-screen py-10">
    <div class="max-w-3xl lg:max-w-4xl mx-auto px-5">

        {{-- Back link styled like the reset password page --}}
        <a href="{{ url('/after-image-upload') }}" class="inline-flex items-center text-[#e04ecb] hover:text-[#c13ab0] transition-colors mb-4 text-sm font-medium">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to dashboard
        </a>

        {{-- Header with left border (removed border to match reset password) --}}
        <div class="mb-8">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800 pl-4">
                Change Your Password
            </h2>
        </div>

        {{-- Description (optional – you can keep or remove) --}}
        <p class="text-gray-600 mb-8 text-lg">
            Update your account password to keep your profile secure.
        </p>

        {{-- Main Card with Alpine component --}}
        <div x-data="passwordForm()" class="bg-white rounded-2xl p-6 md:p-10 shadow-md border border-gray-100">
            <form @submit.prevent="submitForm">
                @csrf

                {{-- Current Password --}}
                <div class="mb-6">
                    <label class="block font-semibold text-gray-800 mb-1">
                        Current password <span class="text-red-600">*</span>
                    </label>
                    <input
                        type="password"
                        x-model="form.current_password"
                        @input="clearFieldError('current_password')"
                        :class="{'border-red-500 ring-red-200': errors.current_password}"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-[#e04ecb] focus:ring-2 focus:ring-[#e04ecb]/20 transition bg-white text-gray-900 placeholder-gray-500 text-base"
                        required
                    >
                    <template x-if="errors.current_password">
                        <p class="text-red-600 text-sm mt-2" x-text="errors.current_password[0]"></p>
                    </template>
                </div>

                {{-- New Password with show/hide, strength meter, and generate popup --}}
                <div class="mb-6 relative">
                    <label class="block font-semibold text-gray-800 mb-1">
                        New password <span class="text-red-600">*</span>
                    </label>

                    <div class="flex gap-2">
                        <div class="relative flex-1">
                            <input
                                :type="showPassword ? 'text' : 'password'"
                                x-model="form.new_password"
                                @input="validateNewPassword(); validatePasswordMatch();"
                                placeholder="Enter your new password"
                                class="w-full px-4 py-3 pr-20 border-2 border-gray-200 rounded-xl focus:border-[#e04ecb] focus:ring-2 focus:ring-[#e04ecb]/20 transition bg-white text-gray-900 placeholder-gray-500 text-base"
                                required
                            >

                            {{-- Show/Hide toggle --}}
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-[#e04ecb]"
                                :aria-label="showPassword ? 'Hide password' : 'Show password'"
                                :title="showPassword ? 'Hide password' : 'Show password'"
                            >
                                <svg x-show="!showPassword" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: none;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7S3.732 16.057 2.458 12z" />
                                </svg>
                                <svg x-show="showPassword" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: none;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.956 9.956 0 012.252-3.592M6.223 6.223A9.956 9.956 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.969 9.969 0 01-4.132 5.411M15 12a3 3 0 00-4.243-2.829M9.88 9.88A3 3 0 0014.12 14.12" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Strength meter --}}
                    <div class="mt-2">
                        <div class="flex items-center justify-between text-xs mb-1">
                            <span class="text-gray-500">Use 8+ characters with uppercase, lowercase, number and symbol</span>
                            <span
                                class="font-semibold"
                                :class="passwordStrength.text === 'Strong' ? 'text-green-600' : (passwordStrength.text === 'Medium' ? 'text-yellow-600' : 'text-red-600')"
                                x-text="passwordStrength.text"
                            ></span>
                        </div>
                        <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                            <div
                                class="h-full rounded-full transition-all duration-300"
                                :class="passwordStrength.color"
                                :style="`width: ${passwordStrength.width}`"
                            ></div>
                        </div>
                    </div>

                    {{-- Generate button --}}
                    <button
                        type="button"
                        @click="generatePasswordPopup()"
                        class="mt-2 px-4 py-2 rounded-xl bg-[#fdf0fb] text-[#c13ab0] font-semibold border border-[#f3c4ea] hover:bg-[#fae3f6] transition"
                    >
                        Generate
                    </button>

                    {{-- Client‑side error for new password --}}
                    <template x-if="errors.new_password">
                        <div class="text-xs text-red-600 mt-1" x-text="errors.new_password"></div>
                    </template>

                    {{-- Password suggestion popup (exactly like reset password page) --}}
                    <div
                        x-show="showPasswordPopup"
                        x-transition
                        @click.away="showPasswordPopup = false"
                        class="absolute z-20 mt-3 w-full bg-white border border-gray-200 rounded-2xl shadow-xl p-4"
                        style="display: none;"
                    >
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <div>
                                <h4 class="font-bold text-gray-800">Strong password suggestion</h4>
                                <p class="text-sm text-gray-500">Save this password somewhere safe before using it.</p>
                            </div>
                            <button
                                type="button"
                                @click="showPasswordPopup = false"
                                class="text-gray-400 hover:text-gray-600 text-xl leading-none"
                            >
                                x
                            </button>
                        </div>

                        <div
                            class="bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 font-mono text-sm break-all text-gray-800 mb-4"
                            x-text="generatedPassword"
                        ></div>

                        <div class="flex flex-wrap gap-2">
                            <button
                                type="button"
                                @click="generatePasswordPopup()"
                                class="px-4 py-2 rounded-lg border border-gray-200 text-gray-700 font-medium hover:bg-gray-50"
                            >
                                Regenerate
                            </button>

                            <button
                                type="button"
                                @click="copyGeneratedPassword()"
                                class="px-4 py-2 rounded-lg border border-gray-200 text-gray-700 font-medium hover:bg-gray-50"
                            >
                                <span x-text="copied ? 'Copied!' : 'Copy'"></span>
                            </button>

                            <button
                                type="button"
                                @click="useGeneratedPassword()"
                                class="px-4 py-2 rounded-lg bg-[#e04ecb] text-white font-semibold hover:bg-[#c13ab0]"
                            >
                                Use this password
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Confirm Password with show/hide toggle --}}
                <div class="mb-8">
                    <label class="block font-semibold text-gray-800 mb-1">
                        Confirm new password <span class="text-red-600">*</span>
                    </label>
                    <div class="relative">
                        <input
                            :type="showConfirmPassword ? 'text' : 'password'"
                            x-model="form.new_password_confirmation"
                            @input="validatePasswordMatch()"
                            placeholder="Confirm your new password"
                            class="w-full px-4 py-3 pr-20 border-2 border-gray-200 rounded-xl focus:border-[#e04ecb] focus:ring-2 focus:ring-[#e04ecb]/20 transition bg-white text-gray-900 placeholder-gray-500 text-base"
                            required
                        >

                        <button
                            type="button"
                            @click="showConfirmPassword = !showConfirmPassword"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-[#e04ecb]"
                            :aria-label="showConfirmPassword ? 'Hide password' : 'Show password'"
                            :title="showConfirmPassword ? 'Hide password' : 'Show password'"
                        >
                            <svg x-show="!showConfirmPassword" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: none;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7S3.732 16.057 2.458 12z" />
                            </svg>
                            <svg x-show="showConfirmPassword" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: none;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.956 9.956 0 012.252-3.592M6.223 6.223A9.956 9.956 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.969 9.969 0 01-4.132 5.411M15 12a3 3 0 00-4.243-2.829M9.88 9.88A3 3 0 0014.12 14.12" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18" />
                            </svg>
                        </button>
                    </div>

                    {{-- Client‑side error for confirm password --}}
                    <template x-if="errors.new_password_confirmation">
                        <div class="text-xs text-red-600 mt-1" x-text="errors.new_password_confirmation[0]"></div>
                    </template>
                </div>

                {{-- Update Password Button (gradient, same as reset password) --}}
                <button
                    type="submit"
                    :disabled="loading"
                    class="w-full bg-gradient-to-r from-[#e04ecb] to-[#c13ab0] text-white font-bold text-xl py-4 rounded-full shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition transform duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span x-show="!loading">UPDATE PASSWORD</span>
                    <span x-show="loading" class="flex items-center justify-center gap-2">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Updating...
                    </span>
                </button>
            </form>

            {{-- Global success/error messages (from AJAX) --}}
            <template x-if="message">
                <div class="mt-6 rounded-xl p-4" :class="message.type === 'success' ? 'bg-green-50 text-green-800 border border-green-200' : 'bg-red-50 text-red-800 border border-red-200'">
                    <p class="text-sm" x-text="message.text"></p>
                </div>
            </template>

            {{-- Optional footer link --}}
            <div class="text-center border-t border-gray-200 mt-8 pt-6">
                <p class="text-gray-500 text-sm">
                    <a href="{{ url('/after-image-upload') }}" class="text-[#e04ecb] font-medium border-b border-dotted border-[#e04ecb] hover:text-[#c13ab0] hover:border-[#c13ab0] transition">
                        Return to dashboard
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function passwordForm() {
    return {
        // Form data
        form: {
            current_password: '',
            new_password: '',
            new_password_confirmation: '',
        },
        // UI state
        showPassword: false,
        showConfirmPassword: false,
        showPasswordPopup: false,
        generatedPassword: '',
        copied: false,
        loading: false,
        errors: {},
        message: null,

        // ===== Validation Methods =====
        validateNewPassword() {
            // Clear existing error if any
            delete this.errors.new_password;

            if (!this.form.new_password) {
                this.errors.new_password = 'New password is required.';
                return;
            }
            if (this.form.new_password.length < 8) {
                this.errors.new_password = 'Password must be at least 8 characters.';
                return;
            }
            const hasUpper = /[A-Z]/.test(this.form.new_password);
            const hasLower = /[a-z]/.test(this.form.new_password);
            const hasNumber = /[0-9]/.test(this.form.new_password);
            const hasSymbol = /[^A-Za-z0-9]/.test(this.form.new_password);
            if (!(hasUpper && hasLower && hasNumber && hasSymbol)) {
                this.errors.new_password = 'Use uppercase, lowercase, number and symbol for a stronger password.';
                return;
            }
        },

        validatePasswordMatch() {
            delete this.errors.new_password_confirmation;
            if (!this.form.new_password_confirmation) {
                this.errors.new_password_confirmation = ['Please confirm your password.'];
                return;
            }
            if (this.form.new_password !== this.form.new_password_confirmation) {
                this.errors.new_password_confirmation = ['Passwords do not match.'];
            }
        },

        clearFieldError(field) {
            if (this.errors[field]) {
                delete this.errors[field];
            }
        },

        // ===== Password Generation =====
        generateRandomPassword(length = 16) {
            const upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            const lower = 'abcdefghijklmnopqrstuvwxyz';
            const numbers = '0123456789';
            const symbols = '!@#$%^&*()-_=+[]{}?';
            const all = upper + lower + numbers + symbols;

            let password = '';
            // Ensure at least one of each required type
            password += upper[Math.floor(Math.random() * upper.length)];
            password += lower[Math.floor(Math.random() * lower.length)];
            password += numbers[Math.floor(Math.random() * numbers.length)];
            password += symbols[Math.floor(Math.random() * symbols.length)];

            for (let i = password.length; i < length; i++) {
                password += all[Math.floor(Math.random() * all.length)];
            }

            // Shuffle to avoid predictable pattern
            return password.split('').sort(() => Math.random() - 0.5).join('');
        },

        generatePasswordPopup() {
            this.generatedPassword = this.generateRandomPassword();
            this.copied = false;
            this.showPasswordPopup = true;
        },

        useGeneratedPassword() {
            this.form.new_password = this.generatedPassword;
            this.form.new_password_confirmation = this.generatedPassword;
            this.validateNewPassword();
            this.validatePasswordMatch();
            this.showPasswordPopup = false;
        },

        async copyGeneratedPassword() {
            try {
                await navigator.clipboard.writeText(this.generatedPassword);
                this.copied = true;
                setTimeout(() => this.copied = false, 1500);
            } catch (e) {
                // fallback – ignore
            }
        },

        // ===== Password Strength Meter =====
        get passwordStrength() {
            let score = 0;
            const pwd = this.form.new_password;

            if (!pwd) {
                return { text: '', color: '', width: '0%' };
            }
            if (pwd.length >= 8) score++;
            if (/[A-Z]/.test(pwd)) score++;
            if (/[a-z]/.test(pwd)) score++;
            if (/[0-9]/.test(pwd)) score++;
            if (/[^A-Za-z0-9]/.test(pwd)) score++;

            if (score <= 2) return { text: 'Weak', color: 'bg-red-500', width: '33%' };
            if (score <= 4) return { text: 'Medium', color: 'bg-yellow-500', width: '66%' };
            return { text: 'Strong', color: 'bg-green-500', width: '100%' };
        },

        // ===== AJAX Submission =====
        async submitForm() {
            this.loading = true;
            this.message = null;
            this.errors = {};

            try {
                const response = await fetch('{{ route("change-password.update") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(this.form),
                });

                const data = await response.json();

                if (!response.ok) {
                    if (response.status === 422 && data.errors) {
                        this.errors = data.errors;
                    } else {
                        throw new Error(data.message || 'Something went wrong.');
                    }
                    return;
                }

                // Success
                this.message = { type: 'success', text: data.message };
                this.form = { current_password: '', new_password: '', new_password_confirmation: '' };
            } catch (error) {
                this.message = { type: 'error', text: error.message };
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>
@endpush

{{-- Add x-cloak to prevent flickering --}}
<style>
[x-cloak] { display: none !important; }
</style>
@endsection
