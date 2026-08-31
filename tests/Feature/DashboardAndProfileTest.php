<?php

declare(strict_types=1);

use App\Filament\Admin\Pages\Dashboard;
use App\Filament\Admin\Pages\EditProfile;
use App\Filament\Admin\Widgets\IncomeExpenseChart;
use App\Filament\Admin\Widgets\KpiOverview;
use App\Filament\Admin\Widgets\RecentSales;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel('admin');
});

it('renderiza el dashboard y sus widgets', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Dashboard::class)
        ->assertSuccessful();

    $filters = [
        'from' => now()->startOfMonth()->toDateString(),
        'to' => now()->endOfMonth()->toDateString(),
    ];

    Livewire::actingAs($user)
        ->test(KpiOverview::class, ['pageFilters' => $filters])
        ->assertSuccessful();

    Livewire::actingAs($user)
        ->test(IncomeExpenseChart::class, ['pageFilters' => $filters])
        ->assertSuccessful();

    Livewire::actingAs($user)
        ->test(RecentSales::class, ['pageFilters' => $filters])
        ->assertSuccessful();
});

it('actualiza la contraseña desde el perfil', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(EditProfile::class)
        ->fillForm([
            'name' => $user->name,
            'email' => $user->email,
            'password' => 'NuevaClave1234',
            'passwordConfirmation' => 'NuevaClave1234',
            'currentPassword' => 'password',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(Hash::check('NuevaClave1234', $user->fresh()->password))->toBeTrue();
});
