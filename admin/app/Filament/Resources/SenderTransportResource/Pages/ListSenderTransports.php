<?php declare(strict_types=1);

namespace App\Filament\Resources\SenderTransportResource\Pages;

use App\Filament\Resources\SenderTransportResource;
use App\Models\MailServer;
use App\Models\SenderTransport;
use App\Support\RemoteServer;
use Filament\Actions;
use Filament\Forms\Components\Select;
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
    protected static string $resource = SenderTransportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label(__("New Sender Transport")),
            Actions\Action::make("sync")
                ->form([
                    Select::make("mail_server")
                        ->options(MailServer::all()->pluck("name", "id"))
                        ->required()
                        ->label(__("Mail Server")),
                ])
                ->action(static fn(array $data) => self::syncSenderTransports($data['mail_server']))
                ->label(__("Sync To Mail Servers")),
        ];
    }

    private static function syncSenderTransports(int $id): void
    {
        $transports = SenderTransport::transports();

        if (!empty($transports)) {
            foreach (MailServer::all() as $server) {
                try {
                    $server->syncSenderTransports($transports);
                }
                catch (\Throwable $th) {
                    logger()->error($th);
                }
            }
        }

        Notification::make()
            ->title(
                __("Sender transports have been synchronized to mail servers!")
            )
            ->success()
            ->send();
    }
}
