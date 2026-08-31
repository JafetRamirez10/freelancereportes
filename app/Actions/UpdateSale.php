<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Sale;
use App\Models\User;
use App\Services\DashboardMetrics;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

final class UpdateSale
{
    public function __construct(
        private StoreSale $store,
        private StoreEvidence $evidence,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(User $user, Sale $sale, array $data, ?UploadedFile $file = null): Sale
    {
        abort_unless($user->can('update', $sale), 403);

        $validated = $this->store->validate($data);

        return DB::transaction(function () use ($sale, $validated, $file): Sale {
            $sale->fill($validated);
            $sale->save();

            $this->evidence->execute($sale, $file);
            DashboardMetrics::flush();

            return $sale->refresh();
        });
    }
}
