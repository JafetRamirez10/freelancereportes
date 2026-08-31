<?php

declare(strict_types=1);

use App\Http\Controllers\EvidenceController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

Route::middleware(['auth'])->group(function (): void {
    Route::get('/evidence/sales/{sale}', [EvidenceController::class, 'sale'])
        ->name('evidence.sales.show');
    Route::get('/evidence/purchases/{purchase}', [EvidenceController::class, 'purchase'])
        ->name('evidence.purchases.show');
});
