<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class WordPressAuthController extends Controller
{
    private const TTL = 120; // segundos de validez del token

    /**
     * GET /admin/auth/wordpress?token=xxx
     *
     * Valida el token firmado generado por el plugin de WordPress,
     * crea o recupera el usuario admin y lo autentica en Backpack.
     */
    public function login(Request $request): RedirectResponse
    {
        $raw = $request->query('token');

        if (! $raw) {
            return $this->fail('Token no recibido.');
        }

        // Separar payload y firma
        $parts = explode('.', $raw, 2);

        if (count($parts) !== 2) {
            return $this->fail('Formato de token inválido.');
        }

        [$payload, $receivedSig] = $parts;

        // Validar firma HMAC
        $secret      = config('services.wordpress_sso.secret');
        $expectedSig = hash_hmac('sha256', $payload, $secret);

        if (! hash_equals($expectedSig, $receivedSig)) {
            Log::warning('WordPress SSO: firma inválida', ['ip' => $request->ip()]);
            return $this->fail('Token inválido.');
        }

        // Decodificar payload
        $data = json_decode(base64_decode(strtr($payload, '-_', '+/')), true);

        if (! $data || empty($data['email']) || empty($data['ts'])) {
            return $this->fail('Payload malformado.');
        }

        // Verificar expiración
        if (time() - (int) $data['ts'] > self::TTL) {
            return $this->fail('Token expirado. Vuelve a hacer clic en "Decorador Virtual" desde WordPress.');
        }

        // Buscar o crear el usuario admin en Laravel
        $user = User::firstOrCreate(
            ['email' => $data['email']],
            [
                'name'     => $data['name'] ?? 'Admin',
                'password' => bcrypt(bin2hex(random_bytes(16))), // contraseña aleatoria
            ]
        );

        // Autenticar en el guard de Backpack
        Auth::guard(backpack_guard_name())->login($user, remember: true);

        Log::info('WordPress SSO: login exitoso', ['email' => $data['email'], 'ip' => $request->ip()]);

        return redirect('/admin/builder');
    }

    private function fail(string $message): RedirectResponse
    {
        return redirect(backpack_url('login'))->withErrors(['sso' => $message]);
    }
}
