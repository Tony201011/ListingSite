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

            const resizeRecaptcha = () => {
                document.querySelectorAll('[data-recaptcha-container]').forEach((container) => {
                    const widget = container.querySelector('.g-recaptcha');
                    const hostWidth = container.parentElement?.clientWidth ?? BASE_WIDTH;
                    const availableWidth = Math.min(hostWidth, BASE_WIDTH);
                    const scale = availableWidth / BASE_WIDTH;

                    if (!widget || scale <= 0) {
                        return;
                    }

                    container.style.width = `${availableWidth}px`;
                    container.style.height = `${Math.ceil(BASE_HEIGHT * scale)}px`;
                    widget.style.transform = `scale(${scale})`;
                    widget.style.transformOrigin = '0 0';
                });
            };

            window.addEventListener('load', resizeRecaptcha);
            window.addEventListener('resize', resizeRecaptcha);
        })();
    </script>
@endpush
