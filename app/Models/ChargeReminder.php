<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ReminderKind;
use Database\Factories\ChargeReminderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['sale_id', 'kind', 'for_charge_on', 'sent_at'])]
class ChargeReminder extends Model
{
    /** @use HasFactory<ChargeReminderFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'kind' => ReminderKind::class,
            'for_charge_on' => 'date',
            'sent_at' => 'datetime',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }
}
