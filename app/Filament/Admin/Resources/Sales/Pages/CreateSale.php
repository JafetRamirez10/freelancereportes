<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Sales\Pages;

use App\Actions\StoreSale;
use App\Filament\Admin\Concerns\ConvertsUploadedFile;
use App\Filament\Admin\Resources\Sales\SaleResource;
use App\Models\Sale;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

final class CreateSale extends CreateRecord
{
    use ConvertsUploadedFile;

    protected static string $resource = SaleResource::class;

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Model
    {
        /** @var User $user */
        $user = auth()->user();
        abort_unless($user->can('create', Sale::class), 403);

        $file = $this->uploaded($data['evidence'] ?? null);
        unset($data['evidence'], $data['evidence_path']);

        return app(StoreSale::class)->execute($user, $data, $file);
    }
}
