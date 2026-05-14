<div class="af-modal-content">
    <p class="af-description">
        Current ad & featured visibility status for this provider profile.
    </p>

    @if (filled($rows))
        <div class="af-grid">
            @foreach ($rows as $row)
                <section class="af-card">
                    <div class="af-card-header">
                        <h3 class="af-tier">{{ $row['tier'] }}</h3>
                    </div>

                    <div class="af-card-body">
                        <div class="af-row">
                            <span class="af-label">Status</span>
                            <span
                                @class([
                                    'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset',
                                    $row['status_class'] ?? 'bg-gray-100 text-gray-700 ring-gray-200',
                                ])
                            >
                                {{ $row['status'] }}
                            </span>
                        </div>

                        <div class="af-row">
                            <span class="af-label">Expiry</span>
                            <span class="af-value">{{ $row['expiry'] }}</span>
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
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 14px;
    }

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

    .af-tier {
        margin: 0;
        font-size: 14px;
        font-weight: 700;
        color: #111827;
    }

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
    }

    .af-value {
        font-size: 14px;
        color: #374151;
        text-align: right;
        word-break: break-word;
    }

    .af-empty {
        padding: 14px 16px;
        border: 1px dashed #d1d5db;
        border-radius: 14px;
        background: #f9fafb;
        font-size: 14px;
        color: #6b7280;
    }

    .dark .af-description {
        color: #d1d5db;
    }

    .dark .af-card {
        border-color: #374151;
        background: #111827;
    }

    .dark .af-card-header {
        border-color: #374151;
        background: #1f2937;
    }

    .dark .af-tier {
        color: #f3f4f6;
    }

    .dark .af-label {
        color: #9ca3af;
    }

    .dark .af-value {
        color: #d1d5db;
    }

    .dark .af-empty {
        border-color: #4b5563;
        background: #1f2937;
        color: #d1d5db;
    }

    @media (max-width: 640px) {
        .af-modal-content {
            --af-modal-offset: 140px;
            padding-right: 4px;
        }

        .af-row {
            align-items: flex-start;
            flex-direction: column;
            gap: 6px;
        }

        .af-value {
            text-align: left;
        }
    }
</style>
