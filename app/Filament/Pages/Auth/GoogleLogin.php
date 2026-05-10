<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\SimplePage;

class GoogleLogin extends SimplePage
{
    protected string $view = 'filament.pages.auth.google-login';

    protected static bool $shouldRegisterNavigation = false;

    public static function getSlug(): string
    {
        return 'login';
    }

    public function mount(): void
    {
        if (auth()->check() && auth()->user()->hasAnyRole(['super_admin', 'office_admin', 'staff'])) {
            $this->redirect(filament()->getUrl());
        }
    }
}
