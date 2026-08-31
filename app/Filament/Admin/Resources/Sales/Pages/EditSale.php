<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Sales\Pages;

use App\Actions\DeleteSale;
use App\Actions\UpdateSale;
use App\Filament\Admin\Concerns\ConvertsUploadedFile;
use App\Filament\Admin\Resources\Sales\SaleResource;
use App\Models\Sale;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

final class EditSale extends EditRecord
{
    use ConvertsUploadedFile;

    protected static string $resource = SaleResource::class;

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var Sale $record */
        /** @var User $user */
        $user = auth()->user();
        abort_unless($user->can('update', $record), 403);

        $file = $this->uploaded($data['evidence'] ?? null);
        unset($data['evidence'], $data['evidence_path']);

        return app(UpdateSale::class)->execute($user, $record, $data, $file);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->using(function (Sale $record): bool {
                    /** @var User $user */
                    $user = auth()->user();
                    abort_unless($user->can('delete', $record), 403);
                    app(DeleteSale::class)->execute($user, $record);

                    return true;
                }),
        ];
    }
}
