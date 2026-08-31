<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\ServiceTypes\Pages;

use App\Actions\StoreServiceType;
use App\Filament\Admin\Resources\ServiceTypes\ServiceTypeResource;
use App\Models\ServiceType;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

final class CreateServiceType extends CreateRecord
{
    protected static string $resource = ServiceTypeResource::class;

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Model
    {
        /** @var User $user */
        $user = auth()->user();
        abort_unless($user->can('create', ServiceType::class), 403);

        return app(StoreServiceType::class)->execute($user, $data);
    }
}
