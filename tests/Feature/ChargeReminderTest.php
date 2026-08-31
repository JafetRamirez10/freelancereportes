<?php

declare(strict_types=1);

use App\Actions\SendChargeReminder;
use App\Enums\RecurrenceInterval;
use App\Enums\ReminderKind;
use App\Mail\ChargeReminderMail;
use App\Models\ChargeReminder;
use App\Models\Sale;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Mail;

it('envía avisos a 30 y 7 días y no duplica el mismo kind y fecha', function () {
    Mail::fake();
    config(['freelancer.admin_email' => 'admin@example.com']);

    $today = CarbonImmutable::now()->startOfDay();
    $week = Sale::factory()->recurring(RecurrenceInterval::Monthly)->create([
        'sold_at' => $today->subMonth()->toDateString(),
        'next_charge_at' => $today->addDays(7)->toDateString(),
    ]);
    $month = Sale::factory()->recurring(RecurrenceInterval::Yearly)->create([
        'sold_at' => $today->subYear()->toDateString(),
        'next_charge_at' => $today->addDays(30)->toDateString(),
    ]);
    Sale::factory()->recurring()->create([
        'next_charge_at' => $today->addDays(3)->toDateString(),
    ]);

    $this->artisan('charges:send-reminders')->assertSuccessful();
    $this->artisan('charges:send-reminders')->assertSuccessful();

    Mail::assertSent(ChargeReminderMail::class, 2);
    expect(ChargeReminder::query()->count())->toBe(2)
        ->and(ChargeReminder::query()->where('sale_id', $week->id)->where('kind', ReminderKind::Week)->exists())->toBeTrue()
        ->and(ChargeReminder::query()->where('sale_id', $month->id)->where('kind', ReminderKind::Month)->exists())->toBeTrue();
});

it('no envía si el aviso ya está registrado', function () {
    Mail::fake();
    config(['freelancer.admin_email' => 'admin@example.com']);

    $sale = Sale::factory()->recurring()->create([
        'next_charge_at' => CarbonImmutable::now()->addDays(7)->toDateString(),
    ]);

    ChargeReminder::factory()->create([
        'sale_id' => $sale->id,
        'kind' => ReminderKind::Week,
        'for_charge_on' => $sale->next_charge_at->toDateString(),
    ]);

    expect(app(SendChargeReminder::class)->execute($sale, ReminderKind::Week))->toBeFalse();
    Mail::assertNothingSent();
});
