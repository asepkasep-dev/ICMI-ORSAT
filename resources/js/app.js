import './bootstrap';
import 'trix';
import 'trix/dist/trix.css';

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

    Alpine.data('trixEditor', () => ({
        timer: null,
        init() {
            const editor = this.$el.querySelector('trix-editor');
            if (!editor) return;

            editor.addEventListener('trix-file-accept', (event) => event.preventDefault());

            editor.addEventListener('trix-change', () => {
                clearTimeout(this.timer);
                this.timer = setTimeout(() => {
                    this.$wire.set('content', editor.innerHTML, false);
                }, 400);
            });
        },
        destroy() {
            clearTimeout(this.timer);
        },
    }));

    Alpine.data('toastHub', () => ({
        toasts: [],
        nextId: 1,
        init() {
            window.addEventListener('toast', (event) => {
                this.push(event.detail?.message ?? event.detail?.[0]?.message, event.detail?.type ?? event.detail?.[0]?.type);
            });
        },
        push(message, type = 'success') {
            if (!message) return;
            const id = this.nextId++;
            this.toasts.push({ id, message, type });
            setTimeout(() => this.dismiss(id), 5200);
        },
        dismiss(id) {
            this.toasts = this.toasts.filter((t) => t.id !== id);
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