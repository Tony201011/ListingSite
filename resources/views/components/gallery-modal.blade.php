<!-- Simple Modal/Slider HTML for Gallery Images -->
<div id="galleryModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-80">
    <button id="closeGalleryModal" class="absolute top-4 right-4 text-white text-3xl font-bold">&times;</button>
    <div class="relative w-full max-w-2xl mx-auto flex flex-col items-center">
        <img id="galleryModalImg" src="" alt="Gallery Image" class="rounded-xl max-h-[80vh] object-contain">
        <div class="flex justify-between w-full mt-4">
            <button id="galleryPrevBtn" class="text-white text-2xl px-4 py-2">&#8592; Prev</button>
            <button id="galleryNextBtn" class="text-white text-2xl px-4 py-2">Next &#8594;</button>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const images = Array.from(document.querySelectorAll('.gallery-img-clickable'));
        const modal = document.getElementById('galleryModal');
        const modalImg = document.getElementById('galleryModalImg');
        const closeBtn = document.getElementById('closeGalleryModal');
        const prevBtn = document.getElementById('galleryPrevBtn');
        const nextBtn = document.getElementById('galleryNextBtn');
        let currentIdx = 0;

        function showModal(idx) {
            currentIdx = idx;
            modalImg.src = images[idx].src;
            modal.classList.remove('hidden');
        }
        function hideModal() {
            modal.classList.add('hidden');
        }
        function showPrev() {
            if (currentIdx > 0) showModal(currentIdx - 1);
        }
        function showNext() {
            if (currentIdx < images.length - 1) showModal(currentIdx + 1);
        }
        images.forEach((img, idx) => {
            img.addEventListener('click', () => showModal(idx));
        });
        closeBtn.addEventListener('click', hideModal);
        prevBtn.addEventListener('click', showPrev);
        nextBtn.addEventListener('click', showNext);
        modal.addEventListener('click', (e) => {
            if (e.target === modal) hideModal();
        });
        document.addEventListener('keydown', (e) => {
            if (modal.classList.contains('hidden')) return;
            if (e.key === 'ArrowLeft') showPrev();
            if (e.key === 'ArrowRight') showNext();
            if (e.key === 'Escape') hideModal();
        });
    });
</script>
<style>
#galleryModal { display: none; }
#galleryModal:not(.hidden) { display: flex; }
</style>
