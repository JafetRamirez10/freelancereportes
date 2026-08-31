<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Clients\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

final class ClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
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
            ]);
    }
}
