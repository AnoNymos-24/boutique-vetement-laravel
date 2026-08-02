# Design System Master File

> **LOGIC:** When building a specific page, first check `design-system/pages/[page-name].md`.
> If that file exists, its rules **override** this Master file.
> If not, strictly follow the rules below.

---

**Project:** Boutique Back-Office
**Generated:** 2026-07-30 10:53:30
**Category:** Inventory & Stock Management
**Design Dials:** Variance 3/10 (Centered / Minimal) | Motion 3/10 (Subtle) | Density 8/10 (Dense / Dashboard)

---

## Global Rules

### Color Palette

| Role | Hex | CSS Variable |
|------|-----|--------------|
| Primary | `#334155` | `--color-primary` |
| On Primary | `#FFFFFF` | `--color-on-primary` |
| Secondary | `#475569` | `--color-secondary` |
| Accent/CTA | `#059669` | `--color-accent` |
| Background | `#F8FAFC` | `--color-background` |
| Foreground | `#0F172A` | `--color-foreground` |
| Muted | `#F2F3F4` | `--color-muted` |
| Border | `#E6E8EA` | `--color-border` |
| Destructive | `#DC2626` | `--color-destructive` |
| Warning (stock bas) | `#F59E0B` | `--color-warning` |
| Ring | `#334155` | `--color-ring` |
| Card | `#FFFFFF` | `--color-card` |

**Color Notes:** Slate industriel + vert stock/succès + ambre pour les alertes de stock bas. Le fond de page (`--color-background`, `#F8FAFC`) et le fond des cartes (`--color-card`, `#FFFFFF`) sont volontairement différents pour que les cartes/tableaux se détachent visuellement de l'arrière-plan.

### Typography

- **Heading Font:** Fira Code
- **Body Font:** Fira Sans
- **Mood:** dashboard, data, analytics, code, technical, precise
- **Google Fonts:** [Fira Code + Fira Sans](https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600;700&family=Fira+Sans:wght@300;400;500;600;700&display=swap)

**CSS Import:**
```css
@import url('https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600;700&family=Fira+Sans:wght@300;400;500;600;700&display=swap');
```

### Spacing Variables

*Density: 8/10 — Dense / Dashboard*

| Token | Value | Usage |
|-------|-------|-------|
| `--space-xs` | `2px` / `0.125rem` | Tight gaps |
| `--space-sm` | `4px` / `0.25rem` | Icon gaps, inline spacing |
| `--space-md` | `8px` / `0.5rem` | Standard padding |
| `--space-lg` | `12px` / `0.75rem` | Section padding |
| `--space-xl` | `16px` / `1rem` | Large gaps |
| `--space-2xl` | `24px` / `1.5rem` | Section margins |
| `--space-3xl` | `32px` / `2rem` | Hero padding |

### Shadow Depths

| Level | Value | Usage |
|-------|-------|-------|
| `--shadow-sm` | `0 1px 2px rgba(0,0,0,0.05)` | Subtle lift |
| `--shadow-md` | `0 4px 6px rgba(0,0,0,0.1)` | Cards, buttons |
| `--shadow-lg` | `0 10px 15px rgba(0,0,0,0.1)` | Modals, dropdowns |
| `--shadow-xl` | `0 20px 25px rgba(0,0,0,0.15)` | Hero images, featured cards |

---

## Component Specs

### Buttons

```css
/* Primary Button */
.btn-primary {
  background: #059669;
  color: white;
  padding: 12px 24px;
  border-radius: 8px;
  font-weight: 600;
  transition: all 200ms ease;
  cursor: pointer;
}

.btn-primary:hover {
  opacity: 0.9;
  transform: translateY(-1px);
}

/* Secondary Button */
.btn-secondary {
  background: transparent;
  color: #334155;
  border: 2px solid #334155;
  padding: 12px 24px;
  border-radius: 8px;
  font-weight: 600;
  transition: all 200ms ease;
  cursor: pointer;
}
```

### Cards

```css
.card {
  background: #FFFFFF;
  border: 1px solid #E6E8EA;
  border-radius: 12px;
  padding: 16px;
  box-shadow: var(--shadow-sm);
  transition: box-shadow 200ms ease;
}

/* Cards de contenu (tableaux, formulaires) ne "flottent" pas au survol : réservé aux cartes cliquables (KPI, raccourcis) */
.card--clickable:hover {
  box-shadow: var(--shadow-md);
  cursor: pointer;
}
```

