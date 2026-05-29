<x-filament-panels::page>
    <div class="w-full mt-2">
        <form wire:submit.prevent="save" class="space-y-4">
            <div>
                <label for="meta_key" class="block text-sm font-medium">Meta Key</label>
                <input type="text" id="meta_key" wire:model="meta_key" class="w-full border rounded px-3 py-2" />
            </div>
            <div>
                <label for="meta_description" class="block text-sm font-medium">Meta Description</label>
                <textarea id="meta_description" wire:model="meta_description" class="w-full border rounded px-3 py-2"></textarea>
            </div>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Save</button>
            @if(session('success'))
                <div class="text-green-600">{{ session('success') }}</div>
            @endif
        </form>
    </div>
</x-filament-panels::page>
