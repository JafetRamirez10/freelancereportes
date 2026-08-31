<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Purchases\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

final class PurchaseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('purchased_at')
                    ->label('Fecha')
                    ->default(now()->toDateString())
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->format('Y-m-d')
                    ->required(),
                TextInput::make('amount')
                    ->label('Precio (USD)')
                    ->numeric()
                    ->prefix('$')
                    ->required()
                    ->minValue(0)
                    ->step('0.01'),
                TextInput::make('description')
                    ->label('Descripción')
                    ->maxLength(255),
                FileUpload::make('evidence')
                    ->label('Evidencia (opcional)')
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'application/pdf'])
                    ->maxSize(5120)
                    ->storeFiles(false),
            ]);
    }
}
