<div class="pa-modal-content">
    <p class="pa-description">
        Login activity summary for {{ $provider->name ?? 'this provider' }} ({{ $provider->user?->email ?? 'N/A' }}).
    </p>

    <div class="pa-summary-grid">
        <section class="pa-summary-card">
            <span class="pa-summary-label">Total Logins</span>
            <span class="pa-summary-value">{{ number_format((int) ($activity['total_logins'] ?? 0)) }}</span>
        </section>
        <section class="pa-summary-card">
            <span class="pa-summary-label">Total Time Online</span>
            <span class="pa-summary-value">{{ $activity['total_online_duration'] ?? '00h 00m' }}</span>
        </section>
        <section class="pa-summary-card">
            <span class="pa-summary-label">Current Session</span>
            <span class="pa-summary-value">{{ $activity['current_session_duration'] ?? '00h 00m' }}</span>
        </section>
    </div>

    @if (! empty($activity['history']))
        @php
            $chartHistory = array_reverse($activity['history']);
            $chartLabels  = array_column($chartHistory, 'date');
            $chartLogins  = array_column($chartHistory, 'login_count');
            $chartMinutes = array_map(
                fn ($row) => round(($row['time_spent_seconds'] ?? 0) / 60, 1),
                $chartHistory
            );
            $chartId = 'pa-chart-' . $provider->id;
        @endphp

        <div class="pa-chart-wrapper">
            <canvas id="{{ $chartId }}" class="pa-chart-canvas"></canvas>
        </div>

        <div class="pa-table-wrapper">
            <table class="pa-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Login Count</th>
                        <th>First Login</th>
                        <th>Last Login</th>
                        <th>Time Spent Online</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($activity['history'] as $row)
                        <tr>
                            <td>{{ $row['date'] ?? '-' }}</td>
                            <td>{{ number_format((int) ($row['login_count'] ?? 0)) }}</td>
                            <td>{{ $row['first_login'] ?? '-' }}</td>
                            <td>{{ $row['last_login'] ?? '-' }}</td>
                            <td>{{ $row['time_spent'] ?? '00h 00m' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <script>
        (function () {
            var chartId   = {{ Js::from($chartId) }};
            var labels    = {{ Js::from($chartLabels) }};
            var logins    = {{ Js::from($chartLogins) }};
            var minutes   = {{ Js::from($chartMinutes) }};

            function buildChart() {
                var canvas = document.getElementById(chartId);
                if (! canvas) { return; }
                if (canvas._paChart) { canvas._paChart.destroy(); }

                var isDark = document.documentElement.classList.contains('dark');
                var gridColor  = isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.07)';
                var labelColor = isDark ? '#9ca3af' : '#6b7280';

                canvas._paChart = new Chart(canvas, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                type: 'bar',
                                label: 'Login Count',
                                data: logins,
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
                                data: minutes,
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
                                        return ' Logins: ' + ctx.parsed.y;
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
                                title: { display: true, text: 'Login Count', color: 'rgba(99,102,241,1)', font: { size: 11 } },
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
            }

            function loadChartJs(callback) {
                if (window.Chart) { callback(); return; }
                var script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js';
                script.onload = callback;
                document.head.appendChild(script);
            }

            loadChartJs(buildChart);
        })();
        </script>
    @else
        <div class="pa-empty">
            No login activity found for this provider yet.
        </div>
    @endif
</div>

<style>
    .pa-modal-content {
        max-height: calc(100vh - 180px);
        overflow: auto;
        display: flex;
        flex-direction: column;
        gap: 12px;
        padding: 4px;
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

    .pa-chart-wrapper {
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        background: #fff;
        padding: 12px 14px 8px;
    }

    .pa-chart-canvas {
        max-height: 260px;
        width: 100% !important;
    }

    .pa-table-wrapper {
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        overflow: auto;
        background: #fff;
    }

    .pa-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 620px;
    }

    .pa-table th,
    .pa-table td {
        padding: 10px 12px;
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
    }

    .pa-empty {
        border: 1px dashed #d1d5db;
        border-radius: 10px;
        padding: 12px;
        color: #6b7280;
        background: #f9fafb;
        font-size: 14px;
    }

    .dark .pa-description {
        color: #d1d5db;
    }

    .dark .pa-summary-card {
        border-color: #374151;
        background: #111827;
    }

    .dark .pa-summary-label {
        color: #9ca3af;
    }

    .dark .pa-summary-value {
        color: #f9fafb;
    }

    .dark .pa-chart-wrapper {
        border-color: #374151;
        background: #111827;
    }

    .dark .pa-table-wrapper {
        border-color: #374151;
        background: #111827;
    }

    .dark .pa-table th {
        background: #1f2937;
        color: #9ca3af;
        border-bottom-color: #374151;
    }

    .dark .pa-table td {
        color: #d1d5db;
        border-bottom-color: #1f2937;
    }

    .dark .pa-empty {
        border-color: #4b5563;
        background: #1f2937;
        color: #d1d5db;
    }
</style>
