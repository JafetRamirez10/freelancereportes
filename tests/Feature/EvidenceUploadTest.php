<?php

declare(strict_types=1);

use App\Actions\StoreEvidence;
use App\Actions\StoreSale;
use App\Models\Client;
use App\Models\Sale;
use App\Models\ServiceType;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

it('guarda evidencia con nombre generado y permite descargarla autenticado', function () {
    Storage::fake('local');
    $user = User::factory()->create();
    $client = Client::factory()->create();
    $type = ServiceType::factory()->create();
    $file = UploadedFile::fake()->image('comprobante.jpg', 100, 100);

    $sale = app(StoreSale::class)->execute($user, [
        'client_id' => $client->id,
        'service_type_id' => $type->id,
        'sold_at' => now()->toDateString(),
        'amount' => '90.00',
    ], $file);

    expect($sale->hasEvidence())->toBeTrue()
        ->and($sale->evidence_path)->not->toContain('comprobante.jpg');

    Storage::disk('local')->assertExists($sale->evidence_path);

    $this->actingAs($user)
        ->get(route('evidence.sales.show', $sale))
        ->assertOk();
});

it('rechaza evidencia con MIME o extensión no permitidos', function () {
    Storage::fake('local');
    $sale = Sale::factory()->create();
    $file = UploadedFile::fake()->create('malware.exe', 20, 'application/x-msdownload');

    expect(fn () => app(StoreEvidence::class)->execute($sale, $file))
        ->toThrow(ValidationException::class);

    expect($sale->fresh()->evidence_path)->toBeNull();
});
