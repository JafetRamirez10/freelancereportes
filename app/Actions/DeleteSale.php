<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Sale;
use App\Models\User;
use App\Services\DashboardMetrics;
use Illuminate\Support\Facades\DB;

final class DeleteSale
{
    public function __construct(private StoreEvidence $evidence) {}

    public function execute(User $user, Sale $sale): void
    {
        abort_unless($user->can('delete', $sale), 403);

        DB::transaction(function () use ($sale): void {
            $this->evidence->delete($sale);
            $sale->delete();
            DashboardMetrics::flush();
        });
    }
}
