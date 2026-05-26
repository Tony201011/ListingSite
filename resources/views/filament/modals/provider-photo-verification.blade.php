@php
    /** @var \App\Models\ProviderProfile $providerProfile */
    /** @var \App\Models\PhotoVerification|null $verification */
    $verificationPhotos = is_array($verificationPhotos ?? null) ? $verificationPhotos : ($verification?->photos ?? []);
    $galleryPreviewHeight = 120;
    $galleryPreviewWidth = 120;
@endphp

<div class="pv-modal-content">

    <section class="pv-card">
        <div class="pv-card-header">Provider Details</div>

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
    </section>

    @if ($verification)
        <section class="pv-card">
            <div class="pv-card-header">Verification Submission</div>

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
        </section>

        <section class="pv-card">
            <div class="pv-card-header">Uploaded Verification Photos</div>

            <div class="pv-gallery-container">
                <div class="photo-verification-gallery">
                    {!! \App\Support\PhotoVerificationGalleryRenderer::render(
                        $verificationPhotos,
                        $galleryPreviewHeight,
                        $galleryPreviewWidth,
                        null,
                        false,
                        true
                    ) !!}
                </div>
            </div>
        </section>
    @else
        <div class="pv-empty">
            No photo verification submission is available for this provider profile yet.
        </div>
    @endif

</div>

<style>
    .pv-modal-content {
        max-height: calc(100vh - 180px);
        overflow-y: auto;
        padding: 4px 8px 16px;
        display: flex;
        flex-direction: column;
        gap: 22px;
    }

    .pv-card {
        width: 100%;
        overflow: hidden;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
    }

    .pv-card-header {
        padding: 16px 20px;
        background: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
        font-size: 16px;
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
        width: 275px;
        padding: 20px;
        background: #f9fafb;
        border-right: 1px solid #e5e7eb;
        text-align: left;
        vertical-align: top;
        font-size: 14px;
        font-weight: 700;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .pv-table td {
        padding: 20px;
        vertical-align: top;
        font-size: 17px;
        color: #111827;
        word-break: break-word;
    }

    .pv-badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 14px;
        border-radius: 999px;
        background: #d1fae5;
        color: #047857;
        font-size: 14px;
        font-weight: 700;
    }

    .pv-note {
        width: 100%;
        min-height: 48px;
        max-height: 130px;
        overflow-y: auto;
        padding: 12px 14px;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        background: #f9fafb;
        color: #374151;
        font-size: 15px;
        line-height: 1.5;
        white-space: pre-wrap;
    }

    .pv-gallery-container {
        padding: 16px 20px 20px;
    }

    .photo-verification-gallery,
    .photo-verification-gallery > div {
        display: flex !important;
        flex-wrap: wrap !important;
        gap: 14px !important;
        align-items: flex-start !important;
        max-width: 100% !important;
        overflow: visible !important;
    }

    .photo-verification-gallery a,
    .photo-verification-gallery button,
    .photo-verification-gallery img,
    .photo-verification-gallery iframe,
    .photo-verification-gallery embed,
    .photo-verification-gallery object {
        width: 150px !important;
        height: 150px !important;
        min-width: 150px !important;
        max-width: 150px !important;
        min-height: 150px !important;
        max-height: 150px !important;
        display: block !important;
        overflow: hidden !important;
        border-radius: 14px !important;
        cursor: pointer !important;
    }

    .photo-verification-gallery img {
        object-fit: cover !important;
        border: 1px solid #e5e7eb !important;
        background: #f9fafb !important;
    }

    .pv-empty {
        padding: 16px;
        border: 1px dashed #d1d5db;
        border-radius: 14px;
        background: #f9fafb;
        color: #6b7280;
        font-size: 14px;
    }

    @media (max-width: 640px) {
        .pv-modal-content {
            max-height: calc(100vh - 140px);
            padding-right: 4px;
        }

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
            padding: 14px 16px 8px;
        }

        .pv-table td {
            padding: 8px 16px 16px;
            font-size: 15px;
        }

        .photo-verification-gallery a,
        .photo-verification-gallery button,
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
        }
    }
</style>
