@php
    /** @var \App\Models\ProviderProfile $providerProfile */
    /** @var \App\Models\PhotoVerification|null $verification */
    $galleryPreviewHeight = 120;
    $galleryPreviewWidth = 120;
@endphp

<div class="space-y-4">
    <div class="grid gap-3 sm:grid-cols-2">
        <div>
            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Provider Name</div>
            <div class="text-sm font-semibold text-gray-900">{{ $providerProfile->name ?? '—' }}</div>
        </div>
        <div>
            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Profile Slug</div>
            <div class="text-sm text-gray-900">{{ $providerProfile->slug ?? '—' }}</div>
        </div>
        <div>
            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Provider Email</div>
            <div class="text-sm text-gray-900">{{ $providerProfile->user?->email ?? '—' }}</div>
        </div>
        <div>
            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Profile Status</div>
            <div class="text-sm text-gray-900">{{ $providerProfile->profile_status ? ucfirst($providerProfile->profile_status) : '—' }}</div>
        </div>
    </div>

    @if ($verification)
        <div class="grid gap-3 sm:grid-cols-2">
            <div>
                <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Verification Status</div>
                <div class="text-sm font-semibold text-gray-900">{{ $verification->status ? ucfirst($verification->status) : '—' }}</div>
            </div>
            <div>
                <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Submitted At</div>
                <div class="text-sm text-gray-900">{{ $verification->submitted_at?->format('M d, Y h:i A') ?: '—' }}</div>
            </div>
        </div>

        <div>
            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Admin Notes</div>
            <div class="mt-1 whitespace-pre-wrap rounded-lg border border-gray-200 bg-gray-50 p-3 text-sm text-gray-800">
                {{ $verification->admin_note ?: 'No admin notes available.' }}
            </div>
        </div>

        <div>
            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Uploaded Verification Images</div>
            <div class="mt-2">
                {{ \App\Support\PhotoVerificationGalleryRenderer::render($verification->photo_urls, $galleryPreviewHeight, $galleryPreviewWidth) }}
            </div>
        </div>
    @else
        <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 p-4 text-sm text-gray-600">
            No photo verification submission is available for this provider profile yet.
        </div>
    @endif
</div>
