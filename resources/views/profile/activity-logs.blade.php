@extends('layouts.frontend')

@section('content')
<div class="min-h-screen bg-gray-50 px-4 py-10 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-5xl">

        <button
            type="button"
            onclick="window.history.back()"
            class="mb-4 inline-flex cursor-pointer items-center border-0 bg-transparent text-sm font-medium text-[#e04ecb] transition-colors hover:text-[#c13ab0]"
        >
            <span class="mr-1">&lt;</span> back
        </button>

        <h1 class="mb-2 text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
            Activity Logs
        </h1>

        <p class="mb-6 text-sm text-gray-500">
            Online and offline session history for
            {{ $profile?->name ?? 'your selected profile' }}
            during {{ $filters['label'] ?? 'last 90 days' }}.
        </p>

        @if ($errors->any())
            <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form
            method="GET"
            action="{{ route('activity-logs') }}"
            class="mb-6 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm"
            aria-label="Activity log filters"
        >

            <div class="mb-4 flex flex-wrap gap-2">
                @foreach ([
                    '30d' => 'Last 30 days',
                    '90d' => 'Last 90 days',
                    'custom' => 'Custom range'
                ] as $value => $label)

                    <label class="al-range-option {{ ($filters['range'] ?? '90d') === $value ? 'is-active' : '' }}">
                        <input
                            type="radio"
                            name="range"
                            value="{{ $value }}"
                            {{ ($filters['range'] ?? '90d') === $value ? 'checked' : '' }}
                            class="al-range-input"
                        >

                        <span>{{ $label }}</span>
                    </label>

                @endforeach
            </div>

            <div class="al-custom-range grid grid-cols-1 gap-3 sm:grid-cols-2 {{ ($filters['range'] ?? '90d') === 'custom' ? '' : 'hidden' }}">

                <label class="block">
                    <span class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">
                        From date
                    </span>

                    <input
                        type="date"
                        name="date_from"
                        value="{{ old('date_from', $filters['date_from_value'] ?? '') }}"
                        class="h-11 w-full rounded-lg border border-gray-200 px-3 text-sm text-gray-700 outline-none transition focus:border-pink-400 focus:ring-2 focus:ring-pink-100"
                    >
                </label>

                <label class="block">
                    <span class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">
                        To date
                    </span>

                    <input
                        type="date"
                        name="date_to"
                        value="{{ old('date_to', $filters['date_to_value'] ?? '') }}"
                        class="h-11 w-full rounded-lg border border-gray-200 px-3 text-sm text-gray-700 outline-none transition focus:border-pink-400 focus:ring-2 focus:ring-pink-100"
                    >
                </label>

            </div>

            <div class="mt-4 flex flex-wrap items-center justify-between gap-3">

                <p class="text-xs text-gray-500">
                    Use a preset duration or choose a custom calendar range.
                </p>

                <div class="flex items-center gap-2">

                    <a
                        href="{{ route('activity-logs') }}"
                        class="inline-flex h-10 items-center rounded-lg border border-gray-200 px-4 text-sm font-medium text-gray-600 transition hover:bg-gray-50"
                    >
                        Reset
                    </a>

                    <button
                        type="submit"
                        class="inline-flex h-10 items-center rounded-lg bg-[#e04ecb] px-4 text-sm font-semibold text-white transition hover:bg-[#c13ab0]"
                    >
                        Apply filter
                    </button>

                </div>

            </div>

        </form>

        {{-- Summary cards --}}
        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-2">

            <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wide text-gray-500">
                    Profile Name
                </p>

                <p class="mt-1 text-base font-bold text-gray-900">
                    {{ $profile?->name ?? 'N/A' }}
                </p>
            </div>

            <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wide text-gray-500">
                    Provider Email
                </p>

                <p class="mt-1 text-base font-bold text-gray-900 break-all">
                    {{ $user->email ?? 'N/A' }}
                </p>
            </div>

        </div>

        {{-- Day-wise session table --}}
        @if (!empty($activity['days']))

            <div class="al-table-wrapper overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">

                <div class="al-table-scroll overflow-x-auto">

                    <table class="al-table w-full border-collapse">

                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Sessions</th>
                                <th>Login Time</th>
                                <th>Logout Time</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>

                            @foreach ($activity['days'] as $day)

                                @php
                                    $dailyTotalSeconds = 0;

                                    foreach (($day['sessions'] ?? []) as $sessionForTotal) {

                                        try {

                                            if (
                                                isset($sessionForTotal['duration_seconds']) &&
                                                is_numeric($sessionForTotal['duration_seconds'])
                                            ) {
                                                $dailyTotalSeconds += (int) $sessionForTotal['duration_seconds'];
                                                continue;
                                            }

                                            $sessionDate = $sessionForTotal['date'] ?? $day['date'] ?? null;
                                            $loginTime = $sessionForTotal['login_at'] ?? null;
                                            $logoutTime = $sessionForTotal['logout_at'] ?? null;

                                            if (
                                                empty($sessionDate) ||
                                                empty($loginTime) ||
                                                empty($logoutTime)
                                            ) {
                                                continue;
                                            }

                                            if (
                                                in_array(
                                                    strtolower(trim($logoutTime)),
                                                    ['online', 'currently online', 'n/a', '-'],
                                                    true
                                                )
                                            ) {
                                                continue;
                                            }

                                            $loginAt = \Carbon\Carbon::parse(
                                                $sessionDate . ' ' . $loginTime
                                            );

                                            $logoutAt = \Carbon\Carbon::parse(
                                                $sessionDate . ' ' . $logoutTime
                                            );

                                            // Handle overnight sessions
                                            if ($logoutAt->lessThan($loginAt)) {
                                                $logoutAt->addDay();
                                            }

                                            $dailyTotalSeconds += $loginAt->diffInSeconds($logoutAt);

                                        } catch (\Throwable $e) {
                                            continue;
                                        }
                                    }

                                    $hours = floor($dailyTotalSeconds / 3600);
                                    $minutes = floor(($dailyTotalSeconds % 3600) / 60);
                                    $seconds = $dailyTotalSeconds % 60;

                                    $calculatedDailyTotal = sprintf(
                                        '%02d:%02d:%02d',
                                        $hours,
                                        $minutes,
                                        $seconds
                                    );
                                @endphp

                                {{-- Day header --}}
                                <tr class="al-day-row">

                                    <td colspan="2" class="al-day-header">
                                        {{ $day['date'] }}

                                        <span class="al-day-count">
                                            {{ $day['session_count'] }}
                                            {{ Str::plural('session', $day['session_count']) }}
                                        </span>
                                    </td>

                                    <td colspan="2" class="al-day-total">
                                        Daily total:
                                        <strong>{{ $calculatedDailyTotal }}</strong>
                                    </td>

                                    <td></td>

                                </tr>

                                {{-- Individual sessions --}}
                                @foreach ($day['sessions'] as $session)

                                    <tr class="al-session-row">

                                        <td>
                                            {{ $session['date'] ?? $day['date'] }}
                                        </td>

                                        <td></td>

                                        <td>
                                            {{ $session['login_at'] }}
                                        </td>

                                        <td>
                                            {{ $session['logout_at'] }}
                                        </td>

                                        <td>

                                            <span class="al-badge al-badge--{{ $session['is_current'] ? 'online' : 'offline' }}">
                                                {{ $session['status'] }}
                                            </span>

                                        </td>

                                    </tr>

                                @endforeach

                            @endforeach

                        </tbody>

                    </table>

                </div>

            </div>

        @else

            <div class="rounded-xl border border-dashed border-gray-200 bg-gray-50 p-10 text-center text-gray-500">
                No online/offline activity found yet.
            </div>

        @endif

    </div>
