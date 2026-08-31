<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Purchase;
use App\Models\User;
use App\Services\DashboardMetrics;
use App\Support\Money;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

final class StorePurchase
{
    public function __construct(private StoreEvidence $evidence) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(User $user, array $data, ?UploadedFile $file = null): Purchase
    {
        abort_unless($user->can('create', Purchase::class), 403);

        $validated = $this->validate($data);

        return DB::transaction(function () use ($user, $validated, $file): Purchase {
            $purchase = new Purchase;
            $purchase->fill($validated);
            $purchase->forceFill([
                'user_id' => $user->id,
            ]);
            $purchase->save();

            $this->evidence->execute($purchase, $file);
            DashboardMetrics::flush();

            return $purchase->refresh();
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function validate(array $data): array
    {
        $validated = Validator::make($data, [
            'purchased_at' => ['required', 'date'],
            'amount' => ['required'],
            'description' => ['nullable', 'string', 'max:255'],
        ])->validate();

        try {
            $validated['amount'] = Money::parse($validated['amount']);
        } catch (InvalidArgumentException) {
            throw ValidationException::withMessages([
                'amount' => 'Monto inválido.',
            ]);
        }

        return $validated;
    }
}
