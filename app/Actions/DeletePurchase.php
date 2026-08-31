<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Purchase;
use App\Models\User;
use App\Services\DashboardMetrics;
use Illuminate\Support\Facades\DB;

final class DeletePurchase
{
    public function __construct(private StoreEvidence $evidence) {}

    public function execute(User $user, Purchase $purchase): void
    {
        abort_unless($user->can('delete', $purchase), 403);

        DB::transaction(function () use ($purchase): void {
            $this->evidence->delete($purchase);
            $purchase->delete();
            DashboardMetrics::flush();
        });
    }
}
