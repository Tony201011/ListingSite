@props([
    'exitUrl' => config('services.age_verification.exit_url'),
    'storageKey' => 'ageVerification',
    'durationDays' => 30,
])

<div
    x-data="{
        show: false,
        storageKey: @js($storageKey),
        durationMs: {{ (int) $durationDays }} * 24 * 60 * 60 * 1000,
        exitUrl: @js($exitUrl),
        init() {
            this.show = this.requiresVerification();
            this.applyScrollLock(this.show);
            this.$watch('show', (value) => this.applyScrollLock(value));
        },
        requiresVerification() {
            try {
                const rawValue = window.localStorage.getItem(this.storageKey);
                if (!rawValue) {
                    return true;
                }

                const payload = JSON.parse(rawValue);
                const verifiedAt = Number(payload?.verifiedAt);

                if (! Number.isFinite(verifiedAt)) {
                    return true;
                }

                return (Date.now() - verifiedAt) > this.durationMs;
            } catch (error) {
                return true;
            }
        },
        applyScrollLock(locked) {
            document.documentElement.style.overflow = locked ? 'hidden' : '';
            document.body.style.overflow = locked ? 'hidden' : '';
        },
        enter() {
            try {
                window.localStorage.setItem(this.storageKey, JSON.stringify({
                    verifiedAt: Date.now(),
                }));
            } catch (error) {}

            this.show = false;
        },
        exit() {
            window.location.replace(this.exitUrl);
        }
    }"
    x-show="show"
    x-cloak
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-250"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-[2147483647] flex items-center justify-center bg-black/90 px-4 py-6"
    role="dialog"
    aria-modal="true"
    aria-labelledby="age-verification-title"
>
    <div
        class="w-full max-w-xl rounded-2xl border border-gray-700 bg-[#1a1a1a] px-6 py-8 text-center shadow-2xl sm:px-10 sm:py-10"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-250"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
    >
        <div class="mx-auto mb-5 flex h-20 w-20 items-center justify-center rounded-full border-2 border-pink-500 bg-pink-500/20 text-3xl font-bold text-pink-400">
            18+
        </div>

        <h2 id="age-verification-title" class="text-3xl font-bold tracking-wide text-white sm:text-4xl">WARNING</h2>
        <p class="mt-2 text-base font-semibold text-pink-400 sm:text-lg">This site is for adults only!</p>
        <p class="mt-4 text-sm leading-relaxed text-gray-300 sm:text-base">
            This website contains adult content and is only accessible to visitors who are legally 18 years of age or older.
            By entering, you confirm that you meet this legal requirement in your location.
        </p>

        <div class="mt-8 flex flex-col items-center gap-4">
            <button
                type="button"
                @click="enter"
                class="w-full rounded-xl bg-pink-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-pink-400 focus:ring-offset-2 focus:ring-offset-[#1a1a1a] sm:text-base"
            >
                I am 18+ years old – ENTER
            </button>

            <button
                type="button"
                @click="exit"
                class="text-sm font-semibold text-gray-300 underline transition hover:text-white sm:text-base"
            >
                Exit
            </button>
        </div>
    </div>
</div>
