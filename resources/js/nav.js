/**
 * Header navigation.
 *
 * On desktop the mega panels are pure CSS (:hover / :focus-within) — nothing
 * here runs. Below 1024px the same markup becomes a drawer whose sub-menus
 * open on tap, which is what this wires up.
 */

const DRAWER_QUERY = '(max-width: 1023px)';

export default function siteNav() {
    const header = document.querySelector('[data-site-header]');
    const toggle = header?.querySelector('[data-menu-toggle]');
    const menu = header?.querySelector('[data-nav-menu]');

    if (!header || !toggle || !menu) {
        return;
    }

    const drawer = window.matchMedia(DRAWER_QUERY);

    /* Shut the drawer and collapse every open sub-menu, back to a clean state. */
    const close = () => {
        menu.classList.remove('is-open');
        toggle.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
        menu.querySelectorAll('.nav-item.is-open').forEach((item) => item.classList.remove('is-open'));
    };

    toggle.addEventListener('click', () => {
        const open = menu.classList.toggle('is-open');
        toggle.classList.toggle('is-open', open);
        toggle.setAttribute('aria-expanded', String(open));
    });

    // In the drawer a parent tap expands its section instead of navigating.
    // The rail links inside still go where they point.
    menu.querySelectorAll('.nav-item.has-mega').forEach((item) => {
        const link = item.querySelector(':scope > .nav-link');

        link?.addEventListener('click', (event) => {
            if (!drawer.matches) {
                return;
            }

            event.preventDefault();
            item.classList.toggle('is-open');
        });
    });

    // Crossing the breakpoint — a resize or a phone rotation — resets the
    // drawer. Without this it stays stuck open when the layout flips back to
    // desktop. matchMedia fires only on the crossing, so scrolling costs nothing.
    drawer.addEventListener('change', close);

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            close();
        }
    });

    // A tap outside the header closes the drawer.
    document.addEventListener('click', (event) => {
        if (drawer.matches && menu.classList.contains('is-open') && !header.contains(event.target)) {
            close();
        }
    });
}
