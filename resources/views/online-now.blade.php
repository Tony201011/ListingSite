@extends('layouts.frontend')

@section('content')
<div class="min-h-screen bg-gray-50 py-10 px-4 sm:px-6 lg:px-8"
     x-data="availableToggle()"
     x-init="init({{ $onlineStatus ? 'true' : 'false' }})">
    <div class="max-w-3xl mx-auto">
        <a href="{{ url('/view-profile-setting') }}"
           class="inline-flex items-center text-[#e04ecb] hover:text-[#c13ab0] text-sm font-medium mb-4">
            <span class="mr-1">&lt;</span> Back to profile settings
        </a>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-3">Online Now</h1>
            <p class="text-gray-600 mb-6">Mark yourself available for Online enquiries and improve visibility.</p>

            <button
                @click="toggleStatus"
                :disabled="loading"
                class="px-5 py-2.5 rounded-lg font-semibold transition flex items-center justify-center gap-2 w-full sm:w-auto"
                :class="enabled ? 'bg-pink-600 text-white hover:bg-pink-700' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
            >
                <span x-show="!loading" x-text="enabled ? 'Online Now Enabled' : 'Enable Online Now'"></span>
                <span x-show="loading" class="flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Updating...
                </span>
            </button>

            <template x-if="message">
                <div class="mt-4 text-sm" :class="messageType === 'success' ? 'text-green-600' : 'text-red-600'" x-text="message"></div>
            </template>
        </div>
    </div>
</div>

@push('scripts')
<script>
function availableToggle() {
    return {
        enabled: false,
        loading: false,
        message: '',
        messageType: 'success',

        init(initialStatus) {
            this.enabled = initialStatus;
        },

        async toggleStatus() {
            this.loading = true;
            this.message = '';

            const newStatus = !this.enabled ? 'online' : 'offline';

            try {
                const response = await fetch('{{ route('onlineUpdateStatus') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ status: newStatus }),
                });

                const data = await response.json();

                if (!response.ok) {
                    console.error('Server error:', data);
                    throw new Error(data.message || 'Something went wrong.');
                }

                this.enabled = data.status === 'online';
                this.message = data.message;
                this.messageType = 'success';

                setTimeout(() => this.message = '', 3000);
            } catch (error) {
                this.message = error.message;
                this.messageType = 'error';
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>
@endpush
@endsection
