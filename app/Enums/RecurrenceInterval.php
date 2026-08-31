<?php

declare(strict_types=1);

namespace App\Enums;

use Carbon\CarbonImmutable;

enum RecurrenceInterval: string
{
    case Monthly = 'monthly';
    case Yearly = 'yearly';

    public function label(): string
    {
        return match ($this) {
            self::Monthly => 'Mensual',
            self::Yearly => 'Anual',
        };
    }

    public function nextChargeOn(CarbonImmutable $soldAt): CarbonImmutable
    {
        return match ($this) {
            self::Monthly => $soldAt->addMonth(),
            self::Yearly => $soldAt->addYear(),
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            self::Monthly->value => self::Monthly->label(),
            self::Yearly->value => self::Yearly->label(),
        ];
    }
}
