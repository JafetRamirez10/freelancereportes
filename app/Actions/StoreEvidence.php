<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Purchase;
use App\Models\Sale;
use App\Services\DashboardMetrics;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

final class StoreEvidence
{
    public function execute(Model $model, ?UploadedFile $file): void
    {
        if ($file === null) {
            return;
        }

        $this->assertSafe($file);

        $disk = (string) config('freelancer.evidence.disk');
        $dir = $this->directory($model);
        $old = $model->getAttribute('evidence_path');

        $path = $file->store($dir, $disk);

        $model->forceFill([
            'evidence_path' => $path,
        ])->save();

        if (is_string($old) && $old !== '' && $old !== $path) {
            Storage::disk($disk)->delete($old);
        }

        DashboardMetrics::flush();
    }

    public function delete(Model $model): void
    {
        $path = $model->getAttribute('evidence_path');

        if (! is_string($path) || $path === '') {
            return;
        }

        $model->forceFill([
            'evidence_path' => null,
        ])->save();

        Storage::disk((string) config('freelancer.evidence.disk'))->delete($path);
        DashboardMetrics::flush();
    }

    private function directory(Model $model): string
    {
        return match (true) {
            $model instanceof Sale => (string) config('freelancer.evidence.sales_directory'),
            $model instanceof Purchase => (string) config('freelancer.evidence.purchases_directory'),
            default => throw ValidationException::withMessages([
                'evidence' => 'No se puede adjuntar evidencia a este registro.',
            ]),
        };
    }

    private function assertSafe(UploadedFile $file): void
    {
        $mimes = config('freelancer.evidence.mimes');
        $extensions = config('freelancer.evidence.extensions');
        $mime = (string) $file->getMimeType();
        $ext = strtolower((string) $file->getClientOriginalExtension());

        if (! in_array($mime, $mimes, true) || ! in_array($ext, $extensions, true)) {
            throw ValidationException::withMessages([
                'evidence' => 'La evidencia debe ser JPEG, PNG, WebP o PDF.',
            ]);
        }

        $size = $file->getSize() ?? 0;
        $max = ((int) config('freelancer.evidence.max_kb')) * 1024;

        if ($size > $max) {
            throw ValidationException::withMessages([
                'evidence' => 'La evidencia no puede superar 5 MB.',
            ]);
        }

        if (str_starts_with($mime, 'image/')) {
            $info = @getimagesize($file->getRealPath() ?: '');

            if ($info === false) {
                throw ValidationException::withMessages([
                    'evidence' => 'El archivo no es una imagen válida.',
                ]);
            }
        }
    }
}
