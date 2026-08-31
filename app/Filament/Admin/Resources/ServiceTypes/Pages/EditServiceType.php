<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\ServiceTypes\Pages;

use App\Actions\UpdateServiceType;
use App\Filament\Admin\Resources\ServiceTypes\ServiceTypeResource;
use App\Models\ServiceType;
use App\Models\User;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

final class EditServiceType extends EditRecord
{
    protected static string $resource = ServiceTypeResource::class;

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var ServiceType $record */
        /** @var User $user */
        $user = auth()->user();
        abort_unless($user->can('update', $record), 403);

        return app(UpdateServiceType::class)->execute($user, $record, $data);
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
