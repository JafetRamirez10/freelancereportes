<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Sales\Schemas;

use App\Actions\StoreClient;
use App\Enums\RecurrenceInterval;
use App\Models\ServiceType;
use Carbon\CarbonImmutable;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

final class SaleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('client_id')
                    ->label('Cliente')
                    ->relationship('client', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->createOptionForm([
                        TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(150),
                        TextInput::make('phone')
                            ->label('Teléfono')
                            ->tel()
                            ->maxLength(30),
                        TextInput::make('email')
                            ->label('Correo')
                            ->email()
                            ->maxLength(150),
                    ])
                    ->createOptionUsing(function (array $data): int {
                        return app(StoreClient::class)->execute($data)->getKey();
                    }),
                Select::make('service_type_id')
                    ->label('Servicio')
                    ->relationship(
                        'serviceType',
                        'name',
                        fn ($query) => $query->orderBy('name'),
                    )
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (mixed $state, Set $set): void {
                        if ($state === null) {
                            return;
                        }

                        $type = ServiceType::query()->find($state);

                        if ($type !== null) {
                            $set('amount', $type->default_amount);
                        }
                    }),
                DatePicker::make('sold_at')
                    ->label('Fecha de venta')
                    ->default(now()->toDateString())
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->format('Y-m-d')
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Set $set, Get $get) => self::syncNextCharge($set, $get)),
                TextInput::make('amount')
                    ->label('Monto (USD)')
                    ->numeric()
                    ->prefix('$')
                    ->required()
                    ->minValue(0)
                    ->step('0.01'),
                Toggle::make('is_recurring')
                    ->label('Cobro recurrente')
                    ->live()
                    ->afterStateUpdated(function (mixed $state, Set $set, Get $get): void {
                        if (! $state) {
                            $set('recurrence_interval', null);
                            $set('next_charge_at', null);

                            return;
                        }

                        self::syncNextCharge($set, $get);
                    }),
                Select::make('recurrence_interval')
                    ->label('Intervalo')
                    ->options(RecurrenceInterval::options())
                    ->visible(fn (Get $get): bool => (bool) $get('is_recurring'))
                    ->required(fn (Get $get): bool => (bool) $get('is_recurring'))
                    ->live()
                    ->afterStateUpdated(fn (Set $set, Get $get) => self::syncNextCharge($set, $get)),
                DatePicker::make('next_charge_at')
                    ->label('Próximo cobro')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->format('Y-m-d')
                    ->visible(fn (Get $get): bool => (bool) $get('is_recurring'))
                    ->required(fn (Get $get): bool => (bool) $get('is_recurring')),
                FileUpload::make('evidence')
                    ->label('Evidencia (opcional)')
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'application/pdf'])
                    ->maxSize(5120)
                    ->storeFiles(false),
                Textarea::make('notes')
                    ->label('Notas')
                    ->rows(3)
                    ->maxLength(2000)
                    ->columnSpanFull(),
            ]);
    }

    private static function syncNextCharge(Set $set, Get $get): void
    {
        if (! $get('is_recurring')) {
            return;
        }

        $soldAt = $get('sold_at');
        $interval = $get('recurrence_interval');

        if (! is_string($soldAt) || $soldAt === '' || ! is_string($interval) || $interval === '') {
            return;
        }

        $enum = RecurrenceInterval::tryFrom($interval);

        if ($enum === null) {
            return;
        }

        $set('next_charge_at', $enum->nextChargeOn(CarbonImmutable::parse($soldAt))->toDateString());
    }
}
