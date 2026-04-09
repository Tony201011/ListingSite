<x-filament-panels::page>
    <div class="max-w-md mx-auto">
        <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-white/10 dark:bg-white/5">
            <h2 class="text-lg font-semibold text-gray-950 dark:text-white mb-1">Change Your Password</h2>
            <p class="text-sm text-gray-600 dark:text-gray-300 mb-6">
                You must set a new password before continuing.
            </p>

            <form wire:submit="save">
                {{ $this->form }}

                <div class="mt-6">
                    <x-filament::button type="submit" class="w-full">
                        Change Password
                    </x-filament::button>
                </div>
            </form>
        </div>
    </div>
</x-filament-panels::page>
