/**
 * Site header: transparent over the hero, solid once the page scrolls past it.
 */

const SCROLL_THRESHOLD = 40;

export default function siteHeader() {
    const header = document.querySelector('[data-site-header]');

    if (!header) {
        return;
    }

    const solid = ['bg-dark-brown/90', 'backdrop-blur-xl', 'border-b', 'border-sand/15'];

    const sync = () => {
        header.classList.toggle('shadow-lg', window.scrollY > SCROLL_THRESHOLD);
        solid.forEach((cls) => header.classList.toggle(cls, window.scrollY > SCROLL_THRESHOLD));
    };

    window.addEventListener('scroll', sync, { passive: true });
    sync();

    const toggle = header.querySelector('[data-menu-toggle]');
    const menu = header.querySelector('#mobile-menu');

    toggle?.addEventListener('click', () => {
        const open = toggle.getAttribute('aria-expanded') === 'true';
        toggle.setAttribute('aria-expanded', String(!open));
        menu.hidden = open;
    });
}
