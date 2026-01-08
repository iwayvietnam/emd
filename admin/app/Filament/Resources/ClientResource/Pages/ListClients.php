<?php declare(strict_types=1);

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use App\Models\Client;
use App\Models\MailServer;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

/**
 * List client records class
 *
 * @package  App
 * @category Filament
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class ListClients extends ListRecords
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label(__("New Client")),
            Actions\Action::make("sync")
                ->form([
                    Select::make("mail_server")
                        ->options(MailServer::all()->pluck("name", "id"))
                        ->required()
                        ->label(__("Mail Server")),
                ])
                ->action(
                    static fn(array $data) => self::syncSenderBccMaps(
                        (int) $data["mail_server"]
                    )
                )
                ->label(__("Sync Bcc Maps")),
        ];
    }

    private static function syncSenderBccMaps(int $id): void
    {
        $senderBccMaps = Client::senderBccMaps();

        if (!empty($senderBccMaps)) {
            try {
                MailServer::find($id)->syncSenderBccMaps($senderBccMaps);
            } catch (\Throwable $th) {
                logger()->error($th);
                Notification::make()
                    ->title(__("Failed to synchronize sender bcc maps!"))
                    ->danger()
                    ->send();
                return;
            }
        }

        Notification::make()
            ->title(__("Sender bcc maps have been synchronized!"))
            ->success()
            ->send();
    }
}
