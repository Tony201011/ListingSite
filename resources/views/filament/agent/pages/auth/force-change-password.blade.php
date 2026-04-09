<x-filament-panels::page>
    <style>
        [x-cloak] { display: none !important; }

        /* ── Wrapper ─────────────────────────────────────────── */
        .fcp-wrapper {
            max-width: 32rem;
            margin-left: auto;
            margin-right: auto;
        }

        /* ── Card ────────────────────────────────────────────── */
        .fcp-card {
            border-radius: 1rem;
            border: 1px solid #e5e7eb;
            background-color: #ffffff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        /* ── Header banner ───────────────────────────────────── */
        .fcp-header {
            background: linear-gradient(to right, #e04ecb, #c13ab0);
            padding: 1.25rem 1.5rem;
        }
        .fcp-header-inner {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .fcp-icon-circle-lg {
            flex-shrink: 0;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 9999px;
            background-color: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .fcp-icon-lg {
            width: 1.25rem;
            height: 1.25rem;
            color: #ffffff;
        }
        .fcp-header-title {
            font-size: 1rem;
            font-weight: 700;
            color: #ffffff;
            line-height: 1.25;
            margin: 0;
        }
        .fcp-header-sub {
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.8);
            margin: 0.125rem 0 0;
        }

        /* ── Form body ───────────────────────────────────────── */
        .fcp-body {
            padding: 1.5rem;
        }

        /* ── Generate-password button ────────────────────────── */
        .fcp-generate-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 0.75rem;
            background-color: #fdf0fb;
            color: #c13ab0;
            font-weight: 600;
            font-size: 0.875rem;
            border: 1px solid #f3c4ea;
            cursor: pointer;
            transition: background-color 0.15s ease;
            margin-top: 1rem;
            margin-bottom: 1.25rem;
        }
        .fcp-generate-btn:hover {
            background-color: #fae3f6;
        }
        .fcp-generate-btn svg {
            width: 1rem;
            height: 1rem;
        }

        /* ── Backdrop ────────────────────────────────────────── */
        .fcp-backdrop {
            position: fixed;
            inset: 0;
            z-index: 40;
            background-color: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(4px);
        }

        /* ── Modal ───────────────────────────────────────────── */
        .fcp-modal {
            position: fixed;
            z-index: 50;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: calc(100% - 2rem);
            max-width: 24rem;
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 1rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            padding: 1.25rem;
        }
        .fcp-modal-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }
        .fcp-modal-title-row {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .fcp-icon-circle-sm {
            flex-shrink: 0;
            width: 2rem;
            height: 2rem;
            border-radius: 9999px;
            background-color: #fdf0fb;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .fcp-icon-sm {
            width: 1rem;
            height: 1rem;
            color: #c13ab0;
        }
        .fcp-modal-title {
            font-size: 0.875rem;
            font-weight: 700;
            color: #111827;
            margin: 0;
        }
        .fcp-modal-sub {
            font-size: 0.75rem;
            color: #6b7280;
            margin: 0;
        }
        .fcp-close-btn {
            color: #9ca3af;
            background: transparent;
            border: none;
            cursor: pointer;
            border-radius: 9999px;
            width: 1.75rem;
            height: 1.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.15s ease, background-color 0.15s ease;
            padding: 0;
        }
        .fcp-close-btn:hover {
            color: #4b5563;
            background-color: #f3f4f6;
        }
        .fcp-close-btn svg {
            width: 1rem;
            height: 1rem;
        }

        /* ── Generated-password display ──────────────────────── */
        .fcp-password-display {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
            font-family: monospace;
            font-size: 0.875rem;
            word-break: break-all;
            color: #1f2937;
            margin-bottom: 1rem;
            user-select: all;
        }

        /* ── Modal action buttons ────────────────────────────── */
        .fcp-btn-row {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .fcp-btn-secondary {
            flex: 1;
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
            border: 1px solid #e5e7eb;
            color: #374151;
            font-weight: 500;
            font-size: 0.875rem;
            background-color: #ffffff;
            cursor: pointer;
            transition: background-color 0.15s ease;
        }
        .fcp-btn-secondary:hover {
            background-color: #f9fafb;
        }
        .fcp-btn-primary {
            width: 100%;
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
            border: none;
            background: linear-gradient(to right, #e04ecb, #c13ab0);
            color: #ffffff;
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            transition: opacity 0.15s ease;
        }
        .fcp-btn-primary:hover {
            opacity: 0.9;
        }
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
        class="fcp-wrapper"
    >
        {{-- Card --}}
        <div class="fcp-card">
            {{-- Header banner --}}
            <div class="fcp-header">
                <div class="fcp-header-inner">
                    <div class="fcp-icon-circle-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="fcp-icon-lg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="fcp-header-title">Change Your Password</h2>
                        <p class="fcp-header-sub">You must set a new password before continuing.</p>
                    </div>
                </div>
            </div>

            {{-- Form body --}}
            <div class="fcp-body">
                <form wire:submit="save">
                    {{ $this->form }}

                    <button
                        type="button"
                        @click="generatePasswordPopup()"
                        class="fcp-generate-btn"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                        </svg>
                        Generate Password
                    </button>

                    <x-filament::button type="submit" size="lg" style="width:100%;justify-content:center;">
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
            class="fcp-backdrop"
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
            class="fcp-modal"
        >
            <div class="fcp-modal-header">
                <div class="fcp-modal-title-row">
                    <div class="fcp-icon-circle-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="fcp-icon-sm" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="fcp-modal-title">Strong password suggestion</h4>
                        <p class="fcp-modal-sub">Save this somewhere safe before using it.</p>
                    </div>
                </div>
                <button
                    type="button"
                    @click="showPasswordPopup = false"
                    class="fcp-close-btn"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div
                class="fcp-password-display"
                x-text="generatedPassword"
            ></div>

            <div class="fcp-btn-row">
                <button
                    type="button"
                    @click="generatePasswordPopup()"
                    class="fcp-btn-secondary"
                >
                    Regenerate
                </button>

                <button
                    type="button"
                    @click="copyGeneratedPassword()"
                    class="fcp-btn-secondary"
                >
                    <span x-text="copied ? '✓ Copied!' : 'Copy'"></span>
                </button>

                <button
                    type="button"
                    @click="useGeneratedPassword()"
                    class="fcp-btn-primary"
                >
                    Use this password
                </button>
            </div>
        </div>
    </div>
</x-filament-panels::page>
