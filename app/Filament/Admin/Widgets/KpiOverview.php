<?php

declare(strict_types=1);

namespace App\Filament\Admin\Widgets;

use App\Filament\Admin\Concerns\DoesNotPoll;
use App\Filament\Admin\Concerns\ReadsDashboardMetrics;
use App\Support\Money;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

final class KpiOverview extends StatsOverviewWidget
{
    use DoesNotPoll;
    use ReadsDashboardMetrics;

    protected static bool $isLazy = false;

    protected ?string $heading = 'Resumen del período';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 1;

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $data = $this->metrics()->all();

        return [
            Stat::make('Ingresos', Money::formatUsd((string) $data['income'])),
            Stat::make('Gastos', Money::formatUsd((string) $data['expense'])),
            Stat::make('Ganancia neta', Money::formatUsd((string) $data['net'])),
        ];
    }
}
