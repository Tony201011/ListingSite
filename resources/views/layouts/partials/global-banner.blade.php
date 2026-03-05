@php
    $currentPath = trim(request()->path(), '/');
    $currentPageKey = $currentPath === '' ? 'home' : $currentPath;

    $banner = \App\Models\GlobalBanner::query()
        ->where(function ($query) use ($currentPageKey) {
            $query
                ->whereJsonContains('page_keys', $currentPageKey)
                ->orWhere('page_key', $currentPageKey);
        })
        ->where('is_active', true)
        ->latest('updated_at')
        ->first();

    $globalBanner = \App\Models\GlobalBanner::query()
        ->where(function ($query) {
            $query
                ->whereJsonContains('page_keys', 'all-pages')
                ->orWhere('page_key', 'all-pages');
        })
        ->where('is_active', true)
        ->latest('updated_at')
        ->first();

    $defaultEnabledPages = ['signin', 'signup', 'reset-password', 'about-us', 'pricing', 'help', 'otp-verification', 'my-rate'];
    $defaultBannerImage = 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?q=80&w=1200&auto=format&fit=crop';

    $shouldShow = $banner || $globalBanner || in_array($currentPageKey, $defaultEnabledPages, true);

    if ($banner && filled($banner->banner_image_path)) {
        $bannerImage = \Illuminate\Support\Facades\Storage::disk('public')->url($banner->banner_image_path);
    } elseif ($globalBanner && filled($globalBanner->banner_image_path)) {
        $bannerImage = \Illuminate\Support\Facades\Storage::disk('public')->url($globalBanner->banner_image_path);
    } else {
        $bannerImage = $defaultBannerImage;
    }

    $bannerTitle = $banner?->banner_title ?: ($globalBanner?->banner_title ?: 'hotescorts.com.au');
    $bannerSubtitle = $banner?->banner_subtitle ?: ($globalBanner?->banner_subtitle ?: 'REAL WOMEN NEAR YOU');
@endphp

@if($shouldShow)
    <div class="relative overflow-hidden bg-gradient-to-r from-[#e04ecb] to-[#c13ab0]">
        <div class="absolute inset-0 bg-cover bg-center opacity-20" style="background-image: url('{{ $bannerImage }}');"></div>
        <div class="relative z-10 max-w-6xl mx-auto px-5 py-16 text-center">
            <h1 class="text-5xl md:text-6xl font-extrabold text-white mb-2 drop-shadow-lg">{{ $bannerTitle }}</h1>
            <p class="text-xl text-white/90 tracking-widest">{{ $bannerSubtitle }}</p>
        </div>
    </div>
@endif
