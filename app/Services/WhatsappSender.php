<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WhatsappSender
{
    protected string $token;
    protected string $phoneNumberId;

    public function __construct()
    {
        $this->token = config('services.whatsapp.token');
        $this->phoneNumberId = config('services.whatsapp.phone_number_id');
    }

    public function sendTemplate(string $to, string $template, string $lang = 'es_AR', ?array $params = null): array
    {
        $url = "https://graph.facebook.com/v20.0/{$this->phoneNumberId}/messages";

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'template',
            'template' => [
                'name' => $template,
                'language' => [
                    'code' => $lang,
                ],
            ],
        ];

        if (!empty($params)) {
            $payload['template']['components'] = [
                [
                    'type' => 'body',
                    'parameters' => array_map(function ($value) {
                        return [
                            'type' => 'text',
                            'text' => (string) $value,
                        ];
                    }, $params),
                ],
            ];
        }

        try {
            $response = Http::withToken($this->token)
                ->acceptJson()
                ->post($url, $payload);

            return [
                'ok' => $response->successful(),
                'status' => $response->status(),
                'json' => $response->json(),
                'exception' => null,
            ];
        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'status' => 0,
                'json' => null,
                'exception' => $e->getMessage(),
            ];
        }
    }
}