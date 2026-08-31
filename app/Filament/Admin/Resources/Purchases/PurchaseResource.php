<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Purchases;

use App\Filament\Admin\Resources\Purchases\Pages\CreatePurchase;
use App\Filament\Admin\Resources\Purchases\Pages\EditPurchase;
use App\Filament\Admin\Resources\Purchases\Pages\ListPurchases;
use App\Filament\Admin\Resources\Purchases\Schemas\PurchaseForm;
use App\Filament\Admin\Resources\Purchases\Tables\PurchasesTable;
use App\Models\Purchase;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

final class PurchaseResource extends Resource
{
    protected static ?string $model = Purchase::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;

    protected static ?string $navigationLabel = 'Compras';

    protected static ?string $modelLabel = 'compra';

    protected static ?string $pluralModelLabel = 'compras';

    protected static string|UnitEnum|null $navigationGroup = 'Operación';

    protected static ?int $navigationSort = 20;

    public static function form(Schema $schema): Schema
    {
        return PurchaseForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PurchasesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPurchases::route('/'),
            'create' => CreatePurchase::route('/create'),
            'edit' => EditPurchase::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        abort_unless(auth()->user()?->can('viewAny', Purchase::class) ?? false, 403);

        return parent::getEloquentQuery();
    }
}
