# INA SHOP — Boutique en ligne de vêtements et accessoires

Application Laravel complète pour une boutique de mode : site public (catalogue, panier, commande, compte client) et back-office de gestion (produits, stock, commandes, finance, relation client) pour une petite équipe (administrateur + vendeur·se·s).

## Stack technique

- **Backend** : Laravel 12, PHP 8.2, MySQL
- **Frontend** : Blade + SCSS sur mesure (pas de Tailwind) + Alpine.js pour l'interactivité + GSAP pour les animations
- **Build** : Vite, deux points d'entrée distincts — `resources/scss/app.scss` + `resources/js/app.js` (back-office) et `resources/scss/shop.scss` + `resources/js/shop.js` (boutique publique)
- **Authentification** : deux garde d'auth indépendants — `web` (équipe, `App\Models\User`) et `customer` (clientes, `App\Models\Customer`) — unifiés derrière un seul formulaire de connexion (`/connexion`), qui redirige automatiquement vers l'espace admin ou l'espace client selon le compte détecté

## Deux espaces

### Boutique publique (`/`, `/boutique`, `/produit/{id}`, `/panier`, `/commande`, `/mon-compte/*`)

- Catalogue avec filtres, recherche, fiches produit (variantes taille/couleur, galerie photo, avis)
- Panier et tunnel de commande, avec instructions de paiement détaillées par moyen (Orange Money, Wave, paiement à la livraison) et suivi de commande en temps réel
- Espace client : commandes (avec annulation possible avant expédition), avis (publiés immédiatement, réponse de la boutique visible et envoyée par e-mail), réclamations, adresses, favoris
- Notification par e-mail à chaque étape : confirmation de commande, changement de statut, vérification/rejet du paiement, annulation, réponse à un avis ou une réclamation, remboursement
- Page Conditions générales d'utilisation et de vente, éditable depuis le back-office

### Back-office (`/admin/*`)

- **Tableau de bord** : indicateurs clés, activité récente
- **Produits & Stock** : catalogue, variantes, mouvements de stock tracés, seuils d'alerte
- **Commandes** : suivi du cycle de vie (en attente → confirmée → préparée → expédiée → livrée), vérification manuelle des paiements Orange Money/Wave
- **Vente manuelle (POS)** : interface caisse plein écran pour les ventes en boutique physique
- **Finance** : chiffre d'affaires encaissé, remboursements, marge brute, dépenses, bénéfice net, comparatif sur 3 mois, export CSV détaillé et rapport HTML imprimable
- **Clients** : segmentation (nouveau / régulier / VIP), historique de commandes
- **Avis & Réclamations** : modération, réponses, remboursements liés à une réclamation (plafonnés au montant de la commande)
- **Marketing** : codes promo, bannières d'accueil administrables
- **Équipe & Journal d'activité** : gestion des comptes admin/vendeur, traçabilité des actions sensibles (réservé à l'administrateur)
- **Paramètres** : identité de la boutique (nom, logo, réseaux sociaux, adresse), devise, moyens de paiement (numéros Orange Money/Wave, activation/désactivation), configuration e-mail, conditions générales — ces trois derniers réservés à l'administrateur

## Installation

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Configurer la base de données dans `.env`, puis :

```bash
php artisan migrate --seed
npm run build
php artisan serve
```

La configuration e-mail (SMTP) et les moyens de paiement (numéros Orange Money/Wave) se font ensuite directement dans Paramètres, sans intervention technique — pas besoin de les mettre dans `.env`.

## Déploiement en production

