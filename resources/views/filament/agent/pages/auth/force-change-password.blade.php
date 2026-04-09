<x-filament-panels::page>
    <style>
        [x-cloak] { display: none !important; }
    </style>

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
        class="max-w-lg mx-auto"
    >
        {{-- Card --}}
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900 overflow-hidden">
            {{-- Header banner --}}
            <div class="bg-gradient-to-r from-[#e04ecb] to-[#c13ab0] px-6 py-5">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-white/20 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-white leading-tight">Change Your Password</h2>
                        <p class="text-xs text-white/80 mt-0.5">You must set a new password before continuing.</p>
                    </div>
                </div>
            </div>

            {{-- Form body --}}
            <div class="px-6 py-6">
                <form wire:submit="save">
                    {{ $this->form }}

                    <div class="mt-4 mb-5">
                        <button
                            type="button"
                            @click="generatePasswordPopup()"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#fdf0fb] text-[#c13ab0] font-semibold border border-[#f3c4ea] hover:bg-[#fae3f6] transition text-sm"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                            </svg>
                            Generate Password
                        </button>
                    </div>

                    <x-filament::button type="submit" size="lg" class="w-full justify-center">
                        Change Password
                    </x-filament::button>
                </form>
            </div>
        </div>

        {{-- Popup backdrop --}}
        <div
            x-show="showPasswordPopup"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="showPasswordPopup = false"
            class="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm"
        ></div>

        {{-- Popup modal --}}
        <div
            x-show="showPasswordPopup"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="fixed z-50 top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[calc(100%-2rem)] max-w-sm bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-2xl shadow-2xl p-5"
        >
            <div class="flex items-start justify-between gap-3 mb-4">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-[#fdf0fb] flex items-center justify-center flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-[#c13ab0]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-900 dark:text-white text-sm">Strong password suggestion</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Save this somewhere safe before using it.</p>
                    </div>
                </div>
                <button
                    type="button"
                    @click="showPasswordPopup = false"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition rounded-full w-7 h-7 flex items-center justify-center hover:bg-gray-100 dark:hover:bg-white/10"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div
                class="bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl px-4 py-3 font-mono text-sm break-all text-gray-800 dark:text-gray-100 mb-4 select-all"
                x-text="generatedPassword"
            ></div>

            <div class="flex flex-wrap gap-2">
                <button
                    type="button"
                    @click="generatePasswordPopup()"
                    class="flex-1 px-3 py-2 rounded-lg border border-gray-200 dark:border-white/10 text-gray-700 dark:text-gray-300 font-medium hover:bg-gray-50 dark:hover:bg-white/5 transition text-sm"
                >
                    Regenerate
                </button>

                <button
                    type="button"
                    @click="copyGeneratedPassword()"
                    class="flex-1 px-3 py-2 rounded-lg border border-gray-200 dark:border-white/10 text-gray-700 dark:text-gray-300 font-medium hover:bg-gray-50 dark:hover:bg-white/5 transition text-sm"
                >
                    <span x-text="copied ? '✓ Copied!' : 'Copy'"></span>
                </button>

                <button
                    type="button"
                    @click="useGeneratedPassword()"
                    class="w-full px-3 py-2 rounded-lg bg-gradient-to-r from-[#e04ecb] to-[#c13ab0] text-white font-semibold hover:opacity-90 transition text-sm"
                >
                    Use this password
                </button>
            </div>
        </div>
    </div>
</x-filament-panels::page>
