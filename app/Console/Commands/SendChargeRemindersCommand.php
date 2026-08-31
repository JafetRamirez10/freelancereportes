<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\SendChargeReminder;
use App\Enums\ReminderKind;
use App\Models\Sale;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

final class SendChargeRemindersCommand extends Command
{
    protected $signature = 'charges:send-reminders';

    protected $description = 'Envía avisos de cobro recurrente al administrador (30 y 7 días antes).';

    public function handle(SendChargeReminder $send): int
    {
        $today = CarbonImmutable::now()->startOfDay();
        $sent = 0;

        foreach (ReminderKind::cases() as $kind) {
            $target = $today->addDays($kind->daysAhead())->toDateString();

            Sale::query()
                ->where('is_recurring', true)
                ->whereDate('next_charge_at', $target)
                ->with(['client', 'serviceType'])
                ->orderBy('id')
                ->chunkById(100, function ($sales) use ($send, $kind, &$sent): void {
                    foreach ($sales as $sale) {
                        if ($send->execute($sale, $kind)) {
                            $sent++;
                        }
                    }
                });
        }

        $this->info("Avisos enviados: {$sent}");

        return self::SUCCESS;
    }
}
