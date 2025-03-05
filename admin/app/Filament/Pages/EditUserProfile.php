<?php declare(strict_types=1);

namespace App\Filament\Pages;

use Filament\Forms\Components\Component;
use Filament\Pages\Auth\EditProfile;

class EditUserProfile extends EditProfile
{
    protected function getEmailFormComponent(): Component
    {
        return parent::getEmailFormComponent()->readonly();
    }
}
