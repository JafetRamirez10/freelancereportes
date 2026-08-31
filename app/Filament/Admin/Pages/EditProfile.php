<?php

declare(strict_types=1);

namespace App\Filament\Admin\Pages;

use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Illuminate\Contracts\Support\Htmlable;

final class EditProfile extends BaseEditProfile
{
    protected static bool $isDiscovered = false;

    public static function getLabel(): string
    {
        return 'Mi perfil';
    }

    public function getTitle(): string|Htmlable
    {
        return 'Mi perfil';
    }

    public function getHeading(): string|Htmlable|null
    {
        return 'Mi perfil y contraseña';
    }
}
