<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'wordpress_sso' => [
        // Mismo valor que MAGMA_SSO_SECRET en el plugin de WordPress
        'secret' => env('WORDPRESS_SSO_SECRET'),

        // Lista blanca de correos autorizados a entrar por SSO (separados por coma).
        // Si está vacía, el SSO SOLO deja pasar usuarios que ya existen: nunca crea
        // cuentas nuevas. Evita que quien tenga el secreto se fabrique un admin.
        'allowed_emails' => array_filter(array_map(
            'trim',
            explode(',', (string) env('WORDPRESS_SSO_ALLOWED_EMAILS', ''))
        )),
    ],

    'replicate' => [
        'token'       => env('REPLICATE_API_TOKEN'),
        // SDXL img2img — para el render fotorrealista final
        'render_model'   => env('REPLICATE_RENDER_MODEL', 'stability-ai/sdxl'),
        // SAM 2 — para generación automática de máscaras
        'sam_model'      => env('REPLICATE_SAM_MODEL', 'meta/sam-2'),
    ],

];
