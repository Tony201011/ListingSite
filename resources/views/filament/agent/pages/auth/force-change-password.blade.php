<x-filament-panels::page>
    <div
        x-data="{
            showPasswordPopup: false,
            generatedPassword: '',
            copied: false,
            generatePasswordPopup() {
                const upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                const lower = 'abcdefghijklmnopqrstuvwxyz';
                const numbers = '0123456789';
                const symbols = '!@#$%^&*()-_=+[]{}?';
                const all = upper + lower + numbers + symbols;
                let password = '';
                password += upper[Math.floor(Math.random() * upper.length)];
                password += lower[Math.floor(Math.random() * lower.length)];
                password += numbers[Math.floor(Math.random() * numbers.length)];
                password += symbols[Math.floor(Math.random() * symbols.length)];
                for (let i = password.length; i < 16; i++) {
                    password += all[Math.floor(Math.random() * all.length)];
                }
                this.generatedPassword = password.split('').sort(() => Math.random() - 0.5).join('');
                this.copied = false;
                this.showPasswordPopup = true;
            },
            useGeneratedPassword() {
                $wire.set('new_password', this.generatedPassword);
                $wire.set('new_password_confirmation', this.generatedPassword);
                this.showPasswordPopup = false;
            },
            async copyGeneratedPassword() {
                try {
                    await navigator.clipboard.writeText(this.generatedPassword);
                    this.copied = true;
                    setTimeout(() => { this.copied = false; }, 1500);
                } catch (e) {}
            }
        }"
        class="max-w-md mx-auto"
    >
        <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-white/10 dark:bg-white/5">
            <h2 class="text-lg font-semibold text-gray-950 dark:text-white mb-1">Change Your Password</h2>
            <p class="text-sm text-gray-600 dark:text-gray-300 mb-6">
                You must set a new password before continuing.
            </p>

            <form wire:submit="save">
                {{ $this->form }}

                <div class="mt-3 mb-4">
                    <button
                        type="button"
                        @click="generatePasswordPopup()"
                        class="px-4 py-2 rounded-xl bg-[#fdf0fb] text-[#c13ab0] font-semibold border border-[#f3c4ea] hover:bg-[#fae3f6] transition text-sm"
                    >
                        Generate Password
                    </button>
                </div>

                <div class="mt-6">
                    <x-filament::button type="submit" class="w-full">
                        Change Password
                    </x-filament::button>
                </div>
            </form>
        </div>

        <div
            x-show="showPasswordPopup"
            x-cloak
            x-transition
            @click.away="showPasswordPopup = false"
            class="fixed z-50 top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[calc(100%-2rem)] max-w-sm bg-white border border-gray-200 rounded-2xl shadow-xl p-4"
        >
            <div class="flex items-start justify-between gap-3 mb-3">
                <div>
                    <h4 class="font-bold text-gray-800">Strong password suggestion</h4>
                    <p class="text-sm text-gray-500">Save this password somewhere safe before using it.</p>
                </div>
                <button
                    type="button"
                    @click="showPasswordPopup = false"
                    class="text-gray-400 hover:text-gray-600 text-xl leading-none"
                >
                    &times;
                </button>
            </div>

            <div
                class="bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 font-mono text-sm break-all text-gray-800 mb-4"
                x-text="generatedPassword"
            ></div>

            <div class="flex flex-wrap gap-2">
                <button
                    type="button"
                    @click="generatePasswordPopup()"
                    class="px-4 py-2 rounded-lg border border-gray-200 text-gray-700 font-medium hover:bg-gray-50"
                >
                    Regenerate
                </button>

                <button
                    type="button"
                    @click="copyGeneratedPassword()"
                    class="px-4 py-2 rounded-lg border border-gray-200 text-gray-700 font-medium hover:bg-gray-50"
                >
                    <span x-text="copied ? 'Copied!' : 'Copy'"></span>
                </button>

                <button
                    type="button"
                    @click="useGeneratedPassword()"
                    class="px-4 py-2 rounded-lg bg-[#e04ecb] text-white font-semibold hover:bg-[#c13ab0]"
                >
                    Use this password
                </button>
            </div>
        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</x-filament-panels::page>
