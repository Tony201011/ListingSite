@php
    /** @var \App\Models\ProviderProfile $providerProfile */
    /** @var \App\Models\PhotoVerification|null $verification */
    $galleryPreviewHeight = 120;
    $galleryPreviewWidth = 120;
@endphp

<div class="max-h-[75vh] overflow-y-auto px-1 pr-3">
    <div class="space-y-6">

        {{-- Provider Details --}}
        <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 bg-gray-50 px-4 py-3">
                <h3 class="text-sm font-semibold text-gray-900">
                    Provider Details
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <tr>
                            <th class="w-1/3 bg-gray-50 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                Provider Name
                            </th>
                            <td class="px-4 py-3 font-medium text-gray-900 break-words">
                                {{ $providerProfile->name ?? '—' }}
                            </td>
                        </tr>

                        <tr>
                            <th class="w-1/3 bg-gray-50 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                Profile Slug
                            </th>
                            <td class="px-4 py-3 text-gray-900 break-words">
                                {{ $providerProfile->slug ?? '—' }}
                            </td>
                        </tr>

                        <tr>
                            <th class="w-1/3 bg-gray-50 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                Provider Email
                            </th>
                            <td class="px-4 py-3 text-gray-900 break-all">
                                {{ $providerProfile->user?->email ?? '—' }}
                            </td>
                        </tr>

                        <tr>
                            <th class="w-1/3 bg-gray-50 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                Profile Status
                            </th>
                            <td class="px-4 py-3 text-gray-900">
                                {{ $providerProfile->profile_status ? ucfirst($providerProfile->profile_status) : '—' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        @if ($verification)
            {{-- Verification Submission --}}
            <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 bg-gray-50 px-4 py-3">
                    <h3 class="text-sm font-semibold text-gray-900">
                        Verification Submission
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr>
                                <th class="w-1/3 bg-gray-50 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                    Verification Status
                                </th>
                                <td class="px-4 py-3 font-semibold text-gray-900">
                                    {{ $verification->status ? ucfirst($verification->status) : '—' }}
                                </td>
                            </tr>

                            <tr>
                                <th class="w-1/3 bg-gray-50 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                    Submitted At
                                </th>
                                <td class="px-4 py-3 text-gray-900">
                                    {{ $verification->submitted_at?->format('M d, Y h:i A') ?: '—' }}
                                </td>
                            </tr>

                            <tr>
                                <th class="w-1/3 bg-gray-50 px-4 py-3 align-top text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                    Admin Notes
                                </th>
                                <td class="px-4 py-3 text-gray-900">
                                    <div class="max-h-40 overflow-y-auto whitespace-pre-wrap rounded-lg border border-gray-200 bg-gray-50 p-3 text-gray-800">
                                        {{ $verification->admin_note ?: 'No admin notes available.' }}
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            {{-- Uploaded Verification Images --}}
            <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 bg-gray-50 px-4 py-3">
                    <h3 class="text-sm font-semibold text-gray-900">
                        Uploaded Verification Images
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr>
                                <th class="w-1/3 bg-gray-50 px-4 py-3 align-top text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                    Images
                                </th>
                                <td class="px-4 py-3">
                                    <div class="photo-verification-gallery">
                                        {!! \App\Support\PhotoVerificationGalleryRenderer::render(
                                            $verification->photo_urls,
                                            $galleryPreviewHeight,
                                            $galleryPreviewWidth
                                        ) !!}
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        @else
            <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-4 text-sm text-gray-600">
                No photo verification submission is available for this provider profile yet.
            </div>
        @endif

    </div>
</div>

<style>
    .photo-verification-gallery,
    .photo-verification-gallery > div {
        display: flex !important;
        flex-wrap: wrap !important;
        gap: 12px !important;
        align-items: flex-start !important;
        max-width: 100% !important;
        overflow: hidden !important;
    }

    .photo-verification-gallery img {
        width: 120px !important;
        height: 120px !important;
        min-width: 120px !important;
        max-width: 120px !important;
        min-height: 120px !important;
        max-height: 120px !important;
        object-fit: cover !important;
        border-radius: 10px !important;
        border: 1px solid #e5e7eb !important;
        background: #f9fafb !important;
        display: block !important;
    }

    .photo-verification-gallery a {
        display: inline-block !important;
        width: 120px !important;
        height: 120px !important;
        overflow: hidden !important;
        border-radius: 10px !important;
    }

    .photo-verification-gallery iframe,
    .photo-verification-gallery embed,
    .photo-verification-gallery object {
        width: 120px !important;
        height: 120px !important;
        max-width: 120px !important;
        max-height: 120px !important;
        overflow: hidden !important;
    }
</style>
