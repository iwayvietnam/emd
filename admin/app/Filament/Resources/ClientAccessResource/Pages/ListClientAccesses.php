<?php declare(strict_types=1);

namespace App\Filament\Resources\ClientAccessResource\Pages;

use App\Filament\Resources\ClientAccessResource;
use App\Models\ClientAccess;
use App\Models\MailServer;
use Filament\Actions;
use Filament\Forms\Components\Select;
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
            Actions\Action::make("syncClientAccess")
                ->form([
                    Select::make("mail_server")
                        ->options(MailServer::all()->pluck("name", "id"))
                        ->required()
                        ->label(__("Mail Server")),
                ])
                ->action(
                    static fn(array $data) => self::syncClientAccesses(
                        (int) $data["mail_server"]
                    )
                )
                ->label(__("Sync To Mail Server")),
            Actions\Action::make("clear_cache")
                ->action(static fn() => self::clearAccessCache())
                ->label(__("Clear Cache")),
        ];
    }

    private static function syncClientAccesses(int $id): void
    {
        $clientIps = ClientAccess::clientIpAccesses();
        $senders = ClientAccess::senderAccesses();
        $trustedHosts = ClientAccess::trustedHosts();

        if (!empty($clientIps) || !empty($senders) || !empty($trustedHosts)) {
            try {
                $mailServer = MailServer::find($id)
                $mailServer->syncClientAccesses($clientIps, $senders);
                $mailServer->syncOpenDkimTrustedHosts($trustedHosts);
            } catch (\Throwable $th) {
                logger()->error($th);
                Notification::make()
                    ->title(__("Failed to synchronize client accesses!"))
                    ->danger()
                    ->send();
                return;
            }
        }

        Notification::make()
            ->title(__("Client accesses have been synchronized!"))
            ->success()
            ->send();
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
