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

        <h1 class="mb-2 text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">Activity Logs</h1>
        <p class="mb-8 text-sm text-gray-500">Your login session history for the last 90 days.</p>

        {{-- Summary cards --}}
        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wide text-gray-500">Total Logins</p>
                <p class="mt-1 text-3xl font-bold text-gray-900">{{ number_format($activity['total_logins']) }}</p>
            </div>
            <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wide text-gray-500">Total Time Online</p>
                <p class="mt-1 text-3xl font-bold text-gray-900">{{ $activity['total_online_duration'] }}</p>
            </div>
            <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wide text-gray-500">Current Session</p>
                <p class="mt-1 text-3xl font-bold text-gray-900">{{ $activity['current_session_duration'] }}</p>
            </div>
        </div>

        {{-- Day-wise session table --}}
        @if (! empty($activity['days']))
            <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="al-table w-full border-collapse">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Sessions</th>
                                <th>Login Time</th>
                                <th>Logout Time</th>
                                <th>Duration</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($activity['days'] as $day)
                                {{-- Day header --}}
                                <tr class="al-day-row">
                                    <td colspan="2" class="al-day-header">
                                        {{ $day['date'] }}
                                        <span class="al-day-count">
                                            {{ $day['session_count'] }} {{ Str::plural('session', $day['session_count']) }}
                                        </span>
                                    </td>
                                    <td colspan="3" class="al-day-total">
                                        Daily total: <strong>{{ $day['total_duration'] }}</strong>
                                    </td>
                                    <td></td>
                                </tr>
                                {{-- Individual sessions --}}
                                @foreach ($day['sessions'] as $session)
                                    <tr class="al-session-row">
                                        <td></td>
                                        <td></td>
                                        <td>{{ $session['login_at'] }}</td>
                                        <td>{{ $session['logout_at'] }}</td>
                                        <td>{{ $session['duration'] }}</td>
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
                No login activity found yet.
            </div>
        @endif

    </div>
</div>

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
</style>
@endsection
