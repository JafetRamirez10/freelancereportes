<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Purchases\Pages;

use App\Actions\DeletePurchase;
use App\Actions\UpdatePurchase;
use App\Filament\Admin\Concerns\ConvertsUploadedFile;
use App\Filament\Admin\Resources\Purchases\PurchaseResource;
use App\Models\Purchase;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

final class EditPurchase extends EditRecord
{
    use ConvertsUploadedFile;

    protected static string $resource = PurchaseResource::class;

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var Purchase $record */
        /** @var User $user */
        $user = auth()->user();
        abort_unless($user->can('update', $record), 403);

        $file = $this->uploaded($data['evidence'] ?? null);
        unset($data['evidence'], $data['evidence_path']);

        return app(UpdatePurchase::class)->execute($user, $record, $data, $file);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->using(function (Purchase $record): bool {
                    /** @var User $user */
                    $user = auth()->user();
                    abort_unless($user->can('delete', $record), 403);
                    app(DeletePurchase::class)->execute($user, $record);

                    return true;
                }),
        ];
    }
}
