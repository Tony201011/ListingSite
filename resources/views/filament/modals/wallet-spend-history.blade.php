<div class="wallet-modal-content">
    <p class="wallet-description">
        Latest wallet credit activity for this user account.
    </p>

    <div class="wallet-summary-grid">
        <section class="wallet-summary-card wallet-summary-card--total">
            <span class="wallet-summary-label">Total Balance</span>
            <span class="wallet-summary-value">{{ number_format((int) ($summary['total_balance'] ?? 0)) }}</span>
        </section>
        <section class="wallet-summary-card wallet-summary-card--used">
            <span class="wallet-summary-label">Used Balance</span>
            <span class="wallet-summary-value">{{ number_format((int) ($summary['used_balance'] ?? 0)) }}</span>
        </section>
        <section class="wallet-summary-card wallet-summary-card--remaining">
            <span class="wallet-summary-label">Remaining Balance</span>
            <span class="wallet-summary-value">{{ number_format((int) ($summary['remaining_balance'] ?? 0)) }}</span>
        </section>
    </div>

    @if (filled($history))
        <div class="wallet-table-section">
            <div class="wallet-table-wrapper">
                <div class="wallet-table-scroll">
                    <table class="wallet-table">
                        <thead>
                            <tr>
                                <th class="wallet-col-spent">Spent At</th>
                                <th class="wallet-col-credits">Credits</th>
                                <th class="wallet-col-details">Details</th>
                                <th class="wallet-col-type">Type</th>
                                <th class="wallet-col-reference">Reference</th>
                                <th class="wallet-col-view">View</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($history as $row)
                                @php
                                    $creditsUsed = (int) ($row['credits_used'] ?? 0);
                                    $isNegative = $creditsUsed < 0;
                                    $isPositive = $creditsUsed > 0;
                                    $creditPrefix = $isPositive ? '+' : '';
                                @endphp
                                <tr>
                                    <td class="wallet-cell-spent">
                                        {{
                                            $row['spent_at'] instanceof \Carbon\CarbonInterface
                                                ? $row['spent_at']->format('d M Y, h:i A')
                                                : ($row['spent_at'] ?: '-')
                                        }}
                                    </td>
                                    <td @class([
                                        'wallet-table-credit',
                                        'wallet-table-credit--negative' => $isNegative,
                                        'wallet-table-credit--positive' => $isPositive,
                                    ])>
                                        {{ $creditPrefix }}{{ number_format($creditsUsed) }}
                                    </td>
                                    <td>{{ $row['description'] ?: '-' }}</td>
                                    <td>{{ $row['type'] ?: '-' }}</td>
                                    <td>{{ $row['reference'] ?: '-' }}</td>
                                    <td class="wallet-cell-view">
                                        @if (filled($row['details_url']))
                                            <a href="{{ $row['details_url'] }}" target="_blank" rel="noopener noreferrer" class="wallet-view-link">
                                                View
                                            </a>
                                        @else
                                            <span class="wallet-view-placeholder">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="wallet-empty">
            No wallet spend history found.
        </div>
    @endif
</div>

