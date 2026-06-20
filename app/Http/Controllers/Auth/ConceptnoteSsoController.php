<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * SSO-Client gegen den conceptnote-OIDC-Provider (SSO Phase B).
 *
 * Authorization-Code-Flow mit PKCE (S256), bewusst dependency-frei
 * (HTTP-Facade statt Socialite). Nach erfolgreichem Token-Tausch werden
 * die Claims über /oauth/userinfo geholt. Nur Mitglieder des conceptnote-
 * Teams "Free-Spirits" (Claim is_team_member) dürfen rein; alle anderen
 * werden abgewiesen. Der lokale User wird per sso_subject ge-upsertet.
 *
 * Gegenstück zum IdP in /opt/deployments/conceptnote.
 */
class ConceptnoteSsoController extends Controller
{
    /** Startet den Login: leitet zum conceptnote-Authorize-Endpoint weiter. */
    public function redirect(Request $request): RedirectResponse
    {
        $config = config('services.conceptnote');

        if (empty($config['client_id']) || empty($config['client_secret'])) {
            abort(500, 'conceptnote-SSO ist nicht konfiguriert (CONCEPTNOTE_OIDC_* fehlt).');
        }

        $state = Str::random(40);
        $nonce = Str::random(40);
        $verifier = Str::random(64);
        $challenge = rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');

        $request->session()->put('conceptnote_sso', [
            'state' => $state,
            'nonce' => $nonce,
            'verifier' => $verifier,
        ]);

        $query = http_build_query([
            'response_type' => 'code',
            'client_id' => $config['client_id'],
            'redirect_uri' => $config['redirect'],
            'scope' => 'openid profile email',
            'state' => $state,
            'nonce' => $nonce,
            'code_challenge' => $challenge,
            'code_challenge_method' => 'S256',
        ]);

        return redirect()->away($config['issuer'] . '/oauth/authorize?' . $query);
    }

    /** Verarbeitet den Callback: Token tauschen, Claims holen, einloggen. */
    public function callback(Request $request): RedirectResponse
    {
        $config = config('services.conceptnote');
        $stored = $request->session()->pull('conceptnote_sso');

        // CSRF-/State-Schutz.
        if (! $stored || ! $request->filled('code') || ! hash_equals($stored['state'], (string) $request->query('state'))) {
            return $this->fail('Login abgebrochen oder ungültiger Status. Bitte erneut versuchen.');
        }

        // Authorization-Code gegen Tokens tauschen (PKCE, client_secret_post).
        $tokenResponse = Http::asForm()->post($config['issuer'] . '/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => $config['client_id'],
            'client_secret' => $config['client_secret'],
            'redirect_uri' => $config['redirect'],
            'code_verifier' => $stored['verifier'],
            'code' => $request->query('code'),
        ]);

        if (! $tokenResponse->ok() || ! $tokenResponse->json('access_token')) {
            report(new \RuntimeException('conceptnote-SSO Token-Tausch fehlgeschlagen: ' . $tokenResponse->status() . ' ' . $tokenResponse->body()));

            return $this->fail('Anmeldung beim Identity-Provider fehlgeschlagen.');
        }

        // Claims über UserInfo holen (mit dem Access-Token).
        $userinfo = Http::withToken($tokenResponse->json('access_token'))
            ->acceptJson()
            ->get($config['issuer'] . '/oauth/userinfo');

        if (! $userinfo->ok() || ! $userinfo->json('sub')) {
            report(new \RuntimeException('conceptnote-SSO UserInfo fehlgeschlagen: ' . $userinfo->status() . ' ' . $userinfo->body()));

            return $this->fail('Profil konnte nicht geladen werden.');
        }

        $claims = $userinfo->json();

        // Team-Gate: nur "Free-Spirits"-Mitglieder dürfen in den Workspace.
        if (! ($claims['is_team_member'] ?? false)) {
            return $this->fail('Dieser Workspace ist dem conceptnote-Team „Free-Spirits" vorbehalten.');
        }

        $user = User::updateOrCreate(
            ['sso_subject' => (string) $claims['sub']],
            [
                'name' => $claims['name'] ?? ($claims['email'] ?? 'Mitglied'),
                'email' => $claims['email'] ?? ((string) $claims['sub'] . '@sso.conceptnote'),
                'email_verified_at' => ($claims['email_verified'] ?? false) ? now() : null,
            ]
        );

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect()->intended('/workspace');
    }

    private function fail(string $message): RedirectResponse
    {
        return redirect('/workspace/login')->with('sso_error', $message);
    }
}
