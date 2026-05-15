<div class="wallet-modal-content">
    <p class="wallet-description">
        Latest wallet deductions for this user account.
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
        <div class="wallet-table-wrapper">
            <table class="wallet-table">
                <thead>
                    <tr>
                        <th>Spent At</th>
                        <th>Credits Used</th>
                        <th>Details</th>
                        <th>Type</th>
                        <th>Reference</th>
                        <th>View</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($history as $row)
                        <tr>
                            <td>
                                {{
                                    $row['spent_at'] instanceof \Carbon\CarbonInterface
                                        ? $row['spent_at']->format('d M Y, h:i A')
                                        : ($row['spent_at'] ?: '-')
                                }}
                            </td>
                            <td class="wallet-table-credit">-{{ number_format((int) ($row['credits_used'] ?? 0)) }}</td>
                            <td>{{ $row['description'] ?: '-' }}</td>
                            <td>{{ $row['type'] ?: '-' }}</td>
                            <td>{{ $row['reference'] ?: '-' }}</td>
                            <td>
                                @if (filled($row['details_url']))
                                    <a href="{{ $row['details_url'] }}" target="_blank" rel="noopener noreferrer" class="wallet-view-link">
                                        View
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="wallet-empty">
            No wallet spend history found.
        </div>
    @endif
</div>

<style>
    .wallet-modal-content {
        --wallet-modal-offset: 190px;
        max-height: calc(100vh - var(--wallet-modal-offset));
        overflow-y: auto;
        padding: 4px 8px 12px;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .wallet-description {
        margin: 0;
        font-size: 14px;
        line-height: 1.6;
        color: #4b5563;
    }

    .wallet-summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 12px;
    }

    .wallet-summary-card {
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        padding: 12px 14px;
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

    .wallet-table-wrapper {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
        background: #fff;
    }

    .wallet-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 840px;
    }

    .wallet-table th,
    .wallet-table td {
        padding: 10px 12px;
        border-bottom: 1px solid #f3f4f6;
        font-size: 13px;
        text-align: left;
        vertical-align: top;
        color: #374151;
    }

    .wallet-table th {
        background: #f9fafb;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: #6b7280;
    }

    .wallet-table-credit {
        color: #b91c1c !important;
        font-weight: 700;
    }

    .wallet-view-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 9999px;
        border: 1px solid #c7d2fe;
        background: #eef2ff;
        color: #3730a3;
        padding: 2px 10px;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
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

    .dark .wallet-table th {
        background: #1f2937;
        color: #9ca3af;
        border-bottom-color: #374151;
    }

    .dark .wallet-table td {
        color: #d1d5db;
        border-bottom-color: #1f2937;
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
        }
    }
</style>
