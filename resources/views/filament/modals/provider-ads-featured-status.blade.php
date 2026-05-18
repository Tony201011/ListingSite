<div class="af-modal-content">
    <p class="af-description">
        Current ad &amp; featured visibility status for this provider profile.
    </p>

    @if (filled($rows))
        <div class="af-grid">
            @foreach ($rows as $row)
                @php
                    $hasTxns = ! empty($row['transactions']);
                    $isFree  = ($row['tier'] === 'Free Listing');
                @endphp

                <section
                    class="af-card"
                    x-data="{ open: false }"
                >
                    {{-- Card header --}}
                    <div class="af-card-header">
                        <div class="af-header-row">
                            <h3 class="af-tier">{{ $row['tier'] }}</h3>

                            {{-- Eye / Details button --}}
                            <button
                                type="button"
                                class="af-eye-btn"
                                title="View transaction details"
                                @click="open = !open"
                                :aria-expanded="open"
                                aria-label="Toggle transaction details for {{ $row['tier'] }}"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    aria-hidden="true"
                                    class="af-eye-icon"
                                >
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>
                                <span x-text="open ? 'Hide' : 'Details'" class="af-eye-label"></span>
                            </button>
                        </div>
                    </div>

                    {{-- Status / expiry rows --}}
                    <div class="af-card-body">
                        <div class="af-row">
                            <span class="af-label">Status</span>
                            <span
                                class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $row['status_class'] ?? 'bg-gray-100 text-gray-700 ring-gray-200' }}"
                            >
                                {{ $row['status'] }}
                            </span>
                        </div>

                        <div class="af-row">
                            <span class="af-label">Expiry</span>
                            <span class="af-value">{{ $row['expiry'] }}</span>
                        </div>
                    </div>

                    {{-- Expandable transaction details panel --}}
                    <div
                        x-show="open"
                        x-transition:enter="af-transition-enter"
                        x-transition:enter-start="af-transition-enter-start"
                        x-transition:enter-end="af-transition-enter-end"
                        x-transition:leave="af-transition-leave"
                        x-transition:leave-start="af-transition-leave-start"
                        x-transition:leave-end="af-transition-leave-end"
                        class="af-details-panel"
                        x-cloak
                    >
                        <div class="af-details-inner">
                            <p class="af-details-heading">Transaction Details</p>

                            {{-- Provider / User info --}}
                            <div class="af-detail-section">
                                <div class="af-detail-row">
                                    <span class="af-detail-label">Provider</span>
                                    <span class="af-detail-value">{{ $row['provider_name'] ?? '—' }}</span>
                                </div>
                                <div class="af-detail-row">
                                    <span class="af-detail-label">Email</span>
                                    <span class="af-detail-value">{{ $row['provider_email'] ?? '—' }}</span>
                                </div>
                                <div class="af-detail-row">
                                    <span class="af-detail-label">Listing Type</span>
                                    <span class="af-detail-value">{{ $row['tier'] }}</span>
                                </div>
                                <div class="af-detail-row">
                                    <span class="af-detail-label">Current Status</span>
                                    <span class="af-detail-value">{{ $row['status'] }}</span>
                                </div>
                                <div class="af-detail-row">
                                    <span class="af-detail-label">Expiry Date</span>
                                    <span class="af-detail-value">{{ $row['expiry'] }}</span>
                                </div>
                            </div>

                            {{-- Transaction history --}}
                            @if ($isFree)
                                <div class="af-txn-empty">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="af-txn-empty-icon" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd" />
                                    </svg>
                                    Free Listing is granted automatically — no credit transaction is recorded.
                                </div>
                            @elseif ($hasTxns)
                                <p class="af-txn-heading">Recent Purchases</p>
                                <div class="af-txn-list">
                                    @foreach ($row['transactions'] as $txn)
                                        <div class="af-txn-card">
                                            <div class="af-detail-row">
                                                <span class="af-detail-label">Transaction ID</span>
                                                <span class="af-detail-value af-mono">#{{ $txn['id'] }}</span>
                                            </div>
                                            <div class="af-detail-row">
                                                <span class="af-detail-label">Payment Amount</span>
                                                <span class="af-detail-value af-credit">-{{ number_format((int) $txn['credits_used']) }} credits</span>
                                            </div>
                                            <div class="af-detail-row">
                                                <span class="af-detail-label">Payment Status</span>
                                                <span class="af-badge-paid">{{ $txn['payment_status'] }}</span>
                                            </div>
                                            <div class="af-detail-row">
                                                <span class="af-detail-label">Purchase Date</span>
                                                <span class="af-detail-value">{{ $txn['purchased_at'] }}</span>
                                            </div>
                                            <div class="af-detail-row">
                                                <span class="af-detail-label">Description</span>
                                                <span class="af-detail-value af-wrap">{{ $txn['description'] }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="af-txn-empty">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="af-txn-empty-icon" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                                    </svg>
                                    No purchase transactions found for this listing type.
                                </div>
                            @endif
                        </div>
                    </div>
                </section>
            @endforeach
        </div>
    @else
        <div class="af-empty">
            No ad or featured records found.
        </div>
    @endif
</div>

<style>
    /* ── Layout ── */
    .af-modal-content {
        --af-modal-offset: 180px;
        max-height: calc(100vh - var(--af-modal-offset));
        overflow-y: auto;
        padding: 4px 8px 12px;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .af-description {
        margin: 0;
        font-size: 14px;
        line-height: 1.6;
        color: #4b5563;
    }

    .af-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 14px;
    }

    /* ── Card ── */
    .af-card {
        overflow: hidden;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        background: #ffffff;
    }

    .af-card-header {
        padding: 12px 14px;
        background: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
    }

    .af-header-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }

    .af-tier {
        margin: 0;
        font-size: 14px;
        font-weight: 700;
        color: #111827;
    }

    /* ── Eye button ── */
    .af-eye-btn {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px 4px 8px;
        border-radius: 9999px;
        border: 1px solid #c7d2fe;
        background: #eef2ff;
        color: #3730a3;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.15s, border-color 0.15s;
        white-space: nowrap;
    }

    .af-eye-btn:hover {
        background: #e0e7ff;
        border-color: #a5b4fc;
    }

    .af-eye-btn:focus-visible {
        outline: 2px solid #6366f1;
        outline-offset: 2px;
    }

    .af-eye-icon {
        width: 14px;
        height: 14px;
        flex-shrink: 0;
    }

    .af-eye-label {
        font-size: 12px;
    }

    /* ── Status / expiry ── */
    .af-card-body {
        display: flex;
        flex-direction: column;
        gap: 10px;
        padding: 14px;
    }

    .af-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .af-label {
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #6b7280;
        flex-shrink: 0;
    }

    .af-value {
        font-size: 14px;
        color: #374151;
        text-align: right;
        word-break: break-word;
    }

    /* ── Details panel ── */
    .af-details-panel {
        border-top: 1px solid #e5e7eb;
        background: #f9fafb;
    }

    .af-details-inner {
        padding: 14px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .af-details-heading {
        margin: 0;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: #6b7280;
    }

    /* ── Detail rows ── */
    .af-detail-section {
        display: flex;
        flex-direction: column;
        gap: 6px;
        padding: 10px 12px;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        background: #ffffff;
    }

    .af-detail-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
    }

    .af-detail-label {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #9ca3af;
        flex-shrink: 0;
    }

    .af-detail-value {
        font-size: 13px;
        color: #374151;
        text-align: right;
        word-break: break-word;
    }

    .af-detail-value.af-mono {
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        font-size: 12px;
    }

    .af-detail-value.af-wrap {
        text-align: right;
    }

    /* ── Transaction list ── */
    .af-txn-heading {
        margin: 0;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #6b7280;
    }

    .af-txn-list {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .af-txn-card {
        display: flex;
        flex-direction: column;
        gap: 5px;
        padding: 10px 12px;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        background: #ffffff;
    }

    .af-credit {
        color: #b91c1c;
        font-weight: 700;
    }

    .af-badge-paid {
        display: inline-flex;
        align-items: center;
        border-radius: 9999px;
        padding: 1px 8px;
        font-size: 11px;
        font-weight: 600;
        background: #dcfce7;
        color: #166534;
        ring: 1px solid #bbf7d0;
    }

    /* ── Empty / info state ── */
    .af-txn-empty {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        padding: 10px 12px;
        border: 1px dashed #d1d5db;
        border-radius: 10px;
        background: #f9fafb;
        font-size: 13px;
        color: #6b7280;
        line-height: 1.5;
    }

    .af-txn-empty-icon {
        width: 16px;
        height: 16px;
        flex-shrink: 0;
        margin-top: 1px;
        color: #9ca3af;
    }

    /* ── Generic empty state ── */
    .af-empty {
        padding: 14px 16px;
        border: 1px dashed #d1d5db;
        border-radius: 14px;
        background: #f9fafb;
        font-size: 14px;
        color: #6b7280;
    }

    /* ── Alpine transitions ── */
    .af-transition-enter       { transition: max-height 0.2s ease, opacity 0.2s ease; }
    .af-transition-enter-start { max-height: 0; opacity: 0; overflow: hidden; }
    .af-transition-enter-end   { max-height: 600px; opacity: 1; overflow: hidden; }
    .af-transition-leave       { transition: max-height 0.15s ease, opacity 0.15s ease; }
    .af-transition-leave-start { max-height: 600px; opacity: 1; overflow: hidden; }
    .af-transition-leave-end   { max-height: 0; opacity: 0; overflow: hidden; }

    /* ── Dark mode ── */
    .dark .af-description       { color: #d1d5db; }
    .dark .af-card              { border-color: #374151; background: #111827; }
    .dark .af-card-header       { border-color: #374151; background: #1f2937; }
    .dark .af-tier              { color: #f3f4f6; }
    .dark .af-label             { color: #9ca3af; }
    .dark .af-value             { color: #d1d5db; }

    .dark .af-eye-btn           { border-color: #4338ca; background: rgba(67,56,202,0.25); color: #c7d2fe; }
    .dark .af-eye-btn:hover     { background: rgba(67,56,202,0.4); border-color: #6366f1; }

    .dark .af-details-panel     { border-color: #374151; background: #1f2937; }
    .dark .af-details-heading   { color: #9ca3af; }
    .dark .af-detail-section    { border-color: #374151; background: #111827; }
    .dark .af-detail-label      { color: #6b7280; }
    .dark .af-detail-value      { color: #d1d5db; }
    .dark .af-txn-card          { border-color: #374151; background: #111827; }
    .dark .af-txn-heading       { color: #9ca3af; }
    .dark .af-credit            { color: #fca5a5; }
    .dark .af-badge-paid        { background: rgba(22,101,52,0.35); color: #86efac; }
    .dark .af-txn-empty         { border-color: #4b5563; background: #1f2937; color: #d1d5db; }
    .dark .af-txn-empty-icon    { color: #6b7280; }
    .dark .af-empty             { border-color: #4b5563; background: #1f2937; color: #d1d5db; }

    /* ── Mobile ── */
    @media (max-width: 640px) {
        .af-modal-content {
            --af-modal-offset: 140px;
            padding-right: 4px;
        }

        .af-row,
        .af-detail-row {
            align-items: flex-start;
            flex-direction: column;
            gap: 4px;
        }

        .af-value,
        .af-detail-value {
            text-align: left;
        }

        .af-eye-label {
            display: none;
        }
    }
</style>
