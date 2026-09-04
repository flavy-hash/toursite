/**
 * Photo viewer.
 *
 * Any <a data-lbx="group"> opens every link sharing that group, so the arrows
 * walk one property's photos. Triggers are ordinary links to the full image,
 * so without JavaScript the photo still opens in a new tab.
 */
export default function lightbox() {
    const box = document.querySelector('#lbx');

    if (!box) {
        return;
    }

    const img = box.querySelector('img');
    const count = box.querySelector('.lbx-count');
    const close = box.querySelector('.lbx-close');
    const prev = box.querySelector('.lbx-prev');
    const next = box.querySelector('.lbx-next');

    let shots = [];
    let at = 0;
    let opener = null;

    const show = (index) => {
        at = (index + shots.length) % shots.length;

        img.src = shots[at].src;
        img.alt = shots[at].alt;

        const many = shots.length > 1;
        prev.hidden = next.hidden = !many;
        count.textContent = many ? `${at + 1} / ${shots.length}` : '';
    };

    const open = (list, index, trigger) => {
        shots = list;
        opener = trigger;

        box.hidden = false;
        // Stop the page behind scrolling while the viewer is up.
        document.body.style.overflow = 'hidden';

        show(index);
        close.focus();
    };

    const dismiss = () => {
        box.hidden = true;
        document.body.style.overflow = '';
        img.removeAttribute('src');

        // Send focus back where it came from rather than to the top of the page.
        opener?.focus();
        opener = null;
    };

    // Delegated, so photos rendered after load still work.
    document.addEventListener('click', (event) => {
        const link = event.target.closest('a[data-lbx]');

        if (link) {
            event.preventDefault();

            const group = [...document.querySelectorAll(`a[data-lbx="${link.dataset.lbx}"]`)];

            open(
                group.map((a) => ({ src: a.href, alt: a.dataset.caption || '' })),
                group.indexOf(link),
                link,
            );

            return;
        }

        // A click landing on the backdrop itself came from outside the photo.
        if (event.target === box) {
            dismiss();
        }
    });

    close.addEventListener('click', dismiss);
    prev.addEventListener('click', () => show(at - 1));
    next.addEventListener('click', () => show(at + 1));

    document.addEventListener('keydown', (event) => {
        if (box.hidden) {
            return;
        }

        if (event.key === 'Escape') {
            dismiss();
        } else if (event.key === 'ArrowLeft') {
            show(at - 1);
        } else if (event.key === 'ArrowRight') {
            show(at + 1);
        }
    });
}
