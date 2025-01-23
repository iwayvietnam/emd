<?php declare(strict_types=1);

namespace App\Filament\Resources\MailServerResource\Pages;

use App\Filament\Resources\MailServerResource;
use Filament\Resources\Pages\CreateRecord;

/**
 * Create mail server record class
 *
 * @package  App
 * @category Filament
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class CreateMailServer extends CreateRecord
{
    protected static string $resource = MailServerResource::class;
    protected static bool $canCreateAnother = false;

    protected function getCreatedNotificationTitle(): ?string
    {
        return __("Mail server has been created!");
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl();
    }
}
