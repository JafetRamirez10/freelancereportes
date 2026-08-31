<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RecurrenceInterval;
use Database\Factories\SaleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'client_id',
    'service_type_id',
    'sold_at',
    'amount',
    'is_recurring',
    'recurrence_interval',
    'next_charge_at',
    'notes',
])]
class Sale extends Model
{
    /** @use HasFactory<SaleFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'sold_at' => 'date',
            'next_charge_at' => 'date',
            'amount' => 'decimal:2',
            'is_recurring' => 'boolean',
            'recurrence_interval' => RecurrenceInterval::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function chargeReminders(): HasMany
    {
        return $this->hasMany(ChargeReminder::class);
    }

    public function hasEvidence(): bool
    {
        return is_string($this->evidence_path) && $this->evidence_path !== '';
    }
}
