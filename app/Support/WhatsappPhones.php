<?php

namespace App\Support;

/**
 * Normalización y recolección de teléfonos de morosos para WhatsApp/Meta.
 *
 * Centraliza la lógica que estaba duplicada en EnviarAvisosMoraWhatsapp,
 * SendWhatsappReminders, MorososController y WhatsappBotController.
 */
class WhatsappPhones
{
    /**
     * Normaliza un teléfono al formato esperado por Meta.
     * - Quita no-dígitos.
     * - Convierte 549... (celular AR con 9) a 54... (formato aceptado por Meta).
     * - Quita 0 inicial local.
     * - Agrega prefijo 54 si falta.
     */
    public static function normalizar(?string $telefono): ?string
    {
        if ($telefono === null || trim($telefono) === '') {
            return null;
        }

        $digitos = preg_replace('/\D+/', '', $telefono) ?? '';

        if ($digitos === '') {
            return null;
        }

        if (str_starts_with($digitos, '549')) {
            return '54' . substr($digitos, 3);
        }

        if (str_starts_with($digitos, '0')) {
            $digitos = substr($digitos, 1);
        }

        if (!str_starts_with($digitos, '54')) {
            $digitos = '54' . $digitos;
        }

        return $digitos;
    }

    /**
     * Reúne todos los teléfonos válidos de una fila de morosos
     * (TEL_MOVIL1..3, TEL_ALTER1..2), aceptando múltiples números
     * separados por coma, punto y coma, pipe o barra.
     *
     * @return list<string>
     */
    public static function recolectar(object|array $moroso): array
    {
        $get = fn ($key) => is_array($moroso)
            ? ($moroso[$key] ?? '')
            : ($moroso->{$key} ?? '');

        $fuentes = [
            (string) $get('TEL_MOVIL1'),
            (string) $get('TEL_MOVIL2'),
            (string) $get('TEL_MOVIL3'),
            (string) $get('TEL_ALTER1'),
            (string) $get('TEL_ALTER2'),
        ];

        $telefonos = [];
        foreach ($fuentes as $fuente) {
            if (trim($fuente) === '') {
                continue;
            }
            foreach (preg_split('/[,\;|\/]+/', $fuente) as $parte) {
                $normalizado = self::normalizar($parte);
                if ($normalizado !== null && $normalizado !== '') {
                    $telefonos[] = $normalizado;
                }
            }
        }

        return array_values(array_unique($telefonos));
    }

    /**
     * Primer teléfono válido de la fila (para avisos por rango de mora).
     */
    public static function primero(object|array $moroso): ?string
    {
        $todos = self::recolectar($moroso);

        return $todos[0] ?? null;
    }
}