```bash
composer install --no-dev --optimize-autoloader
npm install && npm run build

cp .env.example .env      # puis éditer .env (voir ci-dessous)
php artisan key:generate

php artisan migrate --force   # jamais --seed en production : voir plus bas
php artisan storage:link

php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**Dans `.env` de production**, en plus des identifiants de base de données réels :

- `APP_ENV=production`, `APP_DEBUG=false` — une page d'erreur en debug expose la stack trace (chemins serveur, requêtes SQL) à n'importe quel visiteur.
- `APP_URL` doit être l'URL réelle du site (`https://...`), exactement — une valeur incorrecte casse les liens absolus (images, e-mails transactionnels, redirections après paiement) **et** les URL des images produits (générées via `Storage::disk('public')->url()`, qui se base sur `APP_URL`).
- `SESSION_SECURE_COOKIE=true` une fois le site servi en HTTPS, pour que le cookie de session ne soit jamais transmis en clair.
- Le site redirige automatiquement tout visiteur vers l'hôte/schéma exact défini par `APP_URL` (`App\Http\Middleware\EnsureCanonicalHost`) — si le domaine répond à la fois en `exemple.com` et `www.exemple.com`, sans ça chaque variante aurait son propre cookie de session, et une cliente pouvait réinitialiser son mot de passe sur l'une et se retrouver déconnectée sur l'autre sans message d'erreur clair.
- Les identifiants e-mail SMTP et les numéros Wave/Orange Money **ne se configurent pas dans `.env`** : ils se saisissent dans l'admin (Paramètres → E-mail, Paramètres → Paiement) après la première connexion.

**Ne jamais lancer `php artisan migrate --seed` en production** : `DatabaseSeeder` crée des données de démonstration (comptes `admin@boutique.test` / `vendeuse@boutique.test` avec le mot de passe codé en dur `password`, ~48 produits factices, ~27 commandes factices). `migrate --force` seul ne l'exécute pas.

**Créer le tout premier compte administrateur** avec la commande interactive dédiée (mot de passe jamais écrit en clair dans le code ou les logs) :

```bash
php artisan app:create-admin
```

Elle refuse de s'exécuter si un compte admin existe déjà — pour un compte supplémentaire, utiliser `/admin/utilisateurs` une fois connecté.

Sur certains hébergements mutualisés (terminal SSH web, panneau « exécuter une commande »), la saisie masquée du mot de passe ne fonctionne pas correctement et la commande échoue avec « Le champ mot de passe est obligatoire » alors qu'un mot de passe a bien été tapé. Dans ce cas, utiliser les options à la place :

```bash
php artisan app:create-admin --name="Nom complet" --email="admin@exemple.com" --password="motdepasse"
```

**Avant d'ouvrir la boutique au public**, compléter dans l'admin : Paramètres → Identité (nom, logo), Paramètres → E-mail (SMTP réel), Paramètres → Paiement (numéros Wave/Orange Money, activation du paiement à la livraison) et Paramètres → Conditions générales (texte juridique adapté à la boutique).

**Si les images produits ne s'affichent pas après le déploiement (erreur 403 sur `/storage/...`)**, dans l'ordre :
1. `ls -la public/storage` sur le serveur — doit être un **lien symbolique** valide vers `storage/app/public`, pas un dossier vide. Certains hébergements mutualisés bloquent par défaut le suivi des liens symboliques par Apache, ce qui renvoie 403 même si le fichier existe réellement sur le disque — `public/.htaccess` force désormais `Options +FollowSymLinks` pour éviter ça.
2. Si le lien est absent ou cassé : supprimer `public/storage` s'il existe déjà comme dossier vide, puis relancer `php artisan storage:link`.
3. Vérifier que `APP_URL` correspond exactement au domaine réel (voir ci-dessus) — sinon les URL générées pointent vers le mauvais hôte même si le fichier existe bien sur le disque.

Vérifier aussi que `storage/` et `bootstrap/cache/` sont accessibles en écriture par le serveur web.

## Notes de conception

- Les montants sont stockés en centimes (entiers) partout en base ; seul l'affichage (symbole, nombre de décimales) dépend de la devise choisie dans Paramètres.
- Le journal d'activité (`ActivityLog`) ne trace que les actions de l'équipe (garde `web`) — une action initiée par une cliente ne doit jamais s'y retrouver associée à un membre du staff.
- Les notifications e-mail utilisent un thème de marque partagé (`resources/views/vendor/mail`), appliqué automatiquement à tous les e-mails transactionnels de l'application.
