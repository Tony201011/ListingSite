@extends('layouts.frontend')

@section('content')

<div class="bg-[#f8fafc] min-h-screen py-10">
    <div class="max-w-3xl lg:max-w-4xl mx-auto px-5">

        <div class="mb-8">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800 border-l-4 border-[#e04ecb] pl-4">
                Verify Your <span class="text-[#e04ecb]">Email</span>
            </h2>
        </div>

        <hr class="border-t-2 border-gray-200 mb-8">

        @if (session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 rounded-xl px-5 py-4 mb-6 text-sm font-medium">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-2xl p-6 md:p-10 shadow-md border border-gray-100">

            <p class="text-gray-600 mb-6 text-sm leading-relaxed">
                Before continuing, please verify your email address by clicking the link we sent you.
                If you did not receive the email, click the button below to request a new one.
            </p>

            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit"
                    class="inline-block bg-[#e04ecb] hover:bg-[#c13ab0] text-white font-semibold text-sm px-6 py-3 rounded-full transition-colors duration-200">
                    Resend Verification Email
                </button>
            </form>

        </div>
    </div>
</div>

@endsection
