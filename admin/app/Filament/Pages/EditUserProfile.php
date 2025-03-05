<?php declare(strict_types=1);

namespace App\Filament\Pages;

use Filament\Forms\Components\Component;
use Filament\Pages\Auth\EditProfile;

/**
 * Edit user profile class
 *
 * @package  App
 * @category Filament
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class EditUserProfile extends EditProfile
{
    protected function getEmailFormComponent(): Component
    {
        return parent::getEmailFormComponent()->readonly();
    }
}
