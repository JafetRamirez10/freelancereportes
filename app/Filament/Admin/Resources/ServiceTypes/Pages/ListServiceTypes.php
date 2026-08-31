<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\ServiceTypes\Pages;

use App\Filament\Admin\Resources\ServiceTypes\ServiceTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

final class ListServiceTypes extends ListRecords
{
    protected static string $resource = ServiceTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
