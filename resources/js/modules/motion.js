import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

/**
 * Révèle les éléments marqués [data-reveal] au chargement, en cascade.
 * Utilisé par le back-office (admin) — ne pas modifier son comportement.
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

/**
 * Boutique uniquement : révèle chaque [data-reveal] à son entrée dans le viewport
 * (pas au chargement) — le contenu sous la ligne de flottaison n'est donc plus déjà
 * visible avant que la cliente n'y arrive en scrollant. Fade court (150-300ms),
 * déplacement vertical minime : on confirme que le contenu vient d'arriver, pas un spectacle.
 */
export function initReveals(scope = document) {
    const targets = scope.querySelectorAll('[data-reveal]');
    if (!targets.length) return;

    if (prefersReducedMotion) {
        gsap.set(targets, { opacity: 1 });
        return;
    }

    targets.forEach((el) => {
        gsap.to(el, {
            opacity: 1,
            y: 0,
            duration: 0.35,
            ease: 'power2.out',
            clearProps: 'transform',
            scrollTrigger: {
                trigger: el,
                start: 'top 90%',
                toggleActions: 'play none none none',
                once: true,
            },
        });
    });
}

/**
 * Reveal en cascade pour une grille (produits, catégories, avis) : les enfants
 * apparaissent en vague quand le conteneur entre dans le viewport, au lieu
 * d'un fade indépendant par carte sans cohérence de groupe. Reprend le spec
 * "Stagger List" documenté dans design-system/boutique-front-office/MASTER.md.
 */
export function revealGrid(container) {
    if (!container) return;
    const items = container.children;
    if (!items.length) return;

    if (prefersReducedMotion) {
        gsap.set(items, { opacity: 1 });
        return;
    }

    gsap.from(items, {
        opacity: 0,
        scale: 0.92,
        y: 16,
        duration: 0.4,
        ease: 'back.out(1.4)',
        stagger: { each: 0.06, from: 'start', grid: 'auto' },
        scrollTrigger: {
            trigger: container,
            start: 'top 85%',
        },
    });
}

/**
 * Petit moment de satisfaction pour la confirmation de commande : rebond du
 * badge de succès puis fade des éléments enfants (numéro de commande, CTA).
 */
export function celebrate(el) {
    if (!el) return;
    const badge = el.querySelector('[data-celebrate-badge]');
    const rest = el.querySelectorAll('[data-celebrate-item]');

    if (prefersReducedMotion) {
        gsap.set([badge, ...rest], { opacity: 1, scale: 1 });
        return;
    }

    const tl = gsap.timeline();
    if (badge) {
        tl.fromTo(badge, { opacity: 0, scale: 0.5 }, { opacity: 1, scale: 1, duration: 0.5, ease: 'back.out(1.8)' });
    }
    if (rest.length) {
        tl.to(rest, { opacity: 1, y: 0, duration: 0.35, stagger: 0.06, ease: 'power2.out' }, badge ? '-=0.15' : 0);
    }
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
