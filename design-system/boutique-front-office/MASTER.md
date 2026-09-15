# Design System Master File

> **LOGIC:** When building a specific page, first check `design-system/pages/[page-name].md`.
> If that file exists, its rules **override** this Master file.
> If not, strictly follow the rules below.

---

**Project:** Boutique Front-Office
**Generated:** 2026-08-01 23:16:36
**Revised:** 2026-08-08 — palette repensée à la demande de l'utilisateur ("j'aime la logique mais pas le design/couleur, je veux du vif type rouge sang")
**Revised again:** 2026-08-10 — thème basculé en sombre + rouge vif (inspiration structurelle `ha.com.tn`, grille catégories bento)
**Revised again (2026-08-26)** — remplacé par la charte graphique officielle de la marque **INA** : luxe accessible, vert profond + doré + blanc cassé, thème clair (header/footer restent en vert profond). Structure des pages inchangée, seuls les tokens visuels (couleurs, typographie, boutons) ont été appliqués.
**Category:** Luxury / premium fashion e-commerce
**Design Dials:** Variance 4/10 (sobre, moins d'éléments mais de meilleure qualité) | Motion 3/10 (lent, discret) | Density 3/10 (Spacious)

---

## Global Rules

### Positionnement de marque

INA — « Vous offre le luxe à votre portée ». Luxe, élégance, sophistication, qualité, exclusivité, accessibilité du luxe. Impression recherchée : haut de gamme et intemporelle, sans être chargée. Règle d'or : **moins d'éléments, mais de meilleure qualité** — l'espace vide renforce la sensation de luxe.

### Color Palette (thème clair)

| Role | Hex | CSS Variable |
|------|-----|--------------|
| Primary (vert profond) | `#003C35` | `--shop-color-primary` |
| Primary Hover | `#0A4F46` | `--shop-color-primary-hover` |
| On Primary | `#FFFFFF` | `--shop-color-on-primary` |
| Secondary (vert footer, très foncé) | `#002923` | `--shop-color-secondary` |
| Accent (doré — à utiliser avec modération) | `#C9A24A` | `--shop-color-accent` |
| Accent Hover (doré clair) | `#E2C77A` | `--shop-color-accent-hover` |
| On Accent (texte vert profond sur fond doré) | `#003C35` | `--shop-color-on-accent` |
| On Dark (texte/icônes sur header, footer, overlay hero) | `#F8F6F0` | `--shop-color-on-dark` |
| On Dark Muted | `rgba(248,246,240,.72)` | `--shop-color-on-dark-muted` |
| On Dark Border | `rgba(248,246,240,.18)` | `--shop-color-on-dark-border` |
| Background (blanc cassé) | `#F8F6F0` | `--shop-color-background` |
| Foreground (noir profond) | `#111111` | `--shop-color-foreground` |
| Muted | `#F1EDE3` | `--shop-color-muted` |
| Muted Foreground (gris élégant) | `#777777` | `--shop-color-muted-foreground` |
| Card | `#FFFFFF` | `--shop-color-card` |
| Border | `rgba(17,17,17,.12)` | `--shop-color-border` |
| Destructive | `#B3261E` | `--shop-color-destructive` |
| Ring (focus, doré) | `#C9A24A` | `--shop-color-ring` |

**Color Notes:** Vert profond = fonds structurants (header, footer, boutons importants, bannière CTA). Doré = accent premium avec modération (bordures de bouton, badges, icônes de note, hover de navigation, liseré de survol des cartes produit) — **jamais** en couleur de fond dominante. Le header et le footer sortent du thème clair général de la page : ils utilisent les tokens `--shop-color-on-dark*` pour leur texte/icônes, pas `--shop-color-foreground` (qui suppose un fond clair). Les overlays sur les images hero utilisent un dégradé vert profond très transparent, jamais noir pur.

### Typography

- **Heading Font:** Playfair Display (serif élégante) — italique disponible pour citations/accents
- **Body Font:** Montserrat (sans-serif moderne, lisible)
- **Price/Label:** Montserrat également (le mono "Fira Code" d'origine a été abandonné — trop "code", pas assez luxe)
- **Poids titres:** volontairement modérés (600–700, jamais 800) et tracking neutre/nul — un serif n'a pas besoin de graisse extrême pour paraître premium
- **Google Fonts:** [Playfair Display + Montserrat](https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,500&family=Montserrat:wght@400;500;600;700;800&display=swap)

**CSS Import:**
```css
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,500&family=Montserrat:wght@400;500;600;700;800&display=swap');
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
| `--shop-shadow-sm` | `0 1px 2px rgba(17,17,17,0.06)` | Subtle lift |
| `--shop-shadow-md` | `0 4px 16px rgba(17,17,17,0.08)` | Cards, buttons |
| `--shop-shadow-lg` | `0 12px 32px rgba(17,17,17,0.1)` | Hover cards, dropdowns |
| `--shop-shadow-xl` | `0 24px 48px rgba(17,17,17,0.14)` | Modals, featured cards |

### Radius

| Token | Value | Usage |
|-------|-------|-------|
| `--shop-radius-sm` | `6px` | |
| `--shop-radius-md` | `10px` | **Boutons** (voir Anti-Patterns — jamais pill) |
| `--shop-radius-lg` | `14px` | Cartes, modals |
| `--shop-radius-pill` | `999px` | Badges, chips, champs de recherche, icônes rondes — pas les boutons CTA |

---

## Component Specs

### Buttons

```css
/* Bouton principal — vert profond, bordure dorée, bascule au survol */
.shop-btn--primary {
  background: #003C35;
  color: #FFFFFF;
  border: 1.5px solid #C9A24A;
  border-radius: 10px; /* jamais 999px */
  padding: 0.875rem 1.75rem;
  font-weight: 600;
  box-shadow: var(--shop-shadow-md);
  transition: all 220ms ease;
}
.shop-btn--primary:hover {
  background: #C9A24A;
  color: #003C35;
  border-color: #C9A24A;
  transform: translateY(-1px);
}

/* Bouton doré (CTA secondaire ponctuel) */
.shop-btn--accent {
  background: #C9A24A;
  color: #003C35;
}
.shop-btn--accent:hover { background: #E2C77A; }

/* Bouton secondaire — outline vert */
.shop-btn--secondary {
  background: transparent;
  color: #003C35;
  border: 1.5px solid #003C35;
}
```

### Cards

```css
.product-card {
  background: #FFFFFF;
  border: 1px solid transparent;
  border-radius: 14px;
  box-shadow: none; /* élévation seulement au survol */
  transition: all 260ms ease;
}
.product-card:hover {
  transform: translateY(-4px);
  box-shadow: var(--shop-shadow-lg);
  border-color: #C9A24A; /* fin liseré doré au survol, charte §9 */
}
```

### Badges

`--shop-color-accent` (doré) avec texte `--shop-color-on-accent` (vert profond, **jamais blanc** — contraste insuffisant sur doré).

### Inputs

```css
.shop-input {
  border: 1px solid rgba(17,17,17,.12);
  border-radius: 10px;
  background: #FFFFFF;
}
.shop-input:focus {
  border-color: #003C35;
  box-shadow: 0 0 0 3px rgba(201,162,74,.35); /* ring doré */
}
```

### Header (2 lignes)

Ligne 1 : logo (doré, majuscules) + recherche centrée + icônes (blanc cassé). Ligne 2 : navigation centrée, liens en majuscules/petites capitales blanc cassé, **hover → doré + soulignement animé depuis le centre**. Fond vert profond sur les deux lignes.

### Footer

Fond vert très foncé (`--shop-color-secondary`), texte en blanc cassé, liens/icônes → doré au survol. Séparateur du haut en doré fin (1px).

---

## Style Guidelines

**Style:** Luxe accessible, minimaliste et raffiné — inspiré des maisons de mode haut de gamme

**Keywords:** Élégance, luxe, qualité, raffinement, accessibilité, sobriété

**Best For:** Boutique mode premium, accessoires haut de gamme

**Key Effects:** Doré réservé aux accents (bordures de bouton, hover, badges, notes, liseré carte produit) ; vert profond porte la structure (header/footer/CTA) ; beaucoup d'espace blanc ; boutons à angles adoucis mais jamais en pilule complète ; animations lentes et discrètes (220–520ms)

### Page Pattern (inchangé — seuls les tokens visuels ont changé)

- **Accueil:** Hero/bannière (carrousel administrable ou fallback statique) > Catégories en grille bento asymétrique > Grille nouveautés > Avis clients (étoiles dorées) > CTA newsletter/boutique (fond vert, mot géant en filigrane doré)
- **Catalogue** (`/boutique`, `/boutique/{categorie}`) — sticky filter sidebar (desktop) / bottom-sheet filters (mobile) + grille produits
- **Fiche produit** (`/produit/{slug}`) — deux colonnes : galerie sticky (gauche) + buy box (droite), étoiles de notation dorées
- **Panier** — colonne unique, résumé sticky
- **Tunnel de commande** — stepper linéaire, une colonne
- **Confirmation / suivi** — carte centrée, timeline de statut

---

## Motion

Lente et discrète (charte §13) — pas d'animation agressive.

**Stagger List** (Standard) — Trigger: load or scroll | Duration: 300-450ms | Easing: `back.out(1.4)`

```js
gsap.from('.grid-item', { opacity: 0, scale: 0.92, y: 16, duration: 0.4, stagger: { each: 0.06, from: 'start', grid: 'auto' }, ease: 'back.out(1.4)' });
```

---

## Anti-Patterns (Do NOT Use)

- ❌ Doré en couleur de fond dominante — c'est un accent, jamais une surface pleine page
- ❌ Texte blanc sur fond doré (contraste insuffisant) — utiliser `--shop-color-on-accent` (vert)
- ❌ Boutons en pilule complète (`border-radius: 999px`) — angles adoucis seulement (`--shop-radius-md`)
- ❌ Réutiliser `--shop-color-foreground`/`--shop-color-border` (tokens "fond clair") sur le header/footer vert — utiliser les tokens `--shop-color-on-dark*`
- ❌ Ombres colorées
- ❌ Emojis comme icônes — utiliser des SVG (set outline maison)
- ❌ `cursor:pointer` manquant sur les éléments cliquables
- ❌ Hover qui déplace le layout (privilégier `transform`)
- ❌ Contraste texte < 4.5:1
- ❌ Changements d'état instantanés (toujours 150-300ms, idéalement plus lent ici)
- ❌ États focus invisibles

---

## Pre-Delivery Checklist

- [ ] Pas d'emoji utilisé comme icône (SVG uniquement)
- [ ] Icônes cohérentes, fines/outline
- [ ] `cursor-pointer` sur tous les éléments cliquables
- [ ] Hover states avec transitions douces et lentes
- [ ] Contraste texte 4.5:1 minimum (attention particulière doré/blanc)
- [ ] Focus visible au clavier (ring doré)
- [ ] `prefers-reduced-motion` respecté
- [ ] Responsive: 375px, 768px, 1024px, 1440px
- [ ] Pas de contenu caché derrière le header sticky
- [ ] Pas de scroll horizontal sur mobile
