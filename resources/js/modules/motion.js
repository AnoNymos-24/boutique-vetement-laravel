import gsap from 'gsap';

const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

/**
 * Révèle les éléments marqués [data-reveal] au chargement, en cascade.
 * Le fade est court (150-300ms) et le déplacement vertical minime : on
 * confirme que le contenu vient d'arriver, on ne fait pas de spectacle.
 */
export function revealOnLoad(scope = document) {
    const targets = scope.querySelectorAll('[data-reveal]');
    if (!targets.length) return;

    if (prefersReducedMotion) {
        gsap.set(targets, { opacity: 1 });
        return;
    }

    gsap.to(targets, {
        opacity: 1,
        y: 0,
        duration: 0.35,
        ease: 'power2.out',
        stagger: 0.045,
        clearProps: 'transform',
    });
}

export function revealKpiCards(scope = document) {
    const cards = scope.querySelectorAll('.kpi-card');
    if (!cards.length) return;

    if (prefersReducedMotion) {
        gsap.set(cards, { opacity: 1 });
        return;
    }

    gsap.fromTo(
        cards,
        { opacity: 0, y: 10, scale: 0.98 },
        {
            opacity: 1,
            y: 0,
            scale: 1,
            duration: 0.4,
            ease: 'back.out(1.6)',
            stagger: 0.07,
        }
    );
}

export function revealTableRows(tableEl) {
    if (!tableEl) return;
    const rows = tableEl.querySelectorAll('tbody tr');
    if (!rows.length) return;

    if (prefersReducedMotion) {
        gsap.set(rows, { opacity: 1 });
        return;
    }

    gsap.to(rows, {
        opacity: 1,
        duration: 0.3,
        ease: 'power1.out',
        stagger: 0.025,
    });
}

export function showBulkBar(el) {
    if (prefersReducedMotion) {
        gsap.set(el, { opacity: 1, y: 0 });
        return;
    }
    gsap.to(el, { opacity: 1, y: 0, duration: 0.25, ease: 'power2.out' });
}

export function hideBulkBar(el, onComplete) {
    if (prefersReducedMotion) {
        onComplete?.();
        return;
    }
    gsap.to(el, { opacity: 0, y: -8, duration: 0.18, ease: 'power1.in', onComplete });
}

/**
 * Compte de 0 (ou valeur actuelle) jusqu'à la valeur cible affichée dans le
 * markup — donne du poids aux chiffres clés du dashboard sans être gadget.
 */
export function animateCounter(el) {
    const target = Number(el.dataset.countTo ?? el.textContent.replace(/[^\d.-]/g, ''));
    if (Number.isNaN(target)) return;

    const prefix = el.dataset.countPrefix ?? '';
    const suffix = el.dataset.countSuffix ?? '';
    const decimals = Number(el.dataset.countDecimals ?? 0);

    if (prefersReducedMotion) {
        el.textContent = `${prefix}${target.toFixed(decimals)}${suffix}`;
        return;
    }

    const counter = { value: 0 };
    gsap.to(counter, {
        value: target,
        duration: 1,
        ease: 'power2.out',
        onUpdate: () => {
            el.textContent = `${prefix}${counter.value.toLocaleString('fr-FR', {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals,
            })}${suffix}`;
        },
    });
}

export function initCounters(scope = document) {
    scope.querySelectorAll('[data-count-to]').forEach(animateCounter);
}
