<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Purchase;
use App\Models\User;
use App\Services\DashboardMetrics;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

final class UpdatePurchase
{
    public function __construct(
        private StorePurchase $store,
        private StoreEvidence $evidence,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(User $user, Purchase $purchase, array $data, ?UploadedFile $file = null): Purchase
    {
        abort_unless($user->can('update', $purchase), 403);

        $validated = $this->store->validate($data);

        return DB::transaction(function () use ($purchase, $validated, $file): Purchase {
            $purchase->fill($validated);
            $purchase->save();

            $this->evidence->execute($purchase, $file);
            DashboardMetrics::flush();

            return $purchase->refresh();
        });
    }
}
