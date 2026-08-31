<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\RecurrenceInterval;
use App\Models\Client;
use App\Models\Sale;
use App\Models\ServiceType;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sale>
 */
class SaleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'client_id' => Client::factory(),
            'service_type_id' => ServiceType::factory(),
            'sold_at' => now()->toDateString(),
            'amount' => '150.00',
            'is_recurring' => false,
            'recurrence_interval' => null,
            'next_charge_at' => null,
            'notes' => null,
        ];
    }

    public function recurring(RecurrenceInterval $interval = RecurrenceInterval::Monthly): static
    {
        return $this->state(function (array $attrs) use ($interval): array {
            $soldAt = $attrs['sold_at'] ?? now()->toDateString();

            return [
                'is_recurring' => true,
                'recurrence_interval' => $interval,
                'next_charge_at' => $interval->nextChargeOn(
                    CarbonImmutable::parse((string) $soldAt),
                )->toDateString(),
            ];
        });
    }
}
