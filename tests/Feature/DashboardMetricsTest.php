<?php

declare(strict_types=1);

use App\Models\Purchase;
use App\Models\Sale;
use App\Services\DashboardMetrics;
use Carbon\CarbonImmutable;

it('calcula ingresos, gastos y neto del mes actual', function () {
    $now = CarbonImmutable::now();
    [$from, $to] = DashboardMetrics::defaultRange();

    expect($from->toDateString())->toBe($now->startOfMonth()->toDateString())
        ->and($to->toDateString())->toBe($now->endOfMonth()->toDateString());

    Sale::factory()->create([
        'sold_at' => $now->toDateString(),
        'amount' => '200.00',
    ]);
    Sale::factory()->create([
        'sold_at' => $now->subMonth()->toDateString(),
        'amount' => '999.00',
    ]);
    Purchase::factory()->create([
        'purchased_at' => $now->toDateString(),
        'amount' => '50.50',
    ]);

    $data = (new DashboardMetrics($from, $to))->all();

    expect($data['income'])->toBe('200.00')
        ->and($data['expense'])->toBe('50.50')
        ->and($data['net'])->toBe('149.50');
});
