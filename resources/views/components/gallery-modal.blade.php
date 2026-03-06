<!-- Gallery Modal/Slider with Alpine.js -->
<div x-data="galleryModal()" x-init="init()" x-show="open" @keydown.window.escape="close()" @keydown.window.arrow-right="next()" @keydown.window.arrow-left="prev()" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-90" x-cloak>
    <!-- Overlay for closing -->
    <div class="absolute inset-0" @click="close()"></div>
    <!-- Modal Content -->
    <div class="relative w-full max-w-3xl flex flex-col items-center justify-center h-[80vh] z-10">
        <!-- Close Button -->
        <button @click="close()" class="absolute -top-8 right-0 text-white text-4xl font-bold hover:text-pink-400 transition focus:outline-none" aria-label="Close gallery">&times;</button>
        <div class="flex items-center w-full h-full">
            <!-- Left Arrow -->
            <button @click="prev()" :disabled="currentIdx === 0" class="flex-shrink-0 text-white text-4xl px-2 py-2 bg-black bg-opacity-30 hover:bg-pink-500 hover:bg-opacity-80 rounded-full transition focus:outline-none disabled:opacity-30 disabled:cursor-not-allowed" aria-label="Previous image">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-10 h-10"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
            </button>
            <!-- Image -->
            <div class="flex-1 flex items-center justify-center">
                <img :src="images[currentIdx]" alt="Gallery Image" class="rounded-2xl max-h-[70vh] max-w-full shadow-2xl border-4 border-white object-contain mx-4">
            </div>
            <!-- Right Arrow -->
            <button @click="next()" :disabled="currentIdx === images.length - 1" class="flex-shrink-0 text-white text-4xl px-2 py-2 bg-black bg-opacity-30 hover:bg-pink-500 hover:bg-opacity-80 rounded-full transition focus:outline-none disabled:opacity-30 disabled:cursor-not-allowed" aria-label="Next image">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-10 h-10"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            </button>
        </div>
        <!-- Image Counter -->
        <div class="absolute bottom-4 left-1/2 -translate-x-1/2 text-white bg-black bg-opacity-40 rounded-full px-4 py-1 text-sm font-semibold tracking-wide select-none">
            <span x-text="currentIdx + 1"></span> / <span x-text="images.length"></span>
        </div>
    </div>
</div>
<script>
function galleryModal() {
    return {
        open: false,
        images: [],
        currentIdx: 0,
        init() {
            this.images = Array.from(document.querySelectorAll('.gallery-img-clickable')).map(img => img.src);
            document.querySelectorAll('.gallery-img-clickable').forEach((img, idx) => {
                img.addEventListener('click', () => {
                    this.open = true;
                    this.currentIdx = idx;
                });
            });
        },
        close() { this.open = false; },
        prev() { if (this.currentIdx > 0) this.currentIdx--; },
        next() { if (this.currentIdx < this.images.length - 1) this.currentIdx++; }
    }
}
</script>
<style>
[x-cloak] { display: none !important; }
</style>
