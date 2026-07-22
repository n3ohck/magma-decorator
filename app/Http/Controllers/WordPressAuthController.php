<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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

        // Verificar expiración (el ts va firmado, no se puede alterar sin el secreto)
        $age = time() - (int) $data['ts'];

        if ($age > self::TTL || $age < -self::TTL) {
            return $this->fail('Token expirado. Vuelve a hacer clic en "Decorador Virtual" desde WordPress.');
        }

        // Anti-replay: cada token sirve UNA sola vez. Sin esto, un token capturado
        // (logs, historial, referer) se puede reusar durante toda su ventana de validez.
        $tokenKey = 'wp_sso_used:' . hash('sha256', $raw);

        if (! Cache::add($tokenKey, true, self::TTL + 10)) {
            Log::warning('WordPress SSO: token reutilizado', ['ip' => $request->ip()]);
            return $this->fail('Este enlace ya fue usado. Genera uno nuevo desde WordPress.');
        }

        $email = strtolower(trim($data['email']));
        $allowed = config('services.wordpress_sso.allowed_emails', []);

        // Sólo se permiten correos de la lista blanca. Si no hay lista configurada,
        // se admite únicamente a usuarios YA existentes: el SSO nunca crea cuentas
        // arbitrarias. Así, aun con el secreto filtrado, no se puede fabricar un admin.
        if (! empty($allowed)) {
            if (! in_array($email, array_map('strtolower', $allowed), true)) {
                Log::warning('WordPress SSO: correo no autorizado', [
                    'email' => $email,
                    'ip'    => $request->ip(),
                ]);
                return $this->fail('Esta cuenta no está autorizada para el panel.');
            }

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name'     => $data['name'] ?? 'Admin',
                    'password' => bcrypt(bin2hex(random_bytes(16))), // contraseña aleatoria
                ]
            );
        } else {
            $user = User::where('email', $email)->first();

            if (! $user) {
                Log::warning('WordPress SSO: usuario inexistente y sin lista blanca', [
                    'email' => $email,
                    'ip'    => $request->ip(),
                ]);
                return $this->fail('Esta cuenta no está autorizada para el panel.');
            }
        }

        // Autenticar en el guard de Backpack
        Auth::guard(backpack_guard_name())->login($user, remember: true);

        Log::info('WordPress SSO: login exitoso', ['email' => $email, 'ip' => $request->ip()]);

        return redirect('/admin/builder');
    }

    private function fail(string $message): RedirectResponse
    {
        return redirect(backpack_url('login'))->withErrors(['sso' => $message]);
    }
}
