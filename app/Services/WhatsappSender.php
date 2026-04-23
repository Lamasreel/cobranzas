<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Throwable;

class WhatsappSender
{
    /**
     * @param  array<string,mixed>|null  $components
     * @return array{ok:bool,status:?int,json:mixed,exception:?string}
     */
    public function sendTemplate(string $to, string $template, string $lang, ?array $components = null): array
    {
        $token = (string) config('services.whatsapp.token');
        $phoneNumberId = (string) config('services.whatsapp.phone_number_id');

        if ($token === '' || $phoneNumberId === '' || $to === '') {
            return [
                'ok' => false,
                'status' => null,
                'json' => null,
                'exception' => 'Faltan credenciales/config de WhatsApp.',
            ];
        }

        $url = "https://graph.facebook.com/v25.0/{$phoneNumberId}/messages";

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'template',
            'template' => [
                'name' => $template,
                'language' => ['code' => $lang],
            ],
        ];

        if (is_array($components) && $components !== []) {
            $payload['template']['components'] = $components;
        }

        try {
            $http = Http::withToken($token)->acceptJson();

            $caBundle = storage_path('cacert.pem');
            if (is_file($caBundle)) {
                $http = $http->withOptions([
                    'verify' => $caBundle,
                ]);
            }

            $resp = $http->asJson()->post($url, $payload);

            return [
                'ok' => $resp->successful(),
                'status' => $resp->status(),
                'json' => $resp->json(),
                'exception' => null,
            ];
        } catch (Throwable $e) {
            return [
                'ok' => false,
                'status' => null,
                'json' => null,
                'exception' => $e->getMessage(),
            ];
        }
    }
}
