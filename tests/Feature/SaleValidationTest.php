<?php

declare(strict_types=1);

use App\Actions\StoreClient;
use App\Actions\StorePurchase;
use App\Actions\StoreSale;
use App\Enums\RecurrenceInterval;
use App\Models\Client;
use App\Models\ServiceType;
use App\Models\User;
use Illuminate\Validation\ValidationException;

it('rechaza una venta sin cliente, servicio o monto', function () {
    $user = User::factory()->create();

    expect(fn () => app(StoreSale::class)->execute($user, [
        'sold_at' => now()->toDateString(),
    ]))->toThrow(ValidationException::class);
});

it('rechaza un cliente sin nombre', function () {
    $user = User::factory()->create();

    expect(fn () => app(StoreClient::class)->execute([
        'phone' => '88888888',
        'email' => 'a@example.com',
    ], $user))->toThrow(ValidationException::class);
});

it('rechaza un correo de cliente inválido', function () {
    $user = User::factory()->create();

    expect(fn () => app(StoreClient::class)->execute([
        'name' => 'Ana',
        'email' => 'no-es-correo',
    ], $user))->toThrow(ValidationException::class);
});

it('rechaza una compra sin fecha o monto', function () {
    $user = User::factory()->create();

    expect(fn () => app(StorePurchase::class)->execute($user, [
        'description' => 'Hosting',
    ]))->toThrow(ValidationException::class);
});

it('calcula el próximo cobro mensual desde sold_at', function () {
    $user = User::factory()->create();
    $client = Client::factory()->create();
    $type = ServiceType::factory()->create();

    $sale = app(StoreSale::class)->execute($user, [
        'client_id' => $client->id,
        'service_type_id' => $type->id,
        'sold_at' => '2026-01-15',
        'amount' => '80.00',
        'is_recurring' => true,
        'recurrence_interval' => RecurrenceInterval::Monthly->value,
    ]);

    expect($sale->next_charge_at?->toDateString())->toBe('2026-02-15')
        ->and($sale->recurrence_interval)->toBe(RecurrenceInterval::Monthly);
});

it('calcula el próximo cobro anual desde sold_at', function () {
    $user = User::factory()->create();
    $client = Client::factory()->create();
    $type = ServiceType::factory()->create();

    $sale = app(StoreSale::class)->execute($user, [
        'client_id' => $client->id,
        'service_type_id' => $type->id,
        'sold_at' => '2026-01-15',
        'amount' => '80.00',
        'is_recurring' => true,
        'recurrence_interval' => RecurrenceInterval::Yearly->value,
    ]);

    expect($sale->next_charge_at?->toDateString())->toBe('2027-01-15');
});

it('permite registrar una venta con fecha anterior', function () {
    $user = User::factory()->create();
    $client = Client::factory()->create();
    $type = ServiceType::factory()->create();

    $sale = app(StoreSale::class)->execute($user, [
        'client_id' => $client->id,
        'service_type_id' => $type->id,
        'sold_at' => '2024-03-01',
        'amount' => '40',
    ]);

    expect($sale->sold_at->toDateString())->toBe('2024-03-01')
        ->and($sale->is_recurring)->toBeFalse()
        ->and($sale->next_charge_at)->toBeNull();
});
