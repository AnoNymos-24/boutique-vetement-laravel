import './bootstrap';
import Alpine from 'alpinejs';
import { initReveals, revealGrid, initCounters, celebrate } from './modules/motion';

Alpine.data('mobileFilters', () => ({
    open: false,
}));

Alpine.data('mobileNav', () => ({
    open: false,
}));

Alpine.data('bannerCarousel', (count = 1) => ({
    active: 0,
    count,
    timer: null,

    init() {
        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (this.count > 1 && !reduceMotion) this.start();
    },

    start() {
        this.timer = setInterval(() => {
            this.active = (this.active + 1) % this.count;
        }, 6000);
    },

    go(index) {
        this.active = index;
        clearInterval(this.timer);
        this.start();
    },
}));

Alpine.data('accordion', () => ({
    open: false,
}));

/**
 * Sélecteur de variante sur la fiche produit : construit la liste des tailles/couleurs
 * disponibles à partir des vraies variantes en base (avec leur stock), et détermine
 * la variante exacte correspondant à la combinaison choisie par la cliente.
 */
Alpine.data('variantSelector', (variants = []) => ({
    variants,
    size: null,
    color: null,
    qty: 1,

    init() {
        this.$watch('size', () => { this.qty = 1; });
        this.$watch('color', () => { this.qty = 1; });
        const sizes = this.availableSizes;
        const colors = this.availableColors;
        if (sizes.length === 1) this.size = sizes[0];
        if (colors.length === 1) this.color = colors[0];
        if (sizes.length === 0 && colors.length === 0 && this.variants.length === 1) {
            this.selectedVariant = this.variants[0];
        }
    },

    get availableSizes() {
        return [...new Set(this.variants.filter((v) => v.size).map((v) => v.size))];
    },

    get availableColors() {
        return [...new Set(this.variants.filter((v) => v.color).map((v) => v.color))];
    },

    stockForSize(size) {
        return this.variants
            .filter((v) => v.size === size && (this.color === null || v.color === this.color))
            .reduce((sum, v) => sum + v.stock, 0);
    },

    stockForColor(color) {
        return this.variants
            .filter((v) => v.color === color && (this.size === null || v.size === this.size))
            .reduce((sum, v) => sum + v.stock, 0);
    },

    selectSize(size) {
        if (this.stockForSize(size) === 0) return;
        this.size = size;
    },

    selectColor(color) {
        if (this.stockForColor(color) === 0) return;
        this.color = color;
    },

    get selectedVariant() {
        return this.variants.find((v) =>
            (v.size ?? null) === this.size && (v.color ?? null) === this.color
        ) ?? null;
    },

    get canAddToCart() {
        const v = this.selectedVariant;
        return v !== null && v.stock > 0;
    },

    get stockLabel() {
        const v = this.selectedVariant;
        if (!v) return 'Choisissez une option';
        if (v.stock === 0) return 'Rupture de stock';
        if (v.stock <= 5) return `Plus que ${v.stock} en stock`;
        return 'En stock';
    },

    incrementQty() {
        const max = this.selectedVariant?.stock ?? 1;
        if (this.qty < max) this.qty++;
    },

    decrementQty() {
        if (this.qty > 1) this.qty--;
    },
}));

window.Alpine = Alpine;
Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    initReveals();
    initCounters();
    document.querySelectorAll('.product-grid, .wishlist-grid, .shop-category-grid, .shop-review-grid').forEach(revealGrid);
    celebrate(document.getElementById('order-confirmation-celebrate'));
});
