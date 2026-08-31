<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

final class UpdateClient
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(User $user, Client $client, array $data): Client
    {
        abort_unless($user->can('update', $client), 403);

        $validated = Validator::make($data, [
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150', Rule::unique('clients', 'email')->ignore($client->id)],
        ])->validate();

        $client->fill([
            'name' => $validated['name'],
            'phone' => filled($validated['phone'] ?? null) ? $validated['phone'] : null,
            'email' => filled($validated['email'] ?? null) ? $validated['email'] : null,
        ])->save();

        return $client->refresh();
    }
}
