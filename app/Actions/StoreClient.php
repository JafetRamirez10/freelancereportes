<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

final class StoreClient
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data, ?User $user = null): Client
    {
        $user ??= auth()->user();
        abort_unless($user?->can('create', Client::class) ?? false, 403);

        $validated = Validator::make($data, [
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150', Rule::unique('clients', 'email')],
        ])->validate();

        return Client::query()->create([
            'name' => $validated['name'],
            'phone' => filled($validated['phone'] ?? null) ? $validated['phone'] : null,
            'email' => filled($validated['email'] ?? null) ? $validated['email'] : null,
        ]);
    }
}
