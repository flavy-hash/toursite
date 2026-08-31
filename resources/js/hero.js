/**
 * Homepage hero carousel.
 *
 * The active slide's artwork fades in behind its copy panel, and every other
 * slide is offered as a thumbnail so the queue stays visible.
 */

const AUTOPLAY_MS = 7000;

function initHero(root) {
    const slides = [...root.querySelectorAll('[data-hero-slide]')];
    const panels = [...root.querySelectorAll('[data-hero-panel]')];
    const thumbs = [...root.querySelectorAll('[data-hero-thumb]')];
    const dots = [...root.querySelectorAll('[data-hero-dot]')];

    if (slides.length < 2) {
        return;
    }

    let index = Number(root.dataset.heroInitial ?? 0);
    let timer = null;

    const show = (next) => {
        index = (next + slides.length) % slides.length;

        slides.forEach((slide, i) => {
            slide.dataset.active = String(i === index);
        });

        panels.forEach((panel, i) => {
            panel.hidden = i !== index;

            // Restart the staggered entrance for the panel coming into view.
            if (i === index) {
                panel.querySelectorAll('.hero-enter').forEach((el) => {
                    el.style.animation = 'none';
                    void el.offsetWidth;
                    el.style.animation = '';
                });
            }
        });

        thumbs.forEach((thumb, i) => {
            thumb.hidden = i === index;
        });

        dots.forEach((dot, i) => {
            dot.setAttribute('aria-selected', String(i === index));
        });
    };

    const play = () => {
        stop();
        timer = window.setInterval(() => show(index + 1), AUTOPLAY_MS);
    };

    const stop = () => {
        if (timer !== null) {
            window.clearInterval(timer);
            timer = null;
        }
    };

    // Any manual choice restarts the clock rather than fighting it.
    const goTo = (next) => {
        show(next);
        play();
    };

    root.querySelector('[data-hero-prev]')?.addEventListener('click', () => goTo(index - 1));
    root.querySelector('[data-hero-next]')?.addEventListener('click', () => goTo(index + 1));

    [...thumbs, ...dots].forEach((el) => {
        const target = Number(el.dataset.heroThumb ?? el.dataset.heroDot);
        el.addEventListener('click', () => goTo(target));
    });

    root.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowLeft') {
            goTo(index - 1);
        } else if (event.key === 'ArrowRight') {
            goTo(index + 1);
        }
    });

    root.addEventListener('mouseenter', stop);
    root.addEventListener('mouseleave', play);
    root.addEventListener('focusin', stop);

    document.addEventListener('visibilitychange', () => {
        document.hidden ? stop() : play();
    });

    show(index);

    if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        play();
    }
}

export default function heroCarousel() {
    document.querySelectorAll('[data-hero]').forEach(initHero);
}
