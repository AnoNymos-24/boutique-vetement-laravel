# Design System Master File

> **LOGIC:** When building a specific page, first check `design-system/pages/[page-name].md`.
> If that file exists, its rules **override** this Master file.
> If not, strictly follow the rules below.

---

**Project:** Boutique Front-Office
**Generated:** 2026-08-01 23:16:36
**Revised:** 2026-08-08 — palette repensée à la demande de l'utilisateur ("j'aime la logique mais pas le design/couleur, je veux du vif type rouge sang")
**Revised again:** 2026-08-10 — thème basculé en sombre + rouge vif (inspiration structurelle `ha.com.tn`, grille catégories bento)
**Category:** Wardrobe & Outfit Planner (fashion e-commerce)
**Design Dials:** Variance 7/10 (Bold / editorial) | Motion 6/10 (Standard) | Density 3/10 (Spacious)

---

## Global Rules

### Color Palette (thème sombre)

| Role | Hex | CSS Variable |
|------|-----|--------------|
| Primary (rouge vif) | `#FF1A2C` | `--shop-color-primary` |
| Primary Hover (glow) | `#FF3B4C` | `--shop-color-primary-hover` |
| On Primary | `#FFFFFF` | `--shop-color-on-primary` |
| Secondary (rouge profond) | `#B3121F` | `--shop-color-secondary` |
| Accent (rouge vif secondaire — badges/notes) | `#FF4D5E` | `--shop-color-accent` |
| Accent Hover | `#FF6B79` | `--shop-color-accent-hover` |
| Background | `#0D0D0D` | `--shop-color-background` |
| Foreground | `#F5F5F0` | `--shop-color-foreground` |
| Muted / Card (surfaces élevées) | `#1A1A1A` | `--shop-color-muted` / `--shop-color-card` |
| Muted Foreground | `#A3A39C` | `--shop-color-muted-foreground` |
| Border | `rgba(255,255,255,.12)` | `--shop-color-border` |
| Destructive | `#F04438` | `--shop-color-destructive` |
| Ring | `#FF1A2C` | `--shop-color-ring` |

**Color Notes:** Fond noir/anthracite partout, rouge vif en accent unique (CTA, prix, badges, liens, glow au survol des boutons primaires). L'élévation des surfaces vient du contraste de ton `--shop-color-card` (#1A1A1A) vs `--shop-color-background` (#0D0D0D) plutôt que du flou d'ombre (peu visible sur fond sombre) — les ombres noires restent un renfort léger. `color-scheme: dark` posé sur `body.shop` pour les contrôles natifs. Tokens de statut (succès/erreur/avertissement) en fond teinté transparent + texte vif, pas de pastels clairs (illisibles sur noir).

### Typography

- **Heading Font:** Syne (display, bold, géométrique, mode/edgy)
- **Body Font:** Manrope
- **Price/Label Mono:** Fira Code — conservé pour la cohérence cross-app avec le back-office
- **Mood:** fashion, avant-garde, bold, artistic, edgy — remplace l'ancien pairing Playfair Display/Inter (trop "luxe doux") par "Fashion Forward", plus proche du rendu vif demandé
- **Google Fonts:** [Syne + Manrope + Fira Code](https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=Manrope:wght@400;500;600;700;800&family=Fira+Code:wght@500;600&display=swap)

**CSS Import:**
```css
@import url('https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=Manrope:wght@400;500;600;700;800&family=Fira+Code:wght@500;600&display=swap');
```

### Spacing Variables

*Density: 3/10 — Spacious (inchangé)*

| Token | Value | Usage |
|-------|-------|-------|
| `--shop-space-xs` | `4px` / `0.25rem` | Tight gaps |
| `--shop-space-sm` | `8px` / `0.5rem` | Icon gaps, inline spacing |
| `--shop-space-md` | `16px` / `1rem` | Standard padding |
| `--shop-space-lg` | `24px` / `1.5rem` | Section padding |
| `--shop-space-xl` | `40px` / `2.5rem` | Large gaps |
| `--shop-space-2xl` | `64px` / `4rem` | Section margins |
| `--shop-space-3xl` | `96px` / `6rem` | Hero padding |

### Shadow Depths

| Level | Value | Usage |
|-------|-------|-------|
| `--shop-shadow-sm` | `0 1px 2px rgba(18,18,18,0.06)` | Subtle lift |
| `--shop-shadow-md` | `0 4px 16px rgba(18,18,18,0.08)` | Cards, buttons |
| `--shop-shadow-lg` | `0 12px 32px rgba(18,18,18,0.12)` | Hover cards, dropdowns |
| `--shop-shadow-xl` | `0 24px 48px rgba(18,18,18,0.16)` | Modals, featured cards |

### Radius

*Plus franc qu'avant (moins "soft UI"), esprit éditorial mode.*

