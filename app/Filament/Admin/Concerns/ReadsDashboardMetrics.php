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

        if (is_string($fromRaw) && $fromRaw !== '') {
            $from = CarbonImmutable::parse($fromRaw)->startOfDay();
        }

        if (is_string($toRaw) && $toRaw !== '') {
            $to = CarbonImmutable::parse($toRaw)->startOfDay();
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
}
