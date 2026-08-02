import './bootstrap';
import Alpine from 'alpinejs';
import './modules/toast';
import { revealOnLoad, revealKpiCards, revealTableRows, initCounters, showBulkBar, hideBulkBar } from './modules/motion';

Alpine.data('sidebar', () => ({
    collapsed: localStorage.getItem('sidebar-collapsed') === '1',
    mobileOpen: false,

    toggleCollapsed() {
        this.collapsed = !this.collapsed;
        localStorage.setItem('sidebar-collapsed', this.collapsed ? '1' : '0');
    },

    toggleMobile() {
        this.mobileOpen = !this.mobileOpen;
    },
}));

Alpine.data('dropdown', () => ({
    open: false,
    toggle() { this.open = !this.open; },
    close() { this.open = false; },
}));

Alpine.data('bulkSelect', (totalIds = []) => ({
    selected: [],
    all: totalIds,

    get count() { return this.selected.length; },
    get hasSelection() { return this.selected.length > 0; },
    get allSelected() { return this.all.length > 0 && this.selected.length === this.all.length; },

    toggleAll() {
        this.selected = this.allSelected ? [] : [...this.all];
    },

    clear() { this.selected = []; },

    init() {
        this.$watch('selected', (value) => {
            const bar = this.$refs.bulkBar;
            if (!bar) return;
            if (value.length > 0) {
                bar.style.display = 'flex';
                showBulkBar(bar);
            } else {
                hideBulkBar(bar, () => { bar.style.display = 'none'; });
            }
        });
    },
}));

Alpine.data('imageUploader', () => ({
    previews: [],

    onChange(event) {
        this.previews.forEach((src) => URL.revokeObjectURL(src));
        this.previews = Array.from(event.target.files).map((file) => URL.createObjectURL(file));
    },
}));

Alpine.data('variantEditor', () => ({
    variants: [{ size: '', color: '', sku: '', stock_quantity: 0, low_stock_threshold: 5 }],

    add() {
        this.variants.push({ size: '', color: '', sku: '', stock_quantity: 0, low_stock_threshold: 5 });
    },

    remove(index) {
        if (this.variants.length > 1) this.variants.splice(index, 1);
    },
}));

Alpine.data('stockAdjuster', () => ({
    productId: null,
    productName: '',
    currentStock: 0,
    type: 'entree',

    get quantityLabel() {
        return {
            entree: 'Quantité entrante',
            sortie: 'Quantité sortante',
            ajustement: 'Nouveau stock total',
        }[this.type];
    },

    open(id, name, stock) {
        this.productId = id;
        this.productName = name;
        this.currentStock = stock;
        this.type = 'entree';
        this.$dispatch('open-modal', 'stock-adjust');
    },
}));

Alpine.data('posRegister', (products = [], checkPromoUrlBase = '') => ({
    products,
    search: '',
    cart: [],
    paymentMethod: 'especes',
    submitting: false,
    pickingProduct: null,

    promoInput: '',
    promoChecking: false,
    promoError: '',
    appliedPromo: null, // { code, discount }

    get filteredProducts() {
        if (!this.search.trim()) return this.products;
        const q = this.search.trim().toLowerCase();
        return this.products.filter((p) => p.name.toLowerCase().includes(q)
            || p.variants.some((v) => v.sku.toLowerCase().includes(q)));
    },

    totalStock(product) {
        return product.variants.reduce((sum, v) => sum + v.stock, 0);
    },

    pickVariant(product) {
        if (product.variants.length === 1) {
            this.chooseVariant(product.variants[0], product);
            return;
        }
        this.pickingProduct = product;
        this.$dispatch('open-modal', 'variant-pick');
    },

    chooseVariant(variant, product = null) {
        product = product ?? this.pickingProduct;
        if (variant.stock === 0) return;
        this.addToCart(product, variant);
        this.pickingProduct = null;
        this.$dispatch('close-modal', 'variant-pick');
    },

    get subtotal() {
        return this.cart.reduce((sum, item) => sum + item.price * item.qty, 0);
    },

    get discount() {
        return this.appliedPromo ? Math.min(this.appliedPromo.discount, this.subtotal) : 0;
    },

    get total() {
        return this.subtotal - this.discount;
    },

    get subtotalFormatted() {
        return this.priceFormatted(this.subtotal);
    },

    get totalFormatted() {
        return this.priceFormatted(this.total);
    },

    priceFormatted(cents) {
        return (cents / 100).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' €';
    },

    addToCart(product, variant) {
        const existing = this.cart.find((i) => i.id === variant.id);
        if (existing) {
            if (existing.qty < variant.stock) existing.qty++;
        } else {
            this.cart.push({
                id: variant.id,
                name: product.name,
                variantLabel: variant.label,
                price: product.price,
                stock: variant.stock,
                qty: 1,
            });
        }
        this.revalidatePromo();
    },

    increment(item) {
        if (item.qty < item.stock) item.qty++;
        this.revalidatePromo();
    },

    decrement(item) {
        item.qty--;
        if (item.qty <= 0) this.removeFromCart(item.id);
        this.revalidatePromo();
    },

    removeFromCart(id) {
        this.cart = this.cart.filter((i) => i.id !== id);
        this.revalidatePromo();
    },

    async applyPromo() {
        if (!this.promoInput.trim()) return;

        this.promoChecking = true;
        this.promoError = '';

        try {
            const res = await fetch(`${checkPromoUrlBase}?code=${encodeURIComponent(this.promoInput.trim())}`, {
                headers: { Accept: 'application/json' },
            });
            const data = await res.json();

            if (!data.valid) {
                this.promoError = 'Code invalide ou expiré.';
                this.appliedPromo = null;
                return;
            }

            if (data.min_order_amount && this.subtotal < data.min_order_amount) {
                this.promoError = `Panier minimum : ${this.priceFormatted(data.min_order_amount)}.`;
                this.appliedPromo = null;
                return;
            }

            const discount = data.type === 'pourcentage'
                ? Math.round(this.subtotal * data.value / 100)
                : data.value;

            this.appliedPromo = { code: this.promoInput.trim().toUpperCase(), discount };
        } catch {
            this.promoError = 'Impossible de vérifier ce code.';
        } finally {
            this.promoChecking = false;
        }
    },

    revalidatePromo() {
        // Le panier a changé : on invalide l'aperçu, mieux vaut revérifier que d'afficher une remise fausse.
        if (this.appliedPromo) {
            this.appliedPromo = null;
            this.promoError = 'Panier modifié : réappliquez le code.';
        }
    },

    removePromo() {
        this.appliedPromo = null;
        this.promoInput = '';
        this.promoError = '';
    },
}));

Alpine.data('confirmModal', () => ({
    open: false,
    pendingForm: null,

    ask(formEl) {
        this.pendingForm = formEl;
        this.open = true;
    },

    cancel() {
        this.open = false;
        this.pendingForm = null;
    },

    confirm() {
        this.pendingForm?.submit();
        this.open = false;
    },
}));

window.Alpine = Alpine;
Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    revealOnLoad();
    revealKpiCards();
    initCounters();
    document.querySelectorAll('.data-table').forEach(revealTableRows);
});