<style>
    .wallet-modal-content {
        --wallet-modal-offset: 210px;
        --wallet-scrollbar-thumb: #9ca3af;
        --wallet-scrollbar-track: #e5e7eb;
        max-height: min(780px, calc(100vh - var(--wallet-modal-offset)));
        overflow: hidden;
        padding: 4px 8px 10px;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .wallet-description {
        margin: 0;
        font-size: 14px;
        line-height: 1.5;
        color: #4b5563;
    }

    .wallet-summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 12px;
        margin-bottom: 4px;
    }

    .wallet-summary-card {
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        padding: 12px 16px;
        display: flex;
        flex-direction: column;
        gap: 4px;
        background: #fff;
    }

    .wallet-summary-card--total {
        border-color: #bfdbfe;
        background: #eff6ff;
    }

    .wallet-summary-card--used {
        border-color: #fecaca;
        background: #fef2f2;
    }

    .wallet-summary-card--remaining {
        border-color: #bbf7d0;
        background: #f0fdf4;
    }

    .wallet-summary-label {
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #6b7280;
    }

    .wallet-summary-value {
        font-size: 20px;
        font-weight: 700;
        color: #111827;
    }

    .wallet-table-section {
        min-height: 0;
        flex: 1 1 auto;
    }

    .wallet-table-wrapper {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
        background: #fff;
        height: 100%;
    }

    .wallet-table-scroll {
        max-height: min(460px, calc(100vh - 420px));
        overflow-y: auto;
        overflow-x: hidden;
        scrollbar-width: thin;
        scrollbar-color: var(--wallet-scrollbar-thumb) var(--wallet-scrollbar-track);
    }

    .wallet-table-scroll::-webkit-scrollbar {
        width: 10px;
    }

    .wallet-table-scroll::-webkit-scrollbar-track {
        background: var(--wallet-scrollbar-track);
        border-radius: 9999px;
    }

    .wallet-table-scroll::-webkit-scrollbar-thumb {
        background: var(--wallet-scrollbar-thumb);
        border-radius: 9999px;
        border: 2px solid var(--wallet-scrollbar-track);
    }

    .wallet-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .wallet-table th,
    .wallet-table td {
        padding: 12px;
        border-bottom: 1px solid #f3f4f6;
        font-size: 13px;
        text-align: left;
        vertical-align: top;
        color: #374151;
        line-height: 1.45;
        white-space: normal;
        overflow-wrap: anywhere;
        word-break: break-word;
    }

    .wallet-table th {
        background: #f9fafb;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: #6b7280;
        position: sticky;
        top: 0;
        z-index: 1;
    }

    .wallet-table tbody tr {
        transition: background-color 0.15s ease;
    }

    .wallet-table tbody tr:hover {
        background: #f8fafc;
    }

    .wallet-table-credit {
        font-weight: 700;
        text-align: right;
    }

    .wallet-table-credit--negative {
        color: #b91c1c !important;
    }

    .wallet-table-credit--positive {
        color: #15803d !important;
    }

    .wallet-cell-spent {
        white-space: nowrap !important;
    }

    .wallet-cell-view {
        text-align: center !important;
        vertical-align: middle !important;
        white-space: nowrap !important;
    }

    .wallet-col-spent {
        width: 19%;
    }

    .wallet-col-credits {
        width: 12%;
        text-align: right;
    }

    .wallet-col-details {
        width: 28%;
    }

    .wallet-col-type {
        width: 14%;
    }

    .wallet-col-reference {
        width: 17%;
    }

    .wallet-col-view {
        width: 10%;
        text-align: center;
    }

    .wallet-view-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 9999px;
        border: 1px solid #c7d2fe;
        background: #eef2ff;
        color: #3730a3;
        padding: 4px 12px;
        min-height: 30px;
        min-width: 62px;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        white-space: nowrap;
    }

    .wallet-view-placeholder {
        display: inline-block;
        min-width: 62px;
        text-align: center;
    }

    .wallet-empty {
        padding: 14px 16px;
        border: 1px dashed #d1d5db;
        border-radius: 12px;
        background: #f9fafb;
        font-size: 14px;
        color: #6b7280;
    }

    .dark .wallet-description {
        color: #d1d5db;
    }

    .dark .wallet-summary-card {
        border-color: #374151;
        background: #111827;
    }

    .dark .wallet-summary-card--total {
        border-color: #1d4ed8;
        background: rgba(30, 58, 138, 0.35);
    }

    .dark .wallet-summary-card--used {
        border-color: #991b1b;
        background: rgba(127, 29, 29, 0.35);
    }

    .dark .wallet-summary-card--remaining {
        border-color: #166534;
        background: rgba(20, 83, 45, 0.35);
    }

    .dark .wallet-summary-label {
        color: #9ca3af;
    }

    .dark .wallet-summary-value {
        color: #f9fafb;
    }

    .dark .wallet-table-wrapper {
        border-color: #374151;
        background: #111827;
    }

    .dark .wallet-table-scroll {
        --wallet-scrollbar-thumb: #6b7280;
        --wallet-scrollbar-track: #1f2937;
    }

    .dark .wallet-table th {
        background: #1f2937;
        color: #9ca3af;
        border-bottom-color: #374151;
    }

    .dark .wallet-table td {
        color: #d1d5db;
        border-bottom-color: #1f2937;
    }

    .dark .wallet-table tbody tr:hover {
        background: rgba(55, 65, 81, 0.55);
    }

    .dark .wallet-table-credit--negative {
        color: #fca5a5 !important;
    }

    .dark .wallet-table-credit--positive {
        color: #86efac !important;
    }

    .dark .wallet-view-link {
        border-color: #4338ca;
        background: rgba(67, 56, 202, 0.25);
        color: #c7d2fe;
    }

    .dark .wallet-empty {
        border-color: #4b5563;
        background: #1f2937;
        color: #d1d5db;
    }

    @media (max-width: 768px) {
        .wallet-modal-content {
            --wallet-modal-offset: 140px;
            padding: 0 0 8px;
            gap: 10px;
        }

        .wallet-summary-grid {
            grid-template-columns: 1fr;
        }

        .wallet-summary-card {
            padding: 10px 12px;
        }

        .wallet-table-scroll {
            max-height: min(56vh, calc(100vh - 360px));
            overflow-x: auto;
        }

        .wallet-table {
            min-width: 680px;
        }

        .wallet-table th,
        .wallet-table td {
            font-size: 12px;
            padding: 10px;
        }

        .wallet-view-link {
            min-width: 56px;
            padding: 3px 10px;
        }
    }

    @media (max-width: 480px) {
        .wallet-modal-content {
            --wallet-modal-offset: 110px;
        }

        .wallet-description {
            font-size: 13px;
        }

        .wallet-summary-value {
            font-size: 18px;
        }
    }
</style>
