@extends('layouts.frontend')

@section('title', 'Social Login')

@section('content')
    <main class="mx-auto flex min-h-[70vh] max-w-md items-center px-4 py-12 sm:px-6 lg:px-8">
        <section class="w-full rounded-xl border border-gray-700 bg-gray-800 p-8 shadow-sm">
            <h1 class="text-2xl font-bold text-white">Login with Social Account</h1>
            <p class="mt-2 text-sm text-gray-400">Choose any enabled provider to continue.</p>

            <div class="mt-6 space-y-3">
                @forelse ($providers as $provider)
                    <a
                        href="{{ route('social.redirect', $provider->provider) }}"
                        class="block w-full rounded-lg border border-gray-600 px-4 py-3 text-center text-sm font-medium text-gray-100 transition hover:bg-gray-700"
                    >
                        Continue with {{ ucfirst($provider->provider) }}
                    </a>
                @empty
                    <p class="text-sm text-gray-400">No social login provider is enabled yet. Please contact admin.</p>
                @endforelse
            </div>
        </section>
    </main>
@endsection