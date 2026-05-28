@props([
    'variant' => 'minimal',
    'position' => 'top-left',
    'label' => 'Featured',
    'icon' => 'crown',
])

@php
    $variant = strtolower((string) $variant);
    $position = strtolower((string) $position);
    $icon = strtolower((string) $icon);
    $isInline = $position === 'inline';

    $iconPaths = [
        'star' => 'M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.386a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.563.563 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z',
        'fire' => 'M12 2.25c.414 2.75-1.27 4.456-2.57 5.773-1.05 1.063-1.93 1.954-1.93 3.227a4.5 4.5 0 109 0c0-2.676-2.076-4.238-4.5-9z',
        'crown' => 'M2.25 8.25a.75.75 0 01.75-.75h2.126a.75.75 0 01.696.467l1.318 3.162 3.512-5.268a.75.75 0 011.248 0l3.512 5.268 1.318-3.162a.75.75 0 01.696-.467H21a.75.75 0 01.724.946l-2.25 8.25a.75.75 0 01-.724.554H5.25a.75.75 0 01-.724-.554l-2.25-8.25a.75.75 0 01-.026-.196z',
    ];

    $iconPath = $iconPaths[$icon] ?? $iconPaths['crown'];

    $positionClass = match ($position) {
        'inline' => '',
        'top-right' => 'absolute right-2 top-2 sm:right-3 sm:top-3',
        'diagonal' => 'absolute -right-10 top-3 rotate-45 sm:-right-9 sm:top-4',
        default => 'absolute left-2 top-2 sm:left-3 sm:top-3',
    };
@endphp

@if($variant === 'ribbon')
    <div {{ $attributes->class(['pointer-events-none select-none z-30', $positionClass]) }}>
        <span @class([
            'inline-flex items-center justify-center gap-1.5 bg-gradient-to-r from-amber-300 via-yellow-300 to-amber-400 py-1 text-[10px] font-bold uppercase tracking-[0.16em] text-amber-950 shadow-[0_4px_14px_rgba(245,158,11,0.45)] ring-1 ring-amber-200/80 sm:text-[11px] dark:from-amber-500 dark:via-yellow-500 dark:to-amber-600 dark:text-gray-900 dark:ring-amber-300/30 whitespace-nowrap',
            'rounded-full px-3 sm:px-3.5' => $isInline,
            'w-32 rounded-md px-2 sm:w-36' => ! $isInline,
        ])>
            <svg class="h-3 w-3 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="{{ $iconPath }}" />
            </svg>
            <span>{{ $label }}</span>
        </span>
    </div>
@elseif($variant === 'glow')
    <div {{ $attributes->class(['pointer-events-none select-none z-30', $positionClass]) }}>
        <span class="relative inline-flex items-center gap-1.5 rounded-full bg-gradient-to-r from-amber-300 via-yellow-300 to-amber-400 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.14em] text-amber-950 shadow-[0_6px_20px_rgba(245,158,11,0.5)] ring-1 ring-amber-200/80 transition-all duration-300 group-hover:scale-[1.04] group-hover:shadow-[0_10px_28px_rgba(245,158,11,0.6)] sm:px-3.5 sm:text-[11px] dark:from-amber-500 dark:via-yellow-500 dark:to-amber-600 dark:text-gray-900 dark:ring-amber-300/30 whitespace-nowrap">
            <span class="absolute inset-0 rounded-full bg-amber-300/40 blur-[6px]"></span>
            <span class="relative inline-flex items-center gap-1.5">
                <svg class="h-3 w-3 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="{{ $iconPath }}" />
                </svg>
                <span>{{ $label }}</span>
            </span>
        </span>
    </div>
@else
    <div {{ $attributes->class(['pointer-events-none select-none z-30', $positionClass]) }}>
        <span class="inline-flex items-center gap-1.5 rounded-full bg-gradient-to-r from-amber-300 via-yellow-300 to-amber-400 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.14em] text-amber-950 shadow-[0_4px_14px_rgba(245,158,11,0.4)] ring-1 ring-amber-200/80 transition-all duration-300 group-hover:shadow-[0_8px_20px_rgba(245,158,11,0.5)] sm:px-3.5 sm:text-[11px] dark:from-amber-500 dark:via-yellow-500 dark:to-amber-600 dark:text-gray-900 dark:ring-amber-300/30 whitespace-nowrap">
            <svg class="h-3 w-3 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="{{ $iconPath }}" />
            </svg>
            <span>{{ $label }}</span>
        </span>
    </div>
@endif
