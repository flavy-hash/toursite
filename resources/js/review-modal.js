/**
 * Review modal.
 *
 * The <dialog> element handles focus trapping, Esc and the backdrop, so this
 * only wires up the triggers, the filename readout, and reopening the dialog
 * when the server bounced the submission back with errors.
 */

export default function reviewModal() {
    const dialog = document.querySelector('#review-dialog');

    if (!dialog) {
        return;
    }

    const open = () => {
        if (!dialog.open) {
            dialog.showModal();
        }
    };

    document.querySelectorAll('[data-open-review]').forEach((trigger) => {
        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            open();
        });
    });

    dialog.querySelectorAll('[data-close-review]').forEach((button) => {
        button.addEventListener('click', () => dialog.close());
    });

    // Clicking the backdrop closes. The dialog itself fills its own box, so a
    // click landing on the element rather than the form came from outside it.
    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) {
            dialog.close();
        }
    });

    // Validation failed server-side: reopen so the errors are visible where
    // they were entered, rather than leaving the visitor on a silent page.
    if (dialog.hasAttribute('data-open-on-load')) {
        open();
    }

    // Show which file was picked, since the native input is hidden.
    const photo = dialog.querySelector('[data-review-photo]');
    const label = dialog.querySelector('[data-photo-label]');

    photo?.addEventListener('change', () => {
        const file = photo.files?.[0];

        // Built as nodes rather than innerHTML — a filename is user-supplied
        // text and has no business being parsed as markup.
        const strong = document.createElement('strong');
        strong.textContent = file ? file.name : 'Click to upload';

        label.replaceChildren(
            strong,
            document.createTextNode(file ? ' — click to choose another' : ' — JPG / PNG / WEBP up to 2 MB'),
        );
    });
}
