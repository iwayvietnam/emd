<?php declare(strict_types=1);

namespace App\Filament\Resources\TransportResource\Pages;

use App\Filament\Resources\TransportResource;
use Filament\Resources\Pages\CreateRecord;

/**
 * Create transport record class
 *
 * @package  App
 * @category Filament
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class CreateTransport extends CreateRecord
{
    protected static string $resource = TransportResource::class;
    protected static bool $canCreateAnother = false;

    protected function getCreatedNotificationTitle(): ?string
    {
        return __("Transport has been created!");
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl();
    }
}
