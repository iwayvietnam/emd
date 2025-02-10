<?php declare(strict_types=1);

namespace App\Filament\Resources\SenderTransportResource\Pages;

use App\Filament\Resources\SenderTransportResource;
use App\Models\MailServer;
use App\Models\SenderTransport;
use App\Support\RemoteManager;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

/**
 * List sender transport records class
 *
 * @package  App
 * @category Filament
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class ListSenderTransports extends ListRecords
{
    const SENDER_TRANSPORT_FILE = '/etc/postfix/sender_transport';
    const POSTMAP_COMMAND = 'postmap %s';

    protected static string $resource = SenderTransportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label(__("New Sender Transport")),
            Actions\Action::make()
                ->action(static fn() => self::syncSenderTransports())
                ->label(__("Sync To Mail Servers")),
        ];
    }

    private static function syncSenderTransports(): void
    {
        Notification::make()
            ->title(__("Sender transports have been synchronized to mail servers!"))
            ->success()
            ->send();
    }
}
