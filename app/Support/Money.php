<?php

declare(strict_types=1);

namespace App\Support;

use InvalidArgumentException;

final class Money
{
    public static function parse(mixed $raw): string
    {
        if ($raw === null || $raw === '') {
            return '0.00';
        }

        if (is_int($raw) || is_float($raw)) {
            return number_format((float) $raw, 2, '.', '');
        }

        $value = trim((string) $raw);
        $value = str_replace(['₡', '$', ' '], '', $value);

        if (str_contains($value, ',') && str_contains($value, '.')) {
            $value = str_replace(',', '', $value);
        } elseif (substr_count($value, ',') === 1 && ! str_contains($value, '.')) {
            $value = str_replace(',', '.', $value);
        } else {
            $value = str_replace(',', '', $value);
        }

        if (! is_numeric($value)) {
            throw new InvalidArgumentException('Monto inválido.');
        }

        if ((float) $value < 0) {
            throw new InvalidArgumentException('Monto inválido.');
        }

        return number_format((float) $value, 2, '.', '');
    }

    public static function formatUsd(string $amount): string
    {
        return '$'.number_format((float) $amount, 2, '.', ',');
    }
}
