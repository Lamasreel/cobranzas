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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'whatsapp' => [
        'token' => env('WHATSAPP_ACCESS_TOKEN'),
        // De tu ejemplo de cURL: /v25.0/{phone_number_id}/messages
        'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID', '1069808222884117'),
        // Número de prueba (sin +)
        'to' => env('WHATSAPP_TEST_TO', '5493865250447'),
        'template' => env('WHATSAPP_TEMPLATE', 'hello_world'),
        'lang' => env('WHATSAPP_TEMPLATE_LANG', 'en_US'),

        // Recordatorios automáticos (Scheduler)
        'auto_enabled' => env('WHATSAPP_AUTO_ENABLED', false),
        // Día del mes (1-31) en el que corre el job (además valida la regla de negocio abajo)
        'auto_day_of_month' => (int) env('WHATSAPP_AUTO_DAY_OF_MONTH', 1),
        // Hora local (según app.timezone) en la que corre el scheduler entrypoint
        'auto_time' => env('WHATSAPP_AUTO_TIME', '09:00'),
        // Archivo JSON persistido por la UI (si existe, pisa defaults de .env)
        'auto_settings_path' => env('WHATSAPP_AUTO_SETTINGS_PATH') ?: storage_path('app/whatsapp_automation.json'),

        // Regla por defecto: clientes en promesa con fecha_promesa_pago = hoy
        'auto_estado_promesa_id' => (int) env('WHATSAPP_AUTO_ESTADO_PROMESA_ID', 3),
        'auto_template' => env('WHATSAPP_AUTO_TEMPLATE', env('WHATSAPP_TEMPLATE', 'hello_world')),
        'auto_template_lang' => env('WHATSAPP_AUTO_TEMPLATE_LANG', env('WHATSAPP_TEMPLATE_LANG', 'en_US')),
        // JSON opcional con "components" del template (si tu template tiene variables/botón URL dinámico)
        'auto_template_components_json' => env('WHATSAPP_AUTO_TEMPLATE_COMPONENTS_JSON', ''),
    ],

];
