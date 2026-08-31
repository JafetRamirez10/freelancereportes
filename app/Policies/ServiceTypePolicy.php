<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ServiceType;
use App\Models\User;

final class ServiceTypePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ServiceType $serviceType): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, ServiceType $serviceType): bool
    {
        return true;
    }

    public function delete(User $user, ServiceType $serviceType): bool
    {
        return true;
    }
}
