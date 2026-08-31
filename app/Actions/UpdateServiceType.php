<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\ServiceType;
use App\Models\User;

final class UpdateServiceType
{
    public function __construct(private StoreServiceType $store) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(User $user, ServiceType $serviceType, array $data): ServiceType
    {
        abort_unless($user->can('update', $serviceType), 403);

        $serviceType->fill($this->store->validate($data))->save();

        return $serviceType->refresh();
    }
}
