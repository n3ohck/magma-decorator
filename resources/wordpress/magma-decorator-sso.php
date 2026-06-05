<?php
/**
 * Plugin Name: Magma Decorator SSO
 * Description: Permite a los administradores de WordPress acceder al Decorador Virtual sin hacer login doble.
 * Version:     1.0.0
 * Author:      Magma Superficies
 *
 * INSTALACIÓN:
 * 1. Copia este archivo a /wp-content/plugins/magma-decorator-sso/magma-decorator-sso.php
 * 2. Activa el plugin en WP → Plugins
 * 3. El secreto compartido DEBE ser el mismo valor que WORDPRESS_SSO_SECRET en el .env de Laravel
 */

if (! defined('ABSPATH')) exit;

// ─── Configuración ───────────────────────────────────────────────────────────

// Secreto compartido con Laravel. Cámbialo por una cadena aleatoria larga
// y copia el mismo valor en WORDPRESS_SSO_SECRET del .env de Laravel.
defined('MAGMA_SSO_SECRET')    || define('MAGMA_SSO_SECRET',    '1bb0d37444ee9195e7c926b7e94e3b97a72bdccc2c9fa3ef');
defined('MAGMA_DECORATOR_URL') || define('MAGMA_DECORATOR_URL', 'https://decorador.keya-erp.com');
defined('MAGMA_SSO_TTL')       || define('MAGMA_SSO_TTL',       120);

// ─── Admin bar ───────────────────────────────────────────────────────────────

/**
 * Agrega "Ir al Decorador" en la barra de administración de WordPress.
 */
add_action('admin_bar_menu', function (WP_Admin_Bar $bar) {
    if (! current_user_can('manage_options')) return;

    $bar->add_node([
        'id'    => 'magma-decorator',
        'title' => '🎨 Decorador Virtual',
        'href'  => admin_url('admin.php?page=magma-decorator-go'),
        'meta'  => ['title' => 'Ir al Decorador Virtual de Magma'],
    ]);
}, 100);

// ─── Página bounce ───────────────────────────────────────────────────────────

/**
 * Registra una página admin fantasma que solo sirve como punto de
 * generación del token — el usuario nunca la ve, es un redirect inmediato.
 */
add_action('admin_menu', function () {
    add_menu_page(
        'Decorador',
        '🎨 Decorador',
        'manage_options',
        'magma-decorator-go',
        'magma_sso_redirect',
        '',
        3
    );
});

/**
 * Genera el token y redirige al Decorador.
 */
function magma_sso_redirect(): void
{
    if (! current_user_can('manage_options')) {
        wp_die('No tienes permisos para acceder al Decorador.');
    }

    $user    = wp_get_current_user();
    $email   = $user->user_email;
    $ts      = time();

    // Payload: base64url del JSON
    $payload = rtrim(strtr(base64_encode(json_encode([
        'email' => $email,
        'name'  => $user->display_name,
        'ts'    => $ts,
    ])), '+/', '-_'), '=');

    // Firma HMAC-SHA256
    $signature = hash_hmac('sha256', $payload, MAGMA_SSO_SECRET);

    $token    = $payload . '.' . $signature;
    $redirect = MAGMA_DECORATOR_URL . '/admin/auth/wordpress?token=' . urlencode($token);

    wp_redirect($redirect);
    exit;
}
