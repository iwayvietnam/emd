<?php declare(strict_types=1);

namespace App\Filament\Resources\PolicyResource\Pages;

use App\Filament\Resources\PolicyResource;
use Filament\Resources\Pages\EditRecord;

/**
 * Edit policy record class
 *
 * @package  App
 * @category Filament
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class EditPolicy extends EditRecord
{
    protected static string $resource = PolicyResource::class;

    protected function getSavedNotificationTitle(): ?string
    {
        return __("Policy has been saved!");
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl();
    }
}