### Tableaux de données (stock, commandes, ventes)

```css
.table-wrapper {
  overflow-x: auto; /* jamais de table qui casse le layout mobile */
}

table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
}

thead th {
  position: sticky;
  top: 0;
  background: #F8FAFC;
  text-align: left;
  font-weight: 600;
  color: #475569;
  padding: 8px 12px;
  border-bottom: 1px solid #E6E8EA;
}

tbody td {
  padding: 8px 12px;
  border-bottom: 1px solid #F2F3F4;
  height: 36px; /* --table-row-height */
}

tbody tr:hover {
  background: #F8FAFC;
}

/* Badge de statut (stock, commande, paiement) */
.badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 2px 8px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 600;
}
.badge--success { background: #DCFCE7; color: #059669; }   /* en stock / payé / livré */
.badge--warning { background: #FEF3C7; color: #B45309; }   /* stock bas / en attente vérification */
.badge--destructive { background: #FEE2E2; color: #DC2626; } /* rupture / rejeté / annulé */
```

### Inputs

```css
.input {
  padding: 12px 16px;
  border: 1px solid #E2E8F0;
  border-radius: 8px;
  font-size: 16px;
  transition: border-color 200ms ease;
}

.input:focus {
  border-color: #334155;
  outline: none;
  box-shadow: 0 0 0 3px #33415520;
}
```

### Modals

```css
.modal-overlay {
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
}

.modal {
  background: white;
  border-radius: 16px;
  padding: 32px;
  box-shadow: var(--shadow-xl);
  max-width: 500px;
  width: 90%;
}
```

---

## Style Guidelines

**Style:** Data-Dense Dashboard *(corrigé manuellement — la recherche automatique avait renvoyé "Exaggerated Minimalism", un style landing page mode/luxe, non pertinent pour un back-office)*

**Keywords:** Widgets multiples, tableaux de données, cartes KPI, padding minimal, grille, densité d'information maximale

**Best For:** Dashboards BI, back-office e-commerce, reporting opérationnel — exactement le cas d'usage stock/commandes/finance de ce projet

**Key Effects:** Hover discret sur les lignes de tableau, tooltips au survol des graphiques, transitions de filtre fluides, spinners de chargement des données

### Structure de page (layout back-office)

- **Sidebar gauche fixe** (`--sidebar-width: 240px`) : navigation par module — Tableau de bord, Produits, Stock, Commandes, Finance, Clients, Avis, Marketing, Paramètres. Repliable sur tablette (icônes seules), masquée en drawer sur mobile.
- **Barre supérieure** (`--header-height: 56px`) : fil d'ariane, recherche rapide, notifications (alertes stock bas, paiements à vérifier), profil utilisateur (rôle admin/vendeur visible).
- **Zone de contenu** : grille 12 colonnes, cartes KPI en haut de page (CA du jour, commandes à traiter, alertes stock — 4 à 6 max, voir §Executive Dashboard), tableau de données en dessous avec filtres + actions groupées.
- **Vue "Vente manuelle" (mode caisse)** : layout dédié plein écran, moins dense, gros boutons tactiles — c'est un point de vente utilisé rapidement, pas un tableau de données.

---

## Motion

*Un back-office n'a pas de scroll marketing — la seule animation à valeur ajoutée ici est le retour visuel sur chargement/actions, pas de la décoration.*

**Chargement de données (KPI/tableau)** — Duration: 150-250ms | Easing: `power1.out`, transitions CSS simples suffisent (pas besoin de GSAP pour un back-office Blade) :

```css
.skeleton {
  animation: pulse 1.5s ease-in-out infinite;
  background: #F2F3F4;
}
@keyframes pulse { 50% { opacity: 0.5; } }
```

