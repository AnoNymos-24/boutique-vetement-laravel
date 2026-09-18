<?php

namespace App\Console\Commands;

use App\Models\StoreSetting;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

/**
 * Réservée à la mise en production : DatabaseSeeder crée un compte admin avec
 * un mot de passe codé en dur ("password"), pratique en local mais jamais
 * acceptable sur un vrai déploiement. Cette commande crée le tout premier
 * compte administrateur de façon interactive, sans jamais écrire de mot de
 * passe en clair nulle part (ni dans le code, ni dans un fichier de seed).
 */
class CreateAdminCommand extends Command
{
    // --password en option : certains terminaux d'hébergement mutualisé (SSH
    // web, panneaux "exécuter une commande") n'allouent pas de vrai pty, et
    // secret()/ask() y retournent alors silencieusement une chaîne vide au
    // lieu de la saisie — l'option contourne le problème en évitant toute
    // saisie interactive masquée.
    protected $signature = 'app:create-admin {--name=} {--email=} {--password=}';

    protected $description = 'Crée le tout premier compte administrateur (mise en production uniquement)';

    public function handle(): int
    {
        if (User::where('role', 'admin')->exists()) {
            $this->error('Un compte administrateur existe déjà. Pour un compte supplémentaire, utilisez /admin/utilisateurs une fois connecté.');

            return self::FAILURE;
        }

        $name = $this->option('name') ?: $this->ask('Nom complet');
        $email = $this->option('email') ?: $this->ask('Adresse e-mail');

        if ($this->option('password')) {
            $password = $confirmation = $this->option('password');
        } else {
            $password = $this->secret('Mot de passe (au moins 8 caractères)');
            $confirmation = $this->secret('Confirmer le mot de passe');

            // Sur un terminal qui ne supporte pas la saisie masquée, secret()
            // renvoie une chaîne vide sans erreur visible — d'où le message
            // Laravel générique "Le champ mot de passe est obligatoire" alors
            // que l'utilisateur a bel et bien tapé quelque chose. On le
            // détecte ici pour orienter vers la solution plutôt que de
            // laisser échouer la validation sans explication.
            if ($password === '' || $password === null) {
                $this->error('Aucune saisie détectée pour le mot de passe — ce terminal ne supporte probablement pas la saisie masquée.');
                $this->line('Relancez avec : php artisan app:create-admin --name="..." --email="..." --password="...")');

                return self::FAILURE;
            }
        }

        $validator = Validator::make(
            ['name' => $name, 'email' => $email, 'password' => $password],
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'unique:users,email'],
                'password' => ['required', 'string', 'min:8'],
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        if ($password !== $confirmation) {
            $this->error('Les deux mots de passe ne correspondent pas.');

            return self::FAILURE;
        }

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => 'admin',
            'is_active' => true,
        ]);

        // S'assure que le singleton StoreSetting existe (sinon la première page
        // admin visitée le crée avec le nom "Laravel" par défaut).
        StoreSetting::current();

        $this->info("Compte administrateur créé : {$email}");
        $this->line('Connectez-vous sur /connexion, puis complétez Paramètres → Identité (nom, logo), Paramètres → Paiement et Paramètres → E-mail avant d\'ouvrir la boutique au public.');

        return self::SUCCESS;
    }
}
