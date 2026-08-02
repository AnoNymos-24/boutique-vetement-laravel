# Back Office Page Overrides

> **PROJECT:** Boutique Back-Office
> **Generated:** 2026-07-30 10:53:30
> **Page Type:** Dashboard / Data View

> ⚠️ **IMPORTANT:** Rules in this file **override** the Master file (`design-system/MASTER.md`).
> Only deviations from the Master are documented here. For all other rules, refer to the Master.

---

## Page-Specific Rules

### Layout Overrides

- **Max Width:** 1400px or full-width (pas de conteneur centré étroit type landing page)
- **Grid:** 12-column grid for data flexibility
- **Sections:** 1. Sidebar navigation (fixe/repliable), 2. Barre supérieure (fil d'ariane + recherche + notifications), 3. Cartes KPI (4-6 max), 4. Tableau de données + filtres + actions groupées

### Spacing Overrides

- **Content Density:** High — optimize for information display

### Typography Overrides

- No overrides — use Master typography

### Color Overrides

- **Strategy:** Neutre clair (fond `#F8FAFC`, cartes `#FFFFFF`) — préféré au dark mode par défaut pour une petite équipe qui utilise l'outil en continu toute la journée (lisibilité longue durée, factures/écran partagé). Couleurs de statut : vert succès, ambre alerte stock bas, rouge rupture/rejet. Dense mais lisible (contraste 4.5:1 minimum même en 12-14px).

### Component Overrides

- Avoid: Break browser/app back button behavior
- Avoid: Leave UI frozen with no feedback
- Avoid: Use arbitrary large z-index values

---

## Page-Specific Components

- No unique components for this page

---

## Recommendations

- Effects: Hover tooltips, chart zoom on click, row highlighting on hover, smooth filter animations, data loading spinners
- Navigation: Preserve navigation history properly
- Animation: Use skeleton screens or spinners
- Layout: Define z-index scale system (10 20 30 50)
- Action principale: bouton "+ Nouveau" (produit/commande) fixe en haut de la barre supérieure, pas de CTA marketing
