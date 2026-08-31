<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Clients\Pages;

use App\Actions\UpdateClient;
use App\Filament\Admin\Resources\Clients\ClientResource;
use App\Models\Client;
use App\Models\User;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

final class EditClient extends EditRecord
{
    protected static string $resource = ClientResource::class;

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var Client $record */
        /** @var User $user */
        $user = auth()->user();
        abort_unless($user->can('update', $record), 403);

        return app(UpdateClient::class)->execute($user, $record, $data);
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
