@extends('layouts.frontend')

@section('content')
<div class="min-h-screen bg-gray-50">
    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="min-h-[600px] rounded-lg bg-white p-6 shadow-sm sm:p-8">
            <button
                type="button"
                onclick="window.history.back()"
                class="inline-flex items-center text-pink-500 hover:text-pink-600 transition-colors mb-6 text-sm font-medium bg-transparent border-0 cursor-pointer"
            >
                <span class="mr-1">&lt;</span> back
            </button>

            <h1 class="text-3xl font-bold mb-3 text-gray-900">Delete Account</h1>
            <p class="text-sm text-gray-600 mb-6">
                Your account and related data will be kept for 30 days. You can request account restoration within this period. After 30 days, your account and related data will be permanently deleted or anonymised where legally required or legally allowed.
            </p>

            @if(session('error'))
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <ul class="space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="delete-account-form" action="{{ route('account.destroy') }}" method="POST" class="space-y-5 max-w-3xl">
                @csrf
                @method('DELETE')

                <div>
                    <label for="confirmation_text" class="block text-sm font-semibold text-gray-700 mb-2">
                        Type <span class="text-red-600 font-bold">DELETE</span> to confirm
                    </label>
                    <input
                        type="text"
                        name="confirmation_text"
                        id="confirmation_text"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-pink-500"
                        placeholder="Type DELETE"
                        required
                    >
                </div>

                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    After 30 days, your account and related data will be permanently deleted or anonymised where legally required or legally allowed.
                </div>

                <div class="flex flex-wrap gap-3">
                    <button
                        type="submit"
                        class="inline-flex items-center px-5 py-2.5 rounded bg-red-600 hover:bg-red-700 text-white text-sm font-semibold transition"
                    >
                        Delete my account
                    </button>

                    <a href="{{ route('contact-us') }}" class="inline-flex items-center px-5 py-2.5 rounded bg-pink-500 hover:bg-pink-600 text-white text-sm font-semibold transition">
                        Contact support
                    </a>

                    <a href="{{ url('/profile') }}" class="inline-flex items-center px-5 py-2.5 rounded bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </main>
</div>
@endsection

@push('scripts')
    <script>
        (function () {
            const deleteForm = document.getElementById('delete-account-form');

            if (deleteForm) {
                deleteForm.addEventListener('submit', async function (event) {
                    event.preventDefault();

                    const result = await Swal.fire({
                        title: 'Delete account now?',
                        text: 'This will immediately soft-delete your account and start a 30-day restoration period.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Delete account',
                        cancelButtonText: 'Cancel',
                        confirmButtonColor: '#dc2626',
                    });

                    if (result.isConfirmed) {
                        deleteForm.submit();
                    }
                });
            }
        })();
    </script>

    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Account deleted',
                text: @json(session('success')),
                confirmButtonColor: '#db2777',
            });
        </script>
    @endif
@endpush
