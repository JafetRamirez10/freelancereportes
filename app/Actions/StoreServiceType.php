<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\ServiceType;
use App\Models\User;
use App\Support\Money;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

final class StoreServiceType
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(User $user, array $data): ServiceType
    {
        abort_unless($user->can('create', ServiceType::class), 403);

        return ServiceType::query()->create($this->validate($data));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function validate(array $data): array
    {
        $validated = Validator::make($data, [
            'name' => ['required', 'string', 'max:150'],
            'default_amount' => ['required'],
            'is_active' => ['sometimes', 'boolean'],
        ])->validate();

        try {
            $validated['default_amount'] = Money::parse($validated['default_amount']);
        } catch (InvalidArgumentException) {
            throw ValidationException::withMessages([
                'default_amount' => 'Monto inválido.',
            ]);
        }

        $validated['is_active'] = (bool) ($validated['is_active'] ?? true);

        return $validated;
    }
}
