<?php

declare(strict_types=1);

namespace App\Filament\Admin\Concerns;

trait DoesNotPoll
{
    protected function getPollingInterval(): ?string
    {
        return null;
    }
}
