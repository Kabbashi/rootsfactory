<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Users\UserResource;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

/**
 * A direct entry point to the signed-in member's own profile — where they edit
 * their bio, scholarly profile, links and password. It simply forwards to the
 * member's own record in the Community resource (owner-editable).
 */
class MyProfile extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserCircle;

    // No group and a very low sort: sits at the very top, above Portal.
    protected static ?string $navigationLabel = 'My Profile';

    protected static ?string $title = 'My Profile';

    protected static ?int $navigationSort = -3;

    protected string $view = 'filament.pages.my-profile';

    public function mount(): void
    {
        $this->redirect(UserResource::getUrl('edit', ['record' => auth()->user()]));
    }
}
