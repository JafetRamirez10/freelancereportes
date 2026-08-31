<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Clients\Pages;

use App\Actions\StoreClient;
use App\Filament\Admin\Resources\Clients\ClientResource;
use App\Models\Client;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

final class CreateClient extends CreateRecord
{
    protected static string $resource = ClientResource::class;

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Model
    {
        /** @var User $user */
        $user = auth()->user();
        abort_unless($user->can('create', Client::class), 403);

        return app(StoreClient::class)->execute($data, $user);
    }
}
