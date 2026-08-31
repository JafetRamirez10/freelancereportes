<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ServiceType;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedAdmin();
        $this->seedServices();
    }

    private function seedAdmin(): void
    {
        $seed = config('freelancer.seed');
        $email = $seed['admin_email'] ?? null;
        $password = $seed['admin_password'] ?? null;

        if (! is_string($email) || $email === '' || ! is_string($password) || $password === '') {
            throw new RuntimeException('Faltan credenciales de semilla en .env (SEED_ADMIN_EMAIL / SEED_ADMIN_PASSWORD).');
        }

        User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $seed['admin_name'] ?? 'Administrador',
                'password' => $password,
            ],
        );
    }

    private function seedServices(): void
    {
        foreach (['Hosting', 'Software', 'Diseño web'] as $name) {
            ServiceType::query()->firstOrCreate(
                ['name' => $name],
                [
                    'default_amount' => '0.00',
                    'is_active' => true,
                ],
            );
        }
    }
}
