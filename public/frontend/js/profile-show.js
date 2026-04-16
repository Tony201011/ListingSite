document.addEventListener('DOMContentLoaded', function () {
    // Smooth scroll for anchor links with .smooth-scroll class
    document.querySelectorAll('a.smooth-scroll[href^="#"]').forEach(function (anchor) {
        anchor.addEventListener('click', function (e) {
            const targetId = this.getAttribute('href').slice(1);
            const target = document.getElementById(targetId);
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // Lazy-load fade-in via Intersection Observer
    const lazyImages = document.querySelectorAll('img.lazy-img');
    if (lazyImages.length && 'IntersectionObserver' in window) {
        const imgObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                const img = entry.target;
                if (img.complete) {
                    img.classList.add('is-loaded');
                } else {
                    img.addEventListener('load', function () {
                        img.classList.add('is-loaded');
                    }, { once: true });
                    img.addEventListener('error', function () {
                        img.classList.add('is-loaded');
                    }, { once: true });
                }
                imgObserver.unobserve(img);
            });
        }, { rootMargin: '150px' });

        lazyImages.forEach(function (img) { imgObserver.observe(img); });
    } else {
        // Fallback: show all images immediately
        lazyImages.forEach(function (img) { img.classList.add('is-loaded'); });
    }

});

document.addEventListener('alpine:init', () => {
    Alpine.store('videoControl', {
        pauseOthers(current) {
            document.querySelectorAll('video').forEach(video => {
                if (video !== current) video.pause();
            });
        }
    });

    Alpine.directive('pauseothers', (el) => {
        el.addEventListener('play', () => {
            Alpine.store('videoControl').pauseOthers(el);
        });
    });
});

function favouriteBookmark(config) {
    return {
        favourites: config.favourites || [],

        isFavourite(slug) {
            return this.favourites.includes(slug);
        },

        async toggleFavourite(slug) {
            const res = await fetch('/favourite/' + encodeURIComponent(slug), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            });
            if (!res.ok) return;
            const data = await res.json();
            if (data.active) {
                if (!this.favourites.includes(slug)) this.favourites.push(slug);
            } else {
                this.favourites = this.favourites.filter(s => s !== slug);
            }
        },
    };
}

function submitReport(event) {
    event.preventDefault();
    const cfg = window.__profileShowConfig || {};
    const form = document.getElementById('report-form');
    const btn = document.getElementById('report-submit-btn');
    const errorEl = document.getElementById('report-error');
    const successEl = document.getElementById('report-success');

    btn.disabled = true;
    btn.textContent = 'Submitting...';
    errorEl.classList.add('hidden');
    successEl.classList.add('hidden');

    const formData = new FormData(form);

    fetch(cfg.reportUrl, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': formData.get('_token'),
            'Accept': 'application/json',
        },
        body: formData,
    })
    .then(async response => {
        const data = await response.json();
        if (response.ok) {
            successEl.classList.remove('hidden');
            form.reset();
            form.querySelector('[name="provider_profile_id"]').value = cfg.profileId;
            setTimeout(() => {
                document.getElementById('report-modal').classList.add('hidden');
                successEl.classList.add('hidden');
            }, 3000);
        } else {
            const messages = data.errors
                ? Object.values(data.errors).flat().join(' ')
                : (data.message || 'An error occurred. Please try again.');
            errorEl.textContent = messages;
            errorEl.classList.remove('hidden');
        }
    })
    .catch(() => {
        errorEl.textContent = 'A network error occurred. Please try again.';
        errorEl.classList.remove('hidden');
    })
    .finally(() => {
        btn.disabled = false;
        btn.textContent = 'Submit Report';
    });
}
