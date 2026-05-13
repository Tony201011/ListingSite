@php
    /** @var \App\Models\ProviderProfile $providerProfile */
    /** @var \App\Models\PhotoVerification|null $verification */
    $galleryPreviewHeight = 120;
    $galleryPreviewWidth = 120;
@endphp

<div class="space-y-5">
    <section class="overflow-hidden rounded-xl border border-gray-200 bg-white">
        <div class="border-b border-gray-200 bg-gray-50 px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-600">
            Provider Details
        </div>
        <div class="grid gap-px bg-gray-200 sm:grid-cols-2">
            <div class="space-y-1 bg-white px-4 py-3">
                <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Provider Name</div>
                <div class="text-sm font-semibold text-gray-900 break-all">{{ $providerProfile->name ?? '—' }}</div>
            </div>
            <div class="space-y-1 bg-white px-4 py-3">
                <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Profile Slug</div>
                <div class="text-sm text-gray-900 break-all">{{ $providerProfile->slug ?? '—' }}</div>
            </div>
            <div class="space-y-1 bg-white px-4 py-3">
                <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Provider Email</div>
                <div class="text-sm text-gray-900 break-all">{{ $providerProfile->user?->email ?? '—' }}</div>
            </div>
            <div class="space-y-1 bg-white px-4 py-3">
                <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Profile Status</div>
                <div class="text-sm text-gray-900">{{ $providerProfile->profile_status ? ucfirst($providerProfile->profile_status) : '—' }}</div>
            </div>
        </div>
    </section>

    @if ($verification)
        <section class="overflow-hidden rounded-xl border border-gray-200 bg-white">
            <div class="border-b border-gray-200 bg-gray-50 px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-600">
                Verification Submission
            </div>
            <div class="grid gap-px bg-gray-200 sm:grid-cols-2">
                <div class="space-y-1 bg-white px-4 py-3">
                    <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Verification Status</div>
                    <div class="text-sm font-semibold text-gray-900">{{ $verification->status ? ucfirst($verification->status) : '—' }}</div>
                </div>
                <div class="space-y-1 bg-white px-4 py-3">
                    <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Submitted At</div>
                    <div class="text-sm text-gray-900">{{ $verification->submitted_at?->format('M d, Y h:i A') ?: '—' }}</div>
                </div>
                <div class="space-y-1 bg-white px-4 py-3 sm:col-span-2">
                    <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Admin Notes</div>
                    <div class="whitespace-pre-wrap rounded-lg border border-gray-200 bg-gray-50 p-3 text-sm text-gray-800">
                        {{ $verification->admin_note ?: 'No admin notes available.' }}
                    </div>
                </div>
            </div>
        </section>

        <section class="overflow-hidden rounded-xl border border-gray-200 bg-white">
            <div class="border-b border-gray-200 bg-gray-50 px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-600">
                Uploaded Verification Images
            </div>
            <div class="px-4 py-3">
                {{ \App\Support\PhotoVerificationGalleryRenderer::render($verification->photo_urls, $galleryPreviewHeight, $galleryPreviewWidth) }}
            </div>
        </section>
    @else
        <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 p-4 text-sm text-gray-600">
            No photo verification submission is available for this provider profile yet.
        </div>
    @endif
</div>
