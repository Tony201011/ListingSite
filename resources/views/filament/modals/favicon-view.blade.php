@props(['icon'])
<div class="flex flex-col items-center justify-center p-4">
    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($icon) }}" alt="Favicon" class="w-24 h-24 object-contain mb-2" />
    <span class="text-gray-600 text-xs">{{ $icon }}</span>
</div>
