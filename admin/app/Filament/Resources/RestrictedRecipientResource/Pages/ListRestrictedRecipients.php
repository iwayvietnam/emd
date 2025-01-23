<?php declare(strict_types=1);

namespace App\Filament\Resources\RestrictedRecipientResource\Pages;

use App\Filament\Resources\RestrictedRecipientResource;
use App\Models\RestrictedRecipient;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

/**
 * List restricted recipient records class
 *
 * @package  App
 * @category Filament
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class ListRestrictedRecipients extends ListRecords
{
    protected static string $resource = RestrictedRecipientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label(__("Create Restrict Recipients")),
            Actions\Action::make("clear_cache")
                ->action(static fn() => self::clearRestrictedCache())
                ->label(__("Clear Cache")),
        ];
    }

    private static function clearRestrictedCache(): void
    {
        RestrictedRecipient::clearCache();
        Notification::make()
            ->title(__("Restricted recipients have been removed from the cache!"))
            ->success()
            ->send();
    }
}
