<?php declare(strict_types=1);

namespace App\Filament\Resources\TransportResource\Pages;

use App\Filament\Resources\TransportResource;
use Filament\Resources\Pages\EditRecord;

/**
 * Edit transport record class
 *
 * @package  App
 * @category Filament
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class EditTransport extends EditRecord
{
    protected static string $resource = TransportResource::class;

    protected function getSavedNotificationTitle(): ?string
    {
        return __("Transport has been saved!");
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl();
    }
}
