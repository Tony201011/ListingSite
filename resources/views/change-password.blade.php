@extends('layouts.frontend')

@section('content')
<div class="min-h-screen bg-gray-50 px-4 py-10 sm:px-6 lg:px-8">
    <div class="mx-auto w-full max-w-5xl">
        <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="m-0 text-2xl font-bold leading-tight text-gray-900 sm:text-3xl">Change Password</h1>
                <p class="mt-2 text-sm text-gray-600">Update your account password to keep your profile secure.</p>
            </div>
            <a href="{{ url('/after-image-upload') }}" class="text-sm font-medium text-[#e04ecb] transition hover:text-[#c13ab0] hover:underline">&larr; Back to dashboard</a>
        </div>

        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm sm:p-8">
            <form action="#" method="POST" class="mx-auto max-w-2xl space-y-5">
                    @csrf

                    <div>
                        <label for="current_password" class="mb-2 block text-sm font-semibold text-gray-700">Current password <span class="text-rose-600">*</span></label>
                        <input
                            id="current_password"
                            name="current_password"
                            type="password"
                            class="h-11 w-full rounded-lg border border-gray-400 bg-white px-3 text-sm text-gray-700 outline-none ring-1 ring-gray-200/70 transition focus:border-pink-500 focus:ring-2 focus:ring-pink-200"
                            required
                        >
                    </div>

                    <div>
                        <label for="new_password" class="mb-2 block text-sm font-semibold text-gray-700">New password <span class="text-rose-600">*</span></label>
                        <input
                            id="new_password"
                            name="new_password"
                            type="password"
                            class="h-11 w-full rounded-lg border border-gray-400 bg-white px-3 text-sm text-gray-700 outline-none ring-1 ring-gray-200/70 transition focus:border-pink-500 focus:ring-2 focus:ring-pink-200"
                            required
                        >
                    </div>

                    <div>
                        <label for="repeat_new_password" class="mb-2 block text-sm font-semibold text-gray-700">Repeat new password <span class="text-rose-600">*</span></label>
                        <input
                            id="repeat_new_password"
                            name="repeat_new_password"
                            type="password"
                            class="h-11 w-full rounded-lg border border-gray-400 bg-white px-3 text-sm text-gray-700 outline-none ring-1 ring-gray-200/70 transition focus:border-pink-500 focus:ring-2 focus:ring-pink-200"
                            required
                        >
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="inline-flex h-11 items-center rounded-lg bg-[#e04ecb] px-6 text-sm font-semibold text-white transition hover:bg-[#c13ab0]">
                            UPDATE
                        </button>
                    </div>
                </form>
        </div>
    </div>
</div>
@endsection
