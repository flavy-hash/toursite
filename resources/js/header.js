/**
 * Site header: transparent over the hero, solid once the page scrolls past it.
 * Drawer and sub-menu behaviour lives in nav.js.
 */

const SCROLL_THRESHOLD = 40;

const SOLID = ['bg-dark-brown/90', 'backdrop-blur-xl', 'border-b', 'border-sand/15', 'shadow-lg'];

export default function siteHeader() {
    const header = document.querySelector('[data-site-header]');

    if (!header) {
        return;
    }

    const sync = () => {
        const scrolled = window.scrollY > SCROLL_THRESHOLD;
        SOLID.forEach((cls) => header.classList.toggle(cls, scrolled));
    };

    window.addEventListener('scroll', sync, { passive: true });
    sync();
}
