<div class="pa-modal-content">
    <p class="pa-description">
        Online and offline session activity for {{ $provider->name ?? 'this provider' }} ({{ $provider->user?->email ?? 'N/A' }}).
    </p>

    <div class="pa-summary-grid">
        <section class="pa-summary-card">
            <span class="pa-summary-label">Profile Name</span>
            <span class="pa-summary-meta">{{ $provider->name ?? 'N/A' }}</span>
        </section>
        <section class="pa-summary-card">
            <span class="pa-summary-label">Provider Email</span>
            <span class="pa-summary-meta">{{ $provider->user?->email ?? 'N/A' }}</span>
        </section>
        <section class="pa-summary-card">
            <span class="pa-summary-label">Total Sessions</span>
            <span class="pa-summary-value">{{ number_format((int) ($activity['total_sessions'] ?? $activity['total_logins'] ?? 0)) }}</span>
        </section>
        <section class="pa-summary-card">
            <span class="pa-summary-label">Total Time Online</span>
            <span class="pa-summary-value">{{ $activity['total_online_duration'] ?? '—' }}</span>
        </section>
        <section class="pa-summary-card">
            <span class="pa-summary-label">Current Session</span>
            <span class="pa-summary-value">{{ $activity['current_session_duration'] ?? '—' }}</span>
        </section>
    </div>

    @if (! empty($activity['days']))
        @php
            $chartId      = 'pa-chart-' . $provider->id;
            $chartLabels  = $activity['chart_labels']  ?? [];
            $chartLogins  = $activity['chart_logins']  ?? [];
            $chartMinutes = $activity['chart_minutes'] ?? [];
        @endphp

        <div
            class="pa-chart-wrapper"
            x-data="{
                labels: {{ \Illuminate\Support\Js::from($chartLabels) }},
                logins: {{ \Illuminate\Support\Js::from($chartLogins) }},
                minutes: {{ \Illuminate\Support\Js::from($chartMinutes) }},
                chart: null,
                init() {
                    this.loadChartJs().then(() => this.renderChart()).catch(() => {});
                },
                renderChart() {
                    if (! window.Chart || ! this.$refs.canvas) {
                        return;
                    }

                    if (this.chart) {
                        this.chart.destroy();
                    }

                    const isDark = document.documentElement.classList.contains('dark');
                    const gridColor  = isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.07)';
                    const labelColor = isDark ? '#9ca3af' : '#6b7280';

                    this.chart = new window.Chart(this.$refs.canvas, {
                        type: 'bar',
                        data: {
                            labels: this.labels,
                            datasets: [
                                {
                                    type: 'bar',
                                    label: 'Session Count',
                                    data: this.logins,
                                    backgroundColor: 'rgba(99,102,241,0.75)',
                                    borderColor: 'rgba(99,102,241,1)',
                                    borderWidth: 1,
                                    borderRadius: 4,
                                    yAxisID: 'yLogins',
                                    order: 2,
                                },
                                {
                                    type: 'line',
                                    label: 'Time Online (min)',
                                    data: this.minutes,
                                    borderColor: 'rgba(245,158,11,1)',
                                    backgroundColor: 'rgba(245,158,11,0.15)',
                                    borderWidth: 2,
                                    pointRadius: 4,
                                    pointBackgroundColor: 'rgba(245,158,11,1)',
                                    tension: 0.35,
                                    fill: true,
                                    yAxisID: 'yMinutes',
                                    order: 1,
                                },
                            ],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            interaction: { mode: 'index', intersect: false },
                            plugins: {
                                legend: {
                                    position: 'top',
                                    labels: { color: labelColor, boxWidth: 12, padding: 14, font: { size: 12 } },
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function (ctx) {
                                            if (ctx.datasetIndex === 1) {
                                                return ' Time Online: ' + ctx.parsed.y + ' min';
                                            }

                                            return ' Sessions: ' + ctx.parsed.y;
                                        },
                                    },
                                },
                            },
                            scales: {
                                x: {
                                    ticks: { color: labelColor, maxRotation: 45, font: { size: 11 } },
                                    grid:  { color: gridColor },
                                },
                                yLogins: {
                                    type: 'linear',
                                    position: 'left',
                                    beginAtZero: true,
                                    ticks: { color: 'rgba(99,102,241,1)', stepSize: 1, font: { size: 11 } },
                                    grid: { color: gridColor },
                                    title: { display: true, text: 'Session Count', color: 'rgba(99,102,241,1)', font: { size: 11 } },
                                },
                                yMinutes: {
                                    type: 'linear',
                                    position: 'right',
                                    beginAtZero: true,
                                    ticks: { color: 'rgba(245,158,11,1)', font: { size: 11 } },
                                    grid: { drawOnChartArea: false },
                                    title: { display: true, text: 'Time Online (min)', color: 'rgba(245,158,11,1)', font: { size: 11 } },
                                },
                            },
                        },
                    });
                },
                loadChartJs() {
                    if (window.Chart) {
                        return Promise.resolve();
                    }

                    if (! window.__providerActivityChartLoader) {
                        window.__providerActivityChartLoader = new Promise((resolve, reject) => {
                            const script = document.createElement('script');
                            script.src = 'https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js';
                            script.onload = resolve;
                            script.onerror = reject;
                            document.head.appendChild(script);
                        });
                    }

                    return window.__providerActivityChartLoader;
                },
            }"
            x-init="init()"
        >
            <canvas x-ref="canvas" id="{{ $chartId }}" class="pa-chart-canvas"></canvas>
        </div>

        <div class="pa-table-wrapper">
            <div class="pa-table-scroll">
                <table class="pa-table">
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
                    @foreach ($activity['days'] as $day)
                        <tbody x-data="{ open: true }">
                            {{-- Day header row --}}
                            <tr
                                class="pa-day-row"
                                @click="open = !open"
                            >
                                <td colspan="2" class="pa-day-header">
                                    <span class="pa-chevron" :class="{ 'pa-chevron--collapsed': !open }">&#9660;</span>
                                    {{ $day['date'] }}
                                    <span class="pa-day-sessions">{{ $day['session_count'] }} {{ Str::plural('session', $day['session_count']) }}</span>
                                </td>
                                <td colspan="3" class="pa-day-total">
                                    Daily total: {{ $day['total_duration'] }}
                                </td>
                                <td></td>
                            </tr>
                            {{-- Individual session rows --}}
                            @foreach ($day['sessions'] as $session)
                                <tr class="pa-session-row" x-show="open">
                                    <td>{{ $session['date'] ?? $day['date'] }}</td>
                                    <td></td>
                                    <td>{{ $session['login_at'] }}</td>
                                    <td>{{ $session['logout_at'] }}</td>
                                    <td>{{ $session['duration'] }}</td>
                                    <td>
                                        <span class="pa-badge pa-badge--{{ $session['is_current'] ? 'online' : 'offline' }}">
                                            {{ $session['status'] }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    @endforeach
                </table>
            </div>
        </div>

    @else
        <div class="pa-empty">
            No online/offline activity found for this profile yet.
        </div>
    @endif
</div>

<style>
    .pa-modal-content {
        max-height: min(82vh, calc(100vh - 150px));
        overflow: hidden;
        display: flex;
        flex-direction: column;
        gap: 12px;
        padding: 4px;
        min-height: 0;
    }

    .pa-description {
        margin: 0;
        color: #4b5563;
        font-size: 14px;
    }

    .pa-summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 10px;
    }

    .pa-summary-card {
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        background: #fff;
        padding: 10px 12px;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .pa-summary-label {
        text-transform: uppercase;
        font-size: 11px;
        font-weight: 700;
        color: #6b7280;
    }

    .pa-summary-value {
        font-size: 18px;
        font-weight: 700;
        color: #111827;
    }

    .pa-summary-meta {
        font-size: 14px;
        font-weight: 600;
        color: #111827;
        line-height: 1.35;
    }

    .pa-chart-wrapper {
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        background: #fff;
        padding: 12px 14px 8px;
        flex: 0 0 auto;
    }

    .pa-chart-canvas {
        max-height: 260px;
        width: 100% !important;
    }

    .pa-table-wrapper {
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        background: #fff;
        flex: 1 1 auto;
        min-height: 0;
        overflow: hidden;
    }

    .pa-table-scroll {
        height: 100%;
        max-height: 100%;
        overflow-x: auto;
        overflow-y: auto;
        min-height: 0;
        overscroll-behavior: contain;
        scroll-behavior: smooth;
    }

    .pa-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 720px;
    }

    .pa-table th,
    .pa-table td {
        padding: 8px 12px;
        border-bottom: 1px solid #f3f4f6;
        text-align: left;
        font-size: 13px;
        color: #374151;
    }

    .pa-table th {
        background: #f9fafb;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.03em;
        color: #6b7280;
        position: sticky;
        top: 0;
        z-index: 1;
    }

    .pa-day-row td {
        background: #f0f4ff;
        border-top: 2px solid #c7d2fe;
        border-bottom: 1px solid #c7d2fe;
    }

    .pa-day-row {
        cursor: pointer;
        user-select: none;
    }

    .pa-day-row:hover td {
        filter: brightness(0.97);
    }

    .pa-chevron {
        display: inline-block;
        font-size: 10px;
        margin-right: 6px;
        transition: transform 0.2s ease;
        color: #6366f1;
    }

    .pa-chevron--collapsed {
        transform: rotate(-90deg);
    }

    .pa-day-header {
        font-weight: 700;
        font-size: 13px !important;
        color: #3730a3 !important;
    }

    .pa-day-sessions {
        margin-left: 8px;
        font-weight: 400;
        font-size: 11px;
        color: #6366f1;
    }

    .pa-day-total {
        font-size: 12px !important;
        color: #4b5563 !important;
        font-weight: 600;
    }

    .pa-session-row td {
        padding-left: 24px;
        background: #fff;
    }

    .pa-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 9999px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .pa-badge--online {
        background: #dcfce7;
        color: #15803d;
    }

    .pa-badge--offline {
        background: #f3f4f6;
        color: #6b7280;
    }

    .pa-empty {
        border: 1px dashed #d1d5db;
        border-radius: 10px;
        padding: 12px;
        color: #6b7280;
        background: #f9fafb;
        font-size: 14px;
    }

    /* Dark mode */
    .dark .pa-description { color: #d1d5db; }
    .dark .pa-summary-card { border-color: #374151; background: #111827; }
    .dark .pa-summary-label { color: #9ca3af; }
    .dark .pa-summary-value { color: #f9fafb; }
    .dark .pa-summary-meta { color: #f9fafb; }
    .dark .pa-chart-wrapper { border-color: #374151; background: #111827; }
    .dark .pa-table-wrapper { border-color: #374151; background: #111827; }
    .dark .pa-table th { background: #1f2937; color: #9ca3af; border-bottom-color: #374151; }
    .dark .pa-table td { color: #d1d5db; border-bottom-color: #1f2937; }
    .dark .pa-day-row td { background: #1e2a4a; border-top-color: #3730a3; border-bottom-color: #3730a3; }
    .dark .pa-day-header { color: #a5b4fc !important; }
    .dark .pa-day-sessions { color: #818cf8; }
    .dark .pa-day-total { color: #9ca3af !important; }
    .dark .pa-session-row td { background: #111827; }
    .dark .pa-badge--online { background: #14532d; color: #86efac; }
    .dark .pa-badge--offline { background: #374151; color: #9ca3af; }
    .dark .pa-empty { border-color: #4b5563; background: #1f2937; color: #d1d5db; }
</style>
