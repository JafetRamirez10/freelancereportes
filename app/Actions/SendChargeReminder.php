<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ReminderKind;
use App\Mail\ChargeReminderMail;
use App\Models\ChargeReminder;
use App\Models\Sale;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Mail;

final class SendChargeReminder
{
    public function execute(Sale $sale, ReminderKind $kind): bool
    {
        if (! $sale->is_recurring || $sale->next_charge_at === null) {
            return false;
        }

        $for = $sale->next_charge_at->toDateString();
        $email = (string) config('freelancer.admin_email');

        if ($email === '') {
            return false;
        }

        $already = ChargeReminder::query()
            ->where('sale_id', $sale->id)
            ->where('kind', $kind)
            ->whereDate('for_charge_on', $for)
            ->exists();

        if ($already) {
            return false;
        }

        try {
            $reminder = ChargeReminder::query()->create([
                'sale_id' => $sale->id,
                'kind' => $kind,
                'for_charge_on' => $for,
                'sent_at' => now(),
            ]);
        } catch (UniqueConstraintViolationException) {
            return false;
        }

        try {
            Mail::to($email)->send(new ChargeReminderMail($sale->loadMissing(['client', 'serviceType']), $kind));
        } catch (\Throwable $e) {
            $reminder->delete();

            throw $e;
        }

        return true;
    }
}
