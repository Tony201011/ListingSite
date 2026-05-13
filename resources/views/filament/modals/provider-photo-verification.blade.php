@php
    /** @var \App\Models\ProviderProfile $providerProfile */
    /** @var \App\Models\PhotoVerification|null $verification */
    $galleryPreviewHeight = 120;
    $galleryPreviewWidth = 120;
@endphp

<div class="pv-modal-content">
    <div class="pv-section">
        <div class="pv-section-header">
            Provider Details
        </div>

        <table class="pv-table">
            <tbody>
                <tr>
                    <th>Provider Name</th>
                    <td>{{ $providerProfile->name ?? '—' }}</td>
                </tr>
                <tr>
                    <th>Profile Slug</th>
                    <td>{{ $providerProfile->slug ?? '—' }}</td>
                </tr>
                <tr>
                    <th>Provider Email</th>
                    <td>{{ $providerProfile->user?->email ?? '—' }}</td>
                </tr>
                <tr>
                    <th>Profile Status</th>
                    <td>
                        <span class="pv-badge">
                            {{ $providerProfile->profile_status ? ucfirst($providerProfile->profile_status) : '—' }}
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    @if ($verification)
        <div class="pv-section">
            <div class="pv-section-header">
                Verification Submission
            </div>

            <table class="pv-table">
                <tbody>
                    <tr>
                        <th>Verification Status</th>
                        <td>
                            <span class="pv-badge">
                                {{ $verification->status ? ucfirst($verification->status) : '—' }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Submitted At</th>
                        <td>{{ $verification->submitted_at?->format('M d, Y h:i A') ?: '—' }}</td>
                    </tr>
                    <tr>
                        <th>Admin Notes</th>
                        <td>
                            <div class="pv-note">
                                {{ $verification->admin_note ?: 'No admin notes available.' }}
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="pv-section">
            <div class="pv-section-header">
                Uploaded Verification Images
            </div>

            <div class="pv-images-wrap">
                <div class="photo-verification-gallery">
                    {!! \App\Support\PhotoVerificationGalleryRenderer::render(
                        $verification->photo_urls,
                        $galleryPreviewHeight,
                        $galleryPreviewWidth
                    ) !!}
                </div>
            </div>
        </div>
    @else
        <div class="pv-empty">
            No photo verification submission is available for this provider profile yet.
        </div>
    @endif
</div>

<style>
    .pv-modal-content {
        max-height: 72vh;
        overflow-y: auto;
        padding: 4px 12px 4px 4px;
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    .pv-section {
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        overflow: hidden;
        background: #ffffff;
    }

    .pv-section-header {
        padding: 12px 16px;
        background: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
        font-size: 13px;
        font-weight: 700;
        color: #111827;
    }

    .pv-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .pv-table tr {
        border-bottom: 1px solid #e5e7eb;
    }

    .pv-table tr:last-child {
        border-bottom: none;
    }

    .pv-table th {
        width: 220px;
        padding: 14px 16px;
        background: #f9fafb;
        text-align: left;
        vertical-align: top;
        font-size: 12px;
        font-weight: 700;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        border-right: 1px solid #e5e7eb;
    }

    .pv-table td {
        padding: 14px 16px;
        vertical-align: top;
        font-size: 14px;
        color: #111827;
        word-break: break-word;
    }

    .pv-badge {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        background: #ecfdf5;
        color: #047857;
        padding: 4px 10px;
        font-size: 12px;
        font-weight: 700;
    }

    .pv-note {
        min-height: 44px;
        max-height: 140px;
        overflow-y: auto;
        white-space: pre-wrap;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        background: #f9fafb;
        padding: 12px;
        color: #374151;
    }

    .pv-images-wrap {
        padding: 16px;
    }

    .photo-verification-gallery,
    .photo-verification-gallery > div {
        display: flex !important;
        flex-wrap: wrap !important;
        gap: 14px !important;
        align-items: flex-start !important;
        max-width: 100% !important;
    }

    .photo-verification-gallery a,
    .photo-verification-gallery img,
    .photo-verification-gallery iframe,
    .photo-verification-gallery embed,
    .photo-verification-gallery object {
        width: 120px !important;
        height: 120px !important;
        min-width: 120px !important;
        max-width: 120px !important;
        min-height: 120px !important;
        max-height: 120px !important;
        display: block !important;
        overflow: hidden !important;
        border-radius: 12px !important;
    }

    .photo-verification-gallery img {
        object-fit: cover !important;
        border: 1px solid #e5e7eb !important;
        background: #f9fafb !important;
    }

    .pv-empty {
        border: 1px dashed #d1d5db;
        border-radius: 12px;
        background: #f9fafb;
        padding: 16px;
        font-size: 14px;
        color: #6b7280;
    }

    @media (max-width: 640px) {
        .pv-table,
        .pv-table tbody,
        .pv-table tr,
        .pv-table th,
        .pv-table td {
            display: block;
            width: 100%;
        }

        .pv-table th {
            border-right: none;
            border-bottom: 1px solid #e5e7eb;
        }

        .pv-table td {
            border-bottom: 1px solid #e5e7eb;
        }
    }
</style>
