<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\StoreSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class MailController extends Controller
{
    public function edit()
    {
        return view('settings.mail', [
            'setting' => StoreSetting::current(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'mail_username' => ['required', 'email', 'max:255'],
            'mail_password' => ['nullable', 'string', 'max:255'],
            'mail_from_name' => ['nullable', 'string', 'max:255'],
            'remove_mail' => ['nullable', 'boolean'],
        ]);

        $setting = StoreSetting::current();

        if ($request->boolean('remove_mail')) {
            $setting->update(['mail_username' => null, 'mail_password' => null, 'mail_from_name' => null]);

            ActivityLog::record('settings.mail_removed', 'Configuration e-mail supprimée.');

            return back()->with('status', 'Configuration e-mail supprimée — les notifications repassent en mode journal (aucun envoi réel).');
        }

        $update = [
            'mail_username' => $data['mail_username'],
            'mail_from_name' => $data['mail_from_name'] ?? null,
        ];

        // Le mot de passe d'application reste optionnel à la modification : laisser vide
        // conserve celui déjà enregistré (même logique que le mot de passe utilisateur).
        if (! empty($data['mail_password'])) {
            $update['mail_password'] = $data['mail_password'];
        } elseif (! $setting->hasMailConfigured()) {
            return back()->withInput()->with('error', "Le mot de passe d'application est requis lors de la première configuration.");
        }

        $setting->update($update);

        ActivityLog::record('settings.mail_updated', "Configuration e-mail mise à jour ({$data['mail_username']}).");

        return back()->with('status', 'Configuration e-mail enregistrée.');
    }

    public function sendTest(Request $request)
    {
        $setting = StoreSetting::current();

        if (! $setting->hasMailConfigured()) {
            return back()->with('error', 'Renseignez et enregistrez un compte e-mail avant de tester.');
        }

        try {
            Mail::raw(
                "Ceci est un e-mail de test envoyé depuis {$setting->name}. Si vous le recevez, la configuration fonctionne.",
                fn ($message) => $message->to($request->user()->email)->subject('Test de configuration e-mail')
            );
        } catch (TransportExceptionInterface|\Throwable $e) {
            return back()->with('error', "Échec de l'envoi : " . $e->getMessage());
        }

        return back()->with('status', "E-mail de test envoyé à {$request->user()->email}.");
    }
}
