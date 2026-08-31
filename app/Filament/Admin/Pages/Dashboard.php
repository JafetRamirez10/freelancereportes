<?php

declare(strict_types=1);

namespace App\Filament\Admin\Pages;

use App\Services\DashboardMetrics;
use Carbon\CarbonImmutable;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    protected string $view = 'filament.admin.pages.dashboard';

    protected static ?string $title = 'Dashboard';

    protected static ?string $navigationLabel = 'Inicio';

    public function getFiltersForm(): Schema
    {
        if ((! $this->isCachingSchemas) && $this->hasCachedSchema('filtersForm')) {
            return $this->getSchema('filtersForm');
        }

        $schema = $this->makeSchema()
            ->columns([
                'md' => 2,
                'xl' => 2,
            ])
            ->extraAttributes(['wire:partial' => 'table-filters-form'])
            ->live(debounce: 500)
            ->statePath('filters');

        return $this->filtersForm($schema);
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

    public function getWidgetsContentComponent(): Component
    {
        return Grid::make($this->getColumns())
            ->extraAttributes([
                'class' => 'fl-dashboard-widgets',
            ])
            ->schema(fn (): array => $this->getWidgetsSchemaComponents($this->getWidgets()));
    }

    public function updatedFilters(): void
    {
        if (is_array($this->filters)) {
            foreach (['from', 'to'] as $key) {
                $normalized = $this->dateOnly($this->filters[$key] ?? null);
                if ($normalized !== ($this->filters[$key] ?? null)) {
                    $this->filters[$key] = $normalized;
                }
            }
        }

        if ($this->persistsFiltersInSession()) {
            session()->put($this->getFiltersSessionKey(), $this->filters);
        }
    }

    private function dateOnly(mixed $value): ?string
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        if (preg_match('/^(\d{4}-\d{2}-\d{2})/', $value, $matches) === 1) {
            return $matches[1];
        }

        try {
            return CarbonImmutable::parse($value)->toDateString();
        } catch (\Throwable) {
            return $value;
        }
    }
}
