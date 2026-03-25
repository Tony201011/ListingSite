@extends('layouts.frontend')

@section('content')
<div
    class="min-h-screen bg-gray-50 py-10 px-4 sm:px-6 lg:px-8"
    x-data="availableToggle({
        initialStatus: {{ $status ? 'true' : 'false' }},
        initialRemainingUses: {{ $remainingUses }},
        initialExpiresAt: @js($expiresAt ?? null),
        updateUrl: '{{ route("availableUpdateStatus") }}',
        csrfToken: '{{ csrf_token() }}'
    })"
    x-init="init()"
>
    <div class="max-w-3xl mx-auto">
        <a href="{{ url('/view-profile-setting') }}"
           class="inline-flex items-center text-[#e04ecb] hover:text-[#c13ab0] text-sm font-medium mb-4">
            <span class="mr-1">&lt;</span> Back to profile settings
        </a>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-3">Available Now</h1>

            <p class="text-gray-600 mb-2">
                Mark yourself available for immediate enquiries and improve visibility.
            </p>

            <p class="text-sm text-pink-600 font-medium mb-6">
                Promote your availability twice a day for two hours.
            </p>

            <div class="grid gap-4 sm:grid-cols-2 mb-6">
                <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4">
                    <p class="text-sm font-medium text-gray-500">Remaining uses today</p>
                    <p class="mt-2 text-2xl font-bold text-pink-600" x-text="remainingUses"></p>
                </div>

                <div
                    class="rounded-2xl border border-green-100 bg-green-50 p-4"
                    x-show="enabled"
                    x-transition
                >
                    <p class="text-sm font-medium text-green-700">Time left</p>
                    <p class="mt-2 text-2xl font-bold text-green-600 tabular-nums" x-text="countdown"></p>
                </div>
            </div>

            <button
                @click="toggleStatus"
                :disabled="loading || (!enabled && remainingUses <= 0)"
                class="px-5 py-2.5 rounded-lg font-semibold transition flex items-center justify-center gap-2 w-full sm:w-auto"
                :class="enabled
                    ? 'bg-pink-600 text-white hover:bg-pink-700'
                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed'"
            >
                <span x-show="!loading" x-text="enabled ? 'Available now enabled' : 'Enable available now'"></span>

                <span x-show="loading" class="flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Updating...
                </span>
            </button>

            <template x-if="message">
                <div
                    class="mt-4 text-sm"
                    :class="messageType === 'success' ? 'text-green-600' : 'text-red-600'"
                    x-text="message"
                ></div>
            </template>

            <div
                class="mt-6 rounded-2xl border border-yellow-200 bg-yellow-50 p-4 text-sm text-yellow-800"
                x-show="!enabled && remainingUses <= 0"
                x-transition
            >
                You have reached your daily limit for Available Now. Please try again tomorrow.
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function availableToggle({
    initialStatus = false,
    initialRemainingUses = 0,
    initialExpiresAt = null,
    updateUrl = '',
    csrfToken = ''
}) {
    return {
        enabled: initialStatus,
        remainingUses: initialRemainingUses,
        expiresAt: initialExpiresAt,
        loading: false,
        message: '',
        messageType: 'success',
        countdown: '00:00:00',
        timer: null,

        init() {
            if (this.enabled && this.expiresAt) {
                this.startTimer();
            }
        },

        startTimer() {
            this.stopTimer();
            this.updateCountdown();

            this.timer = setInterval(() => {
                this.updateCountdown();
            }, 1000);
        },

        stopTimer() {
            if (this.timer) {
                clearInterval(this.timer);
                this.timer = null;
            }
        },

        updateCountdown() {
            if (!this.expiresAt) {
                this.countdown = '00:00:00';
                return;
            }

            const now = new Date().getTime();
            const expiry = new Date(this.expiresAt).getTime();
            const diff = expiry - now;

            if (diff <= 0) {
                this.enabled = false;
                this.expiresAt = null;
                this.countdown = '00:00:00';
                this.stopTimer();
                this.message = 'Your 2-hour available session has ended.';
                this.messageType = 'success';
                return;
            }

            const totalSeconds = Math.floor(diff / 1000);
            const hours = Math.floor(totalSeconds / 3600);
            const minutes = Math.floor((totalSeconds % 3600) / 60);
            const seconds = totalSeconds % 60;

            this.countdown =
                String(hours).padStart(2, '0') + ':' +
                String(minutes).padStart(2, '0') + ':' +
                String(seconds).padStart(2, '0');
        },

        async toggleStatus() {
            this.loading = true;
            this.message = '';

            const newStatus = !this.enabled ? 'online' : 'offline';

            try {
                const response = await fetch(updateUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ status: newStatus }),
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Something went wrong.');
                }

                this.enabled = data.status === 'online';
                this.remainingUses = data.remaining_uses ?? this.remainingUses;
                this.expiresAt = data.expires_at ?? null;
                this.message = data.message || 'Status updated successfully.';
                this.messageType = 'success';

                if (this.enabled && this.expiresAt) {
                    this.startTimer();
                } else {
                    this.stopTimer();
                    this.countdown = '00:00:00';
                }

                setTimeout(() => this.message = '', 3000);
            } catch (error) {
                this.message = error.message || 'Something went wrong.';
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
