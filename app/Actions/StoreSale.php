<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\RecurrenceInterval;
use App\Models\Sale;
use App\Models\User;
use App\Services\DashboardMetrics;
use App\Support\Money;
use Carbon\CarbonImmutable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

final class StoreSale
{
    public function __construct(private StoreEvidence $evidence) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(User $user, array $data, ?UploadedFile $file = null): Sale
    {
        abort_unless($user->can('create', Sale::class), 403);

        $validated = $this->validate($data);

        return DB::transaction(function () use ($user, $validated, $file): Sale {
            $sale = new Sale;
            $sale->fill($validated);
            $sale->forceFill([
                'user_id' => $user->id,
            ]);
            $sale->save();

            $this->evidence->execute($sale, $file);
            DashboardMetrics::flush();

            return $sale->refresh();
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function validate(array $data): array
    {
        $validated = Validator::make($data, [
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            'service_type_id' => ['required', 'integer', 'exists:service_types,id'],
            'sold_at' => ['required', 'date'],
            'amount' => ['required'],
            'is_recurring' => ['sometimes', 'boolean'],
            'recurrence_interval' => [
                'nullable',
                Rule::enum(RecurrenceInterval::class),
                'required_if:is_recurring,true',
            ],
            'next_charge_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ])->validate();

        try {
            $validated['amount'] = Money::parse($validated['amount']);
        } catch (InvalidArgumentException) {
            throw ValidationException::withMessages([
                'amount' => 'Monto inválido.',
            ]);
        }

        $validated['is_recurring'] = (bool) ($validated['is_recurring'] ?? false);

        if (! $validated['is_recurring']) {
            $validated['recurrence_interval'] = null;
            $validated['next_charge_at'] = null;

            return $validated;
        }

        $interval = RecurrenceInterval::from((string) $validated['recurrence_interval']);
        $soldAt = CarbonImmutable::parse((string) $validated['sold_at']);

        if (empty($validated['next_charge_at'])) {
            $validated['next_charge_at'] = $interval->nextChargeOn($soldAt)->toDateString();
        }

        return $validated;
    }
}
