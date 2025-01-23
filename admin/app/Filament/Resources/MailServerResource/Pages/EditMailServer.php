<?php declare(strict_types=1);

namespace App\Filament\Resources\MailServerResource\Pages;

use App\Filament\Resources\MailServerResource;
use Filament\Resources\Pages\EditRecord;

/**
 * Create mail server record class
 *
 * @package  App
 * @category Filament
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class EditMailServer extends EditRecord
{
    protected static string $resource = MailServerResource::class;

    protected function getSavedNotificationTitle(): ?string
    {
        return __("Mail server has been saved!");
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl();
    }
}
