document.addEventListener('alpine:init', () => {
    Alpine.data('profileSettingPage', (config = {}) => ({
        bookingOpen: Boolean(config.bookingOpen),

        sliderOpen: false,
        sliderIndex: 0,

        videoOpen: false,
        videoIndex: 0,

        photos: Array.isArray(config.photos)
            ? config.photos.filter((photo) => photo.thumbnail_url || photo.image_url)
            : [],

        videos: Array.isArray(config.videos)
            ? config.videos.filter((video) => video.video_url)
            : [],

        get visiblePhotos() {
            return this.photos.slice(0, 2);
        },

        get remainingPhotoCount() {
            return this.photos.length > 2 ? this.photos.length - 2 : 0;
        },

        get visibleVideos() {
            return this.videos.slice(0, 2);
        },

        get remainingVideoCount() {
            return this.videos.length > 2 ? this.videos.length - 2 : 0;
        },

        openSlider(index = 0) {
            if (!this.photos.length) {
                return;
            }

            this.sliderIndex = index;
            this.sliderOpen = true;
            document.body.classList.add('overflow-hidden');
        },

        closeSlider() {
            this.sliderOpen = false;

            if (!this.videoOpen) {
                document.body.classList.remove('overflow-hidden');
            }
        },

        nextSlide() {
            if (this.photos.length <= 1) {
                return;
            }

            this.sliderIndex = (this.sliderIndex + 1) % this.photos.length;
        },

        prevSlide() {
            if (this.photos.length <= 1) {
                return;
            }

            this.sliderIndex = (this.sliderIndex - 1 + this.photos.length) % this.photos.length;
        },

        openVideo(index = 0) {
            if (!this.videos.length) {
                return;
            }

            this.videoIndex = index;
            this.videoOpen = true;
            document.body.classList.add('overflow-hidden');
        },

        closeVideo() {
            this.videoOpen = false;

            if (!this.sliderOpen) {
                document.body.classList.remove('overflow-hidden');
            }
        },

        nextVideo() {
            if (this.videos.length <= 1) {
                return;
            }

            this.videoIndex = (this.videoIndex + 1) % this.videos.length;
        },

        prevVideo() {
            if (this.videos.length <= 1) {
                return;
            }

            this.videoIndex = (this.videoIndex - 1 + this.videos.length) % this.videos.length;
        }
    }));
});
