import gsap from 'gsap';

let stack = null;

function getStack() {
    if (!stack) {
        stack = document.createElement('div');
        stack.className = 'toast-stack';
        document.body.appendChild(stack);
    }
    return stack;
}

/**
 * Feedback de confirmation visible après chaque action (règle UX
 * "pas de succès silencieux" — voir MASTER.md > Règles UX spécifiques).
 */
export function toast(message, { type = 'success', duration = 3200 } = {}) {
    const el = document.createElement('div');
    el.className = `toast${type === 'error' ? ' toast--error' : ''}`;
    el.innerHTML = `
        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            ${type === 'error'
                ? '<circle cx="12" cy="12" r="9"/><path d="M15 9l-6 6M9 9l6 6"/>'
                : '<path d="M20 6L9 17l-5-5"/>'}
        </svg>
        <span>${message}</span>
    `;

    getStack().appendChild(el);
    gsap.fromTo(el, { opacity: 0, x: 24 }, { opacity: 1, x: 0, duration: 0.3, ease: 'power2.out' });

    setTimeout(() => {
        gsap.to(el, {
            opacity: 0,
            x: 24,
            duration: 0.25,
            ease: 'power1.in',
            onComplete: () => el.remove(),
        });
    }, duration);
}

window.toast = toast;
