<?php declare(strict_types=1);

namespace App\Filament\Resources\ClientAccessResource\Pages;

use App\Filament\Resources\ClientAccessResource;
use App\Models\ClientAccess;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

/**
 * List client access records class
 *
 * @package  App
 * @category Filament
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class ListClientAccesses extends ListRecords
{
    protected static string $resource = ClientAccessResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label(__("New Client Access")),
            Actions\Action::make("clear_cache")
                ->action(static fn() => self::clearAccessCache())
                ->label(__("Clear Cache")),
        ];
    }

    private static function clearAccessCache(): void
    {
        ClientAccess::clearCache();
        Notification::make()
            ->title(__("Client accesses have been removed from the cache!"))
            ->success()
            ->send();
    }
}
