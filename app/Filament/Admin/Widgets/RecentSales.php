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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class RecentSales extends TableWidget
{
    use DoesNotPoll;
    use ReadsDashboardMetrics;

    protected static ?string $heading = 'Ventas recientes';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 3;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => $this->recentQuery())
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
                    ->formatStateUsing(fn (string $state): string => Money::formatUsd($state)),
            ])
            ->paginated(false);
    }

    /**
     * @return Builder<Sale>
     */
    private function recentQuery(): Builder
    {
        $data = $this->metrics()->all();

        /** @var Collection<int, Sale> $recent */
        $recent = $data['recent'];
        $ids = $recent->pluck('id')->all();

        return Sale::query()
            ->with(['client', 'serviceType'])
            ->whereIn('id', $ids === [] ? [0] : $ids)
            ->orderByDesc('sold_at')
            ->orderByDesc('id');
    }
}
