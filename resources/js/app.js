import './bootstrap';

document.addEventListener('alpine:init', () => {
    Alpine.data('lightbox', (photos) => ({
        photos,
        open: false,
        index: 0,
        show(i) {
            this.index = i;
            this.open = true;
        },
        close() {
            this.open = false;
        },
        next() {
            if (this.photos.length === 0) return;
            this.index = (this.index + 1) % this.photos.length;
        },
        prev() {
            if (this.photos.length === 0) return;
            this.index = (this.index - 1 + this.photos.length) % this.photos.length;
        },
        get current() {
            return this.photos[this.index] ?? null;
        },
    }));
});

const revealElements = document.querySelectorAll('[data-reveal]');

if ('IntersectionObserver' in window && revealElements.length > 0) {
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.12 }
    );

    revealElements.forEach((el) => observer.observe(el));
} else {
    revealElements.forEach((el) => el.classList.add('is-visible'));
}