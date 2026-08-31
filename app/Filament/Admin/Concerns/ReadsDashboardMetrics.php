<?php

declare(strict_types=1);

namespace App\Filament\Admin\Concerns;

use App\Services\DashboardMetrics;
use Carbon\CarbonImmutable;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

trait ReadsDashboardMetrics
{
    use InteractsWithPageFilters;

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    protected function dashboardScope(): array
    {
        [$from, $to] = DashboardMetrics::defaultRange();

        $fromRaw = $this->pageFilters['from'] ?? null;
        $toRaw = $this->pageFilters['to'] ?? null;

        $parsedFrom = $this->parseDate($fromRaw);
        $parsedTo = $this->parseDate($toRaw);

        if ($parsedFrom !== null) {
            $from = $parsedFrom;
        }

        if ($parsedTo !== null) {
            $to = $parsedTo;
        }

        if ($from->gt($to)) {
            [$from, $to] = [$to, $from];
        }

        return [$from, $to];
    }

    protected function metrics(): DashboardMetrics
    {
        [$from, $to] = $this->dashboardScope();

        return new DashboardMetrics($from, $to);
    }

    private function parseDate(mixed $value): ?CarbonImmutable
    {
        if ($value instanceof \DateTimeInterface) {
            return CarbonImmutable::instance(\DateTimeImmutable::createFromInterface($value))->startOfDay();
        }

        if (! is_string($value) || $value === '') {
            return null;
        }

        try {
            return CarbonImmutable::parse($value)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }
}
