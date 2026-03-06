<!-- Gallery Modal/Slider with Alpine.js -->
<div x-data="galleryModal()" x-init="init()" x-show="open" @keydown.window.escape="close()" @keydown.window.arrow-right="next()" @keydown.window.arrow-left="prev()" class="fixed inset-0 z-50 items-center justify-center bg-black bg-opacity-90" x-cloak style="display: none;">
    <button @click="close()" class="absolute top-6 right-8 text-white text-4xl font-bold hover:text-pink-400 transition">&times;</button>
    <div class="relative w-full max-w-3xl flex items-center justify-center h-[80vh]">
        <!-- Left Arrow -->
        <button @click="prev()" :disabled="currentIdx === 0" class="absolute left-0 top-1/2 -translate-y-1/2 text-white text-4xl px-4 py-2 bg-black bg-opacity-30 hover:bg-pink-500 hover:bg-opacity-80 rounded-full transition focus:outline-none">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-10 h-10"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
        </button>
        <!-- Image -->
        <img :src="images[currentIdx]" alt="Gallery Image" class="rounded-2xl max-h-[70vh] max-w-full shadow-2xl border-4 border-white object-contain mx-20">
        <!-- Right Arrow -->
        <button @click="next()" :disabled="currentIdx === images.length - 1" class="absolute right-0 top-1/2 -translate-y-1/2 text-white text-4xl px-4 py-2 bg-black bg-opacity-30 hover:bg-pink-500 hover:bg-opacity-80 rounded-full transition focus:outline-none">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-10 h-10"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
        </button>
    </div>
</div>
<script>
function galleryModal() {
    return {
        open: false,
        images: [],
        currentIdx: 0,
        init() {
            // Collect all gallery images on the page
            this.images = Array.from(document.querySelectorAll('.gallery-img-clickable')).map(img => img.src);
            // Add click listeners to open modal
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
