<?php

namespace App\Support;

use Illuminate\Support\Facades\File;

class WhatsappAutomationSettings
{
    public static function path(): string
    {
        $path = (string) config('services.whatsapp.auto_settings_path');
        return $path !== '' ? $path : storage_path('app/whatsapp_automation.json');
    }

    /**
     * @return array{enabled:bool,day_of_month:int,time:string}
     */
    public static function read(): array
    {
        $defaults = [
            'enabled' => (bool) config('services.whatsapp.auto_enabled', false),
            'day_of_month' => (int) config('services.whatsapp.auto_day_of_month', 1),
            'time' => (string) config('services.whatsapp.auto_time', '09:00'),
        ];

        $path = self::path();
        if (!is_file($path)) {
            return $defaults;
        }

        $raw = File::get($path);
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            return $defaults;
        }

        return [
            'enabled' => array_key_exists('enabled', $data) ? (bool) $data['enabled'] : $defaults['enabled'],
            'day_of_month' => isset($data['day_of_month']) ? (int) $data['day_of_month'] : $defaults['day_of_month'],
            'time' => isset($data['time']) ? (string) $data['time'] : $defaults['time'],
        ];
    }

    /**
     * @param  array{enabled:bool,day_of_month:int,time:string}  $data
     */
    public static function write(array $data): void
    {
        $path = self::path();
        File::ensureDirectoryExists(dirname($path));

        File::put($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