| Token | Value |
|-------|-------|
| `--shop-radius-sm` | `6px` |
| `--shop-radius-md` | `10px` |
| `--shop-radius-lg` | `14px` |
| `--shop-radius-pill` | `999px` (boutons, badges, pills catégorie) |

---

## Component Specs

### Buttons

```css
/* Primary Button — rouge sang */
.shop-btn--primary {
  background: #A4123F;
  color: #FFFFFF;
  border-radius: 999px;
  padding: 0.875rem 1.75rem;
  font-weight: 600;
  box-shadow: var(--shop-shadow-md);
  transition: all 200ms ease;
}
.shop-btn--primary:hover { background: #7A0930; transform: translateY(-1px); }

/* Secondary Button — outline noir/rouge */
.shop-btn--secondary {
  background: transparent;
  color: #A4123F;
  border: 1.5px solid #A4123F;
}

/* Accent Button — rouge vif (CTA secondaire ponctuel) */
.shop-btn--accent {
  background: #E11D2E;
  color: #FFFFFF;
}
.shop-btn--accent:hover { background: #B3121F; }
```

### Cards

```css
.product-card {
  background: #FFFFFF;
  border-radius: 14px;
  box-shadow: var(--shop-shadow-md);
  transition: all 200ms ease;
}
.product-card:hover {
  transform: translateY(-4px);
  box-shadow: var(--shop-shadow-lg);
}
```

### Badges (promo/nouveau/rating)

Utilisent `--shop-color-accent` (#E11D2E) — cohérent avec la famille rouge sang, sans réintroduire une couleur tierce (or/rose).

### Inputs

```css
.input {
  border: 1px solid #E5E2E0;
  border-radius: 10px;
}
.input:focus {
  border-color: #A4123F;
  box-shadow: 0 0 0 3px rgba(164,18,63,0.12);
}
```

---

## Style Guidelines

**Style:** Éditorial fashion (blanc/noir + accent rouge sang unique) — inspiré de "Exaggerated Minimalism" / "Fashion Forward"

**Keywords:** Bold, vivid, high contrast, editorial, single vivid accent, fashion, streetwear-premium

**Best For:** Boutique mode haut de gamme, streetwear premium, marques fortes à identité unique

**Key Effects:** Un seul accent vif (rouge) porté sur CTA/prix/badges, reste du fond en noir/blanc/gris neutre, ombres neutres (jamais colorées), typographie display bold (Syne)

### Page Pattern (inchangé)

- **Accueil:** Hero (nouveauté phare) > Catégories en grille bento asymétrique (1 grande tuile + tuiles empilées, libellé en chip sombre haut-gauche — inspiré de ha.com.tn) > Grille nouveautés > Avis clients > CTA newsletter/boutique
- **Catalogue** (`/boutique`, `/boutique/{categorie}`) — sticky filter sidebar (desktop) / bottom-sheet filters (mobile) + grille produits
- **Fiche produit** (`/produit/{slug}`) — deux colonnes : galerie sticky (gauche) + buy box (droite)
- **Panier** — colonne unique, résumé sticky
- **Tunnel de commande** — stepper linéaire, une colonne
- **Confirmation / suivi** — carte centrée, timeline de statut (réutilise le pattern back-office restylé aux couleurs boutique)

---

## Motion (inchangé)

**Stagger List** (Standard) — Trigger: load or scroll | Duration: 300-450ms | Easing: `back.out(1.4)`

```js
gsap.from('.grid-item', { opacity: 0, scale: 0.92, y: 16, duration: 0.4, stagger: { each: 0.06, from: 'start', grid: 'auto' }, ease: 'back.out(1.4)' });
```

---

## Anti-Patterns (Do NOT Use)

- ❌ Réintroduire une couleur tierce (rose/or) — un seul accent vif : le rouge sang
- ❌ Ombres colorées (rester sur des ombres neutres noir)
- ❌ Emojis comme icônes — utiliser des SVG (Heroicons/Lucide)
- ❌ `cursor:pointer` manquant sur les éléments cliquables
- ❌ Hover qui déplace le layout (privilégier `transform`)
- ❌ Contraste texte < 4.5:1
- ❌ Changements d'état instantanés (toujours 150-300ms)
- ❌ États focus invisibles

---

## Pre-Delivery Checklist

- [ ] Pas d'emoji utilisé comme icône (SVG uniquement)
- [ ] Icônes cohérentes (Heroicons/Lucide)
- [ ] `cursor-pointer` sur tous les éléments cliquables
- [ ] Hover states avec transitions douces (150-300ms)
- [ ] Contraste texte 4.5:1 minimum
- [ ] Focus visible au clavier
- [ ] `prefers-reduced-motion` respecté
- [ ] Responsive: 375px, 768px, 1024px, 1440px
- [ ] Pas de contenu caché derrière le header sticky
- [ ] Pas de scroll horizontal sur mobile
