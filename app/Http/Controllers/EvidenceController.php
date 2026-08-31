<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class EvidenceController extends Controller
{
    public function sale(Sale $sale): StreamedResponse
    {
        abort_unless(auth()->user()?->can('view', $sale) ?? false, 403);

        return $this->download($sale->evidence_path);
    }

    public function purchase(Purchase $purchase): StreamedResponse
    {
        abort_unless(auth()->user()?->can('view', $purchase) ?? false, 403);

        return $this->download($purchase->evidence_path);
    }

    private function download(?string $path): StreamedResponse
    {
        abort_if(! is_string($path) || $path === '', 404);

        $disk = Storage::disk((string) config('freelancer.evidence.disk'));

        abort_unless($disk->exists($path), 404);

        return $disk->download($path, basename($path));
    }
}