- ✅ Skeleton loader sur les cartes KPI et lignes de tableau pendant le chargement (jamais d'écran blanc)
- ✅ Bouton en état `disabled` + spinner pendant une action async (éviter double soumission — cf. `--table-row-height`)
- ❌ Pas d'animation décorative (parallax, reveal au scroll) : ça ralentit une équipe qui utilise l'outil toute la journée
- ⚡ Respecter `prefers-reduced-motion` même pour les pulses de skeleton

---

## Icônes

Set unique, outline, cohérent — **Phosphor Icons** (ou Heroicons si le projet en dépend déjà) :

| Usage | Icône | Notes |
|-------|-------|-------|
| Sidebar toggle | `Sidebar` / `List` | Repli sidebar / drawer mobile |
| Tableau de bord | `House` ou `GridFour` | Item de nav "Accueil" |
| Retour | `ArrowLeft` | Navigation, breadcrumb |
| Succès / stock OK | `Check` (dans `.badge--success`) | Vert `#059669` |
| Erreur / rupture | `XCircle` (dans `.badge--destructive`) | Rouge `#DC2626` |
| Alerte stock bas | icône `Warning` (triangle) | Ambre `#F59E0B`, jamais d'emoji ⚠️ |

## Règles UX spécifiques au back-office

Issues de la base UX (sévérité indiquée) — à appliquer en priorité sur les écrans Stock / Commandes / Finance :

- **Actions groupées** (sévérité basse) : case à cocher par ligne + barre d'actions groupées (ex: "Marquer comme expédiées", "Exporter"). Ne jamais forcer une édition ligne par ligne sur un catalogue de plusieurs dizaines de références.
- **Tableaux responsive** (sévérité moyenne) : `overflow-x: auto` sur mobile/tablette plutôt que de casser le layout — utile pour l'équipe qui vérifie le stock depuis un téléphone.
- **Confirmation avant action destructive** (sévérité haute) : modale "Êtes-vous sûr ?" avant suppression produit, annulation de commande, ou rejet de paiement — ce sont des actions qui touchent le stock et la finance, donc irréversibles en pratique.
- **Boutons en état de chargement** (sévérité haute) : désactiver + spinner pendant la validation d'un paiement ou la mise à jour d'un stock, pour éviter les doubles soumissions (double décrément de stock notamment).
- **Feedback de confirmation** (sévérité moyenne) : toast "Enregistré" après chaque sauvegarde — jamais de succès silencieux, surtout sur les écrans Finance où l'équipe doit être sûre qu'une écriture a bien été prise en compte.

## Notes techniques Laravel + Blade + SCSS/JS

- **Toujours paginer** les listes (produits, commandes, mouvements de stock) : `->paginate(20)` + `{{ $items->links() }}`, jamais `->get()` sur une table potentiellement longue passée à une vue Blade.
- **Cache des fragments statiques** (sidebar, menu de navigation) via le helper `cache()` pour ne pas re-render à chaque requête.
- Le choix Blade + SCSS + JS/TS (sans Inertia/SPA) implique une navigation classique par rechargement de page : soigner les temps de réponse serveur et les états de chargement (skeleton ci-dessus) pour éviter l'impression de lenteur.

## Anti-Patterns (Do NOT Use)

- ❌ Excessive decoration
- ❌ Complex shadows
- ❌ 3D effects

### Additional Forbidden Patterns

- ❌ **Emojis as icons** — Use SVG icons (Heroicons, Lucide, Simple Icons)
- ❌ **Missing cursor:pointer** — All clickable elements must have cursor:pointer
- ❌ **Layout-shifting hovers** — Avoid scale transforms that shift layout
- ❌ **Low contrast text** — Maintain 4.5:1 minimum contrast ratio
- ❌ **Instant state changes** — Always use transitions (150-300ms)
- ❌ **Invisible focus states** — Focus states must be visible for a11y

---

## Pre-Delivery Checklist

Before delivering any UI code, verify:

- [ ] No emojis used as icons (use SVG instead)
- [ ] All icons from consistent icon set (Heroicons/Lucide)
- [ ] `cursor-pointer` on all clickable elements
- [ ] Hover states with smooth transitions (150-300ms)
- [ ] Light mode: text contrast 4.5:1 minimum
- [ ] Focus states visible for keyboard navigation
- [ ] `prefers-reduced-motion` respected
- [ ] Responsive: 375px, 768px, 1024px, 1440px
- [ ] No content hidden behind fixed navbars
- [ ] No horizontal scroll on mobile
