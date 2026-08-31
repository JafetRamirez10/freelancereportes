<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Purchases\Pages;

use App\Actions\StorePurchase;
use App\Filament\Admin\Concerns\ConvertsUploadedFile;
use App\Filament\Admin\Resources\Purchases\PurchaseResource;
use App\Models\Purchase;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

final class CreatePurchase extends CreateRecord
{
    use ConvertsUploadedFile;

    protected static string $resource = PurchaseResource::class;

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Model
    {
        /** @var User $user */
        $user = auth()->user();
        abort_unless($user->can('create', Purchase::class), 403);

        $file = $this->uploaded($data['evidence'] ?? null);
        unset($data['evidence'], $data['evidence_path']);

        return app(StorePurchase::class)->execute($user, $data, $file);
    }
}
