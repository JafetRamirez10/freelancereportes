<?php

declare(strict_types=1);

use App\Models\Purchase;
use App\Models\Sale;
use App\Models\User;

it('redirige al invitado al login del panel', function () {
    $this->get('/admin')->assertRedirect('/admin/login');
});

it('permite al administrador autenticado ver el panel', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin')->assertOk();
});

it('exige autenticación para descargar evidencia de venta', function () {
    $sale = Sale::factory()->create();

    $this->get(route('evidence.sales.show', $sale))->assertRedirect('/admin/login');
});

it('exige autenticación para descargar evidencia de compra', function () {
    $purchase = Purchase::factory()->create();

    $this->get(route('evidence.purchases.show', $purchase))->assertRedirect('/admin/login');
});

it('autoriza al usuario autenticado a gestionar ventas y compras', function () {
    $user = User::factory()->create();
    $sale = Sale::factory()->create();
    $purchase = Purchase::factory()->create();

    expect($user->can('viewAny', Sale::class))->toBeTrue()
        ->and($user->can('create', Sale::class))->toBeTrue()
        ->and($user->can('view', $sale))->toBeTrue()
        ->and($user->can('update', $sale))->toBeTrue()
        ->and($user->can('delete', $sale))->toBeTrue()
        ->and($user->can('view', $purchase))->toBeTrue();
});
