<?php

declare(strict_types=1);

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Widgets\IncomeExpenseChart;
use App\Filament\Admin\Widgets\KpiOverview;
use App\Filament\Admin\Widgets\RecentSales;
use App\Services\DashboardMetrics;
use Carbon\CarbonImmutable;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Widgets\Widget;
use Filament\Widgets\WidgetConfiguration;

final class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    protected static ?string $title = 'Dashboard';

    protected static ?string $navigationLabel = 'Inicio';

    public function mount(): void
    {
        $this->filters = $this->normalizedFilters($this->filters);
    }

    public function booted(): void
    {
        $this->filters = $this->normalizedFilters($this->filters);
    }

    /**
     * @return array<class-string<Widget> | WidgetConfiguration>
     */
    public function getWidgets(): array
    {
        return [
            KpiOverview::class,
            IncomeExpenseChart::class,
            RecentSales::class,
        ];
    }

    public function filtersForm(Schema $schema): Schema
    {
        [$from, $to] = DashboardMetrics::defaultRange();

        return $schema
            ->components([
                Section::make('Período')
                    ->schema([
                        DatePicker::make('from')
                            ->label('Desde')
                            ->default($from->toDateString())
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->format('Y-m-d')
                            ->required(),
                        DatePicker::make('to')
                            ->label('Hasta')
                            ->default($to->toDateString())
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->format('Y-m-d')
                            ->required(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }

    public function updatedFilters(): void
    {
        $this->filters = $this->normalizedFilters($this->filters);

        if ($this->persistsFiltersInSession()) {
            session()->put($this->getFiltersSessionKey(), $this->filters);
        }
    }

    /**
     * @param  array<string, mixed>|null  $filters
     * @return array{from: string, to: string}
     */
    private function normalizedFilters(?array $filters): array
    {
        [$from, $to] = DashboardMetrics::defaultRange();

        $fromStr = $this->dateOnly($filters['from'] ?? null) ?? $from->toDateString();
        $toStr = $this->dateOnly($filters['to'] ?? null) ?? $to->toDateString();

        if ($fromStr > $toStr) {
            [$fromStr, $toStr] = [$toStr, $fromStr];
        }

        return [
            'from' => $fromStr,
            'to' => $toStr,
        ];
    }

    private function dateOnly(mixed $value): ?string
    {
        if ($value instanceof \DateTimeInterface) {
            return CarbonImmutable::instance(\DateTimeImmutable::createFromInterface($value))->toDateString();
        }

        if (! is_string($value) || $value === '') {
            return null;
        }

        if (preg_match('/^(\d{4}-\d{2}-\d{2})/', $value, $matches) === 1) {
            return $matches[1];
        }

        try {
            return CarbonImmutable::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }
}
