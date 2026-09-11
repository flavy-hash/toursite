/**
 * "Ask a question" modal on /faq.
 *
 * The <dialog> element handles focus trapping, Esc and the backdrop, so this
 * only wires up the triggers and reopens the dialog when the server bounced
 * the submission back with errors.
 */
export default function faqModal() {
    const dialog = document.querySelector('#faq-dialog');

    if (!dialog) {
        return;
    }

    const open = () => {
        if (!dialog.open) {
            dialog.showModal();
        }
    };

    document.querySelectorAll('[data-open-faq]').forEach((trigger) => {
        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            open();
        });
    });

    dialog.querySelectorAll('[data-close-faq]').forEach((button) => {
        button.addEventListener('click', () => dialog.close());
    });

    // The dialog fills its own box, so a click landing on the element rather
    // than the form came from the backdrop.
    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) {
            dialog.close();
        }
    });

    // Validation failed server-side: reopen so the errors are visible where
    // they were typed, rather than leaving the visitor on a silent page.
    if (dialog.hasAttribute('data-open-on-load')) {
        open();
    }
}