</div>

@push('scripts')

    <script src="{{ asset('profile/js/profile-online-sync.js') }}?v={{ filemtime(public_path('profile/js/profile-online-sync.js')) }}"></script>

    <script>
        (function () {

            const currentProfileId = @json($profile?->id);

            const rangeInputs = document.querySelectorAll('.al-range-input');

            const customRange = document.querySelector('.al-custom-range');

            const toggleCustomRange = function () {

                const selectedRange =
                    document.querySelector('.al-range-input:checked')?.value;

                if (!customRange) {
                    return;
                }

                customRange.classList.toggle(
                    'hidden',
                    selectedRange !== 'custom'
                );
            };

            rangeInputs.forEach(function (input) {
                input.addEventListener('change', toggleCustomRange);
            });

            toggleCustomRange();

            if (!currentProfileId || !window.profileOnlineSync?.subscribe) {
                return;
            }

            window.profileOnlineSync.subscribe(function (payload) {

                if (Number(payload?.profileId) !== Number(currentProfileId)) {
                    return;
                }

                window.location.reload();

            });

        })();
    </script>

@endpush

<style>

    .al-table th {
        background: #f9fafb;
        padding: 10px 14px;
        text-align: left;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #6b7280;
        border-bottom: 1px solid #e5e7eb;
        white-space: nowrap;
        position: sticky;
        top: 0;
        z-index: 1;
    }

    .al-table td {
        padding: 9px 14px;
        font-size: 13px;
        color: #374151;
        border-bottom: 1px solid #f3f4f6;
    }

    .al-day-row td {
        background: #eef2ff;
        border-top: 2px solid #c7d2fe;
        border-bottom: 1px solid #c7d2fe;
    }

    .al-day-header {
        font-weight: 700;
        font-size: 13px;
        color: #3730a3;
    }

    .al-day-count {
        margin-left: 8px;
        font-size: 11px;
        font-weight: 400;
        color: #6366f1;
    }

    .al-day-total {
        font-size: 12px;
        color: #4b5563;
    }

    .al-session-row td {
        padding-left: 28px;
        background: #fff;
    }

    .al-session-row:hover td {
        background: #fafafa;
    }

    .al-badge {
        display: inline-block;
        padding: 2px 10px;
        border-radius: 9999px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .al-badge--online {
        background: #dcfce7;
        color: #15803d;
    }

    .al-badge--offline {
        background: #f3f4f6;
        color: #6b7280;
    }

    .al-table-wrapper {
        max-height: min(70vh, calc(100vh - 260px));
    }

    .al-range-option {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: 1px solid #e5e7eb;
        border-radius: 9999px;
        padding: 10px 14px;
        font-size: 13px;
        font-weight: 600;
        color: #4b5563;
        background: #fff;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .al-range-option.is-active {
        border-color: #e04ecb;
        background: #fdf2f8;
        color: #be185d;
    }

    .al-range-input {
        accent-color: #e04ecb;
    }

    .al-table-scroll {
        max-height: inherit;
        overflow-y: auto;
        overflow-x: auto;
        overscroll-behavior: contain;
        scroll-behavior: smooth;
    }

</style>

@endsection
