<?php

declare(strict_types=1);

namespace App\Enums;

enum ReminderKind: string
{
    case Month = 'month';
    case Week = 'week';

    public function label(): string
    {
        return match ($this) {
            self::Month => 'Un mes',
            self::Week => 'Una semana',
        };
    }

    public function daysAhead(): int
    {
        return match ($this) {
            self::Month => (int) config('freelancer.reminders.month_days', 30),
            self::Week => (int) config('freelancer.reminders.week_days', 7),
        };
    }
}
