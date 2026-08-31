<?php

declare(strict_types=1);

use App\Support\Money;

it('parsea montos con miles', function () {
    expect(Money::parse('1,250.50'))->toBe('1250.50');
});

it('parsea montos con símbolo de dólar', function () {
    expect(Money::parse('$80.00'))->toBe('80.00');
});

it('formatea USD', function () {
    expect(Money::formatUsd('1500.5'))->toBe('$1,500.50');
});

it('rechaza montos negativos', function () {
    expect(fn () => Money::parse('-10'))->toThrow(InvalidArgumentException::class);
});
