@push('styles')
    <style>
        [data-recaptcha-container] {
            width: 304px;
            max-width: 100%;
        }
    </style>
@endpush

@push('scripts')
    <script>
        (function () {
            const BASE_WIDTH = 304;
            const BASE_HEIGHT = 78;
            let resizeScheduled = false;

            const resizeRecaptcha = () => {
                document.querySelectorAll('[data-recaptcha-container]').forEach((container) => {
                    const widget = container.querySelector('.g-recaptcha');
                    const hostWidth = container.parentElement?.clientWidth ?? container.clientWidth;
                    const availableWidth = Math.min(hostWidth, BASE_WIDTH);

                    if (!widget || availableWidth <= 0) {
                        return;
                    }

                    const scale = availableWidth / BASE_WIDTH;
                    container.style.width = `${availableWidth}px`;
                    container.style.height = `${Math.ceil(BASE_HEIGHT * scale)}px`;
                    widget.style.transform = `scale(${scale})`;
                    widget.style.transformOrigin = '0 0';
                });
            };

            const scheduleResizeRecaptcha = () => {
                if (resizeScheduled) {
                    return;
                }

                resizeScheduled = true;
                window.requestAnimationFrame(() => {
                    resizeScheduled = false;
                    resizeRecaptcha();
                });
            };

            window.addEventListener('load', scheduleResizeRecaptcha);
            window.addEventListener('resize', scheduleResizeRecaptcha);
        })();
    </script>
@endpush
