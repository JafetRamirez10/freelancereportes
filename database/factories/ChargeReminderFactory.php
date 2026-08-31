<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ReminderKind;
use App\Models\ChargeReminder;
use App\Models\Sale;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChargeReminder>
 */
class ChargeReminderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sale_id' => Sale::factory()->recurring(),
            'kind' => ReminderKind::Week,
            'for_charge_on' => now()->addWeek()->toDateString(),
            'sent_at' => now(),
        ];
    }
}
