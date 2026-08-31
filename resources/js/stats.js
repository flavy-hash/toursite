/**
 * Hero stat counters.
 *
 * Each figure counts up from zero the first time it scrolls into view. The
 * final value is server-rendered, so without JS the numbers still read
 * correctly — this only replaces them once an animation is actually possible.
 */

const DURATION_MS = 1800;
const STAGGER_MS = 140;

const easeOutCubic = (t) => 1 - (1 - t) ** 3;

/** Builds the "1,200+" / "4.9" renderer for one counter. */
function rendererFor(el) {
    const decimals = Number(el.dataset.countDecimals ?? 0);
    const suffix = el.dataset.countSuffix ?? '';

    const format = new Intl.NumberFormat(undefined, {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
        useGrouping: 'countGroup' in el.dataset,
    });

    return (value) => {
        el.textContent = format.format(value) + suffix;
    };
}

function countUp(el, render, delay) {
    const target = Number(el.dataset.countTo);

    if (!Number.isFinite(target)) {
        return;
    }

    let startedAt = null;

    const frame = (now) => {
        startedAt ??= now;

        const progress = Math.min((now - startedAt) / DURATION_MS, 1);
        render(target * easeOutCubic(progress));

        if (progress < 1) {
            requestAnimationFrame(frame);
        }
    };

    window.setTimeout(() => requestAnimationFrame(frame), delay);
}

export default function statCounters() {
    const counters = [...document.querySelectorAll('[data-count-to]')];

    if (counters.length === 0 || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }

    // One formatter per counter, reused across every frame.
    const renderers = new Map(counters.map((el) => [el, rendererFor(el)]));

    // Zero them up front so the count doesn't visibly snap back when it starts.
    counters.forEach((el) => renderers.get(el)(0));

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }

                observer.unobserve(entry.target);
                countUp(entry.target, renderers.get(entry.target), counters.indexOf(entry.target) * STAGGER_MS);
            });
        },
        { threshold: 0.4 },
    );

    counters.forEach((el) => observer.observe(el));
}
