<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\ServiceTypes;

use App\Filament\Admin\Resources\ServiceTypes\Pages\CreateServiceType;
use App\Filament\Admin\Resources\ServiceTypes\Pages\EditServiceType;
use App\Filament\Admin\Resources\ServiceTypes\Pages\ListServiceTypes;
use App\Filament\Admin\Resources\ServiceTypes\Schemas\ServiceTypeForm;
use App\Filament\Admin\Resources\ServiceTypes\Tables\ServiceTypesTable;
use App\Models\ServiceType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

final class ServiceTypeResource extends Resource
{
    protected static ?string $model = ServiceType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;

    protected static ?string $navigationLabel = 'Servicios';

    protected static ?string $modelLabel = 'servicio';

    protected static ?string $pluralModelLabel = 'servicios';

    protected static string|UnitEnum|null $navigationGroup = 'Catálogo';

    protected static ?int $navigationSort = 40;

    public static function form(Schema $schema): Schema
    {
        return ServiceTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ServiceTypesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServiceTypes::route('/'),
            'create' => CreateServiceType::route('/create'),
            'edit' => EditServiceType::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        abort_unless(auth()->user()?->can('viewAny', ServiceType::class) ?? false, 403);

        return parent::getEloquentQuery();
    }
}
