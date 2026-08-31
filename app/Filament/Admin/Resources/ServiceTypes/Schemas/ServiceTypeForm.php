<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\ServiceTypes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

final class ServiceTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(150),
                TextInput::make('default_amount')
                    ->label('Costo por defecto (USD)')
                    ->numeric()
                    ->prefix('$')
                    ->required()
                    ->minValue(0)
                    ->step('0.01'),
                Toggle::make('is_active')
                    ->label('Activo')
                    ->default(true),
            ]);
    }
}
