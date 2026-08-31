<?php

declare(strict_types=1);

namespace App\Filament\Admin\Concerns;

use Illuminate\Http\UploadedFile;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

trait ConvertsUploadedFile
{
    private function uploaded(mixed $file): ?UploadedFile
    {
        if ($file instanceof UploadedFile) {
            return $file;
        }

        if ($file instanceof TemporaryUploadedFile) {
            return new UploadedFile(
                $file->getRealPath(),
                $file->getClientOriginalName(),
                $file->getMimeType(),
                null,
                true,
            );
        }

        return null;
    }
}
