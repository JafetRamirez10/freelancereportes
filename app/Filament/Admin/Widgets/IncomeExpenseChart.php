<?php

declare(strict_types=1);

namespace App\Filament\Admin\Widgets;

use App\Filament\Admin\Concerns\DoesNotPoll;
use App\Filament\Admin\Concerns\ReadsDashboardMetrics;
use Filament\Widgets\ChartWidget;

final class IncomeExpenseChart extends ChartWidget
{
    use DoesNotPoll;
    use ReadsDashboardMetrics;

    protected ?string $heading = 'Ingresos vs gastos';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $series = $this->metrics()->all()['series'];

        return [
            'datasets' => [
                [
                    'label' => 'Ingresos (USD)',
                    'data' => $series['income'],
                    'borderColor' => '#0f766e',
                    'backgroundColor' => 'rgba(15, 118, 110, 0.15)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Gastos (USD)',
                    'data' => $series['expense'],
                    'borderColor' => '#b45309',
                    'backgroundColor' => 'rgba(180, 83, 9, 0.12)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $series['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
