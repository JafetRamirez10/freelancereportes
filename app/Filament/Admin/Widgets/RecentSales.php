<?php

declare(strict_types=1);

namespace App\Filament\Admin\Widgets;

use App\Filament\Admin\Concerns\DoesNotPoll;
use App\Filament\Admin\Concerns\ReadsDashboardMetrics;
use App\Models\Sale;
use App\Support\Money;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Collection;

final class RecentSales extends TableWidget
{
    use DoesNotPoll;
    use ReadsDashboardMetrics;

    protected static bool $isLazy = false;

    protected static ?string $heading = 'Ventas recientes';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 3;

    public function table(Table $table): Table
    {
        return $table
            ->records(fn (): Collection => $this->recentSales())
            ->columns([
                TextColumn::make('sold_at')
                    ->label('Fecha')
                    ->date('d/m/Y'),
                TextColumn::make('client.name')
                    ->label('Cliente'),
                TextColumn::make('serviceType.name')
                    ->label('Servicio'),
                TextColumn::make('amount')
                    ->label('Monto')
                    ->formatStateUsing(fn (mixed $state): string => Money::formatUsd((string) $state)),
            ])
            ->paginated(false);
    }

    /**
     * @return Collection<int, Sale>
     */
    private function recentSales(): Collection
    {
        [$from, $to] = $this->dashboardScope();
        $limit = (int) config('freelancer.dashboard.recent_sales_limit', 10);

        return Sale::query()
            ->with(['client', 'serviceType'])
            ->whereBetween('sold_at', [$from->toDateString(), $to->toDateString()])
            ->orderByDesc('sold_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }
}
