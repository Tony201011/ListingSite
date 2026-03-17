@extends('layouts.frontend')

@section('content')
<div class="min-h-screen bg-gray-50 py-10 px-4 sm:px-6 lg:px-8"
     x-data="shortUrlForm()"
     x-init="init('{{ $slug }}')">
    <div class="max-w-3xl mx-auto">
        <a href="{{ url('/view-profile-setting') }}" class="inline-flex items-center text-[#e04ecb] hover:text-[#c13ab0] text-sm font-medium mb-4">
            <span class="mr-1">&lt;</span> Back to profile settings
        </a>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-3">Short URL</h1>
            <p class="text-gray-600 mb-4">Set a clean URL that is easy to share on socials and messages.</p>

            <div class="flex items-center rounded-lg border border-gray-200 overflow-hidden bg-white">
                <span class="px-3 py-2.5 bg-gray-50 text-gray-500 text-sm whitespace-nowrap border-r border-gray-200">
                    {{ config('app.url') }}/u/
                </span>
                <input
                    type="text"
                    x-model="slug"
                    @input="clearError"
                    class="flex-1 px-3 py-2.5 focus:outline-none text-gray-900 bg-white placeholder-gray-400"
                    placeholder="your-custom-slug"
                >
            </div>

            <template x-if="error">
                <p class="text-red-600 text-sm mt-2" x-text="error"></p>
            </template>

            <div class="flex items-center gap-3 mt-4">
                <button
                    @click="saveSlug"
                    :disabled="saving"
                    class="px-5 py-2.5 rounded-lg bg-pink-600 hover:bg-pink-700 text-white font-semibold transition disabled:opacity-50 flex items-center gap-2"
                >
                    <span x-show="!saving">Save URL</span>
                    <span x-show="saving" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Saving...
                    </span>
                </button>
            </div>

            <template x-if="successMessage">
                <p class="text-green-600 text-sm mt-2" x-text="successMessage"></p>
            </template>

            <div class="mt-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                <p class="text-sm text-gray-600">Your full short URL:</p>
                <a :href="fullUrl" target="_blank" class="text-[#e04ecb] break-all hover:underline" x-text="fullUrl"></a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function shortUrlForm() {
    return {
        slug: '',
        originalSlug: '',
        saving: false,
        error: '',
        successMessage: '',

        init(initialSlug) {
            this.slug = initialSlug;
            this.originalSlug = initialSlug;
        },

        get fullUrl() {
            return '{{ config('app.url') }}/u/' + this.slug;
        },

        clearError() {
            this.error = '';
            this.successMessage = '';
        },

        async saveSlug() {
            if (this.slug === this.originalSlug) {
                this.successMessage = 'No changes to save.';
                setTimeout(() => this.successMessage = '', 3000);
                return;
            }

            this.saving = true;
            this.error = '';
            this.successMessage = '';

            try {
                const response = await fetch('{{ route("short-url-update") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ slug: this.slug }),
                });

                const data = await response.json();

                if (!response.ok) {
                    if (response.status === 422 && data.errors) {
                        // Validation error
                        const firstError = Object.values(data.errors)[0][0];
                        throw new Error(firstError);
                    } else {
                        throw new Error(data.message || 'Something went wrong.');
                    }
                }

                this.originalSlug = data.slug;
                this.slug = data.slug; // ensure it's the saved version
                this.successMessage = data.message;
                setTimeout(() => this.successMessage = '', 3000);
            } catch (error) {
                this.error = error.message;
            } finally {
                this.saving = false;
            }
        }
    }
}
</script>
@endpush
@endsection
