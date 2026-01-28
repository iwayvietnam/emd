<?php declare(strict_types=1);

namespace App\Filament\Resources\DkimKeyResource\Pages;

use App\Filament\Resources\DkimKeyResource;
use Filament\Actions\CreateAction;
use App\Models\DkimKey;
use App\Models\MailServer;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

/**
 * List dkim key record class
 *
 * @package  App
 * @category Filament
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class ListDkimKeys extends ListRecords
{
    protected static string $resource = DkimKeyResource::class;

    public function getTitle(): string
    {
        return __("DKIM Keys");
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label(__("New DKIM Key")),
            Actions\Action::make("syncKeys")
                ->form([
                    Select::make("mail_server")
                        ->options(MailServer::all()->pluck("name", "id"))
                        ->required()
                        ->label(__("Mail Server")),
                ])
                ->action(
                    static fn(array $data) => self::syncOpenDkimKeys(
                        (int) $data["mail_server"],
                    ),
                )
                ->label(__("Sync To Mail Server")),
        ];
    }

    private static function syncOpenDkimKeys(int $id): void
    {
        $signingTable = DkimKey::signingTable();
        $keyTable = DkimKey::keyTable();
        $privateKeys = DkimKey::privateKeys();

        if (
            !empty($signingTable) ||
            !empty($keyTable) ||
            !empty($privateKeys)
        ) {
            try {
                MailServer::find($id)->syncOpenDkimKeys(
                    $signingTable,
                    $keyTable,
                    $privateKeys,
                );
            } catch (\Throwable $th) {
                logger()->error($th);
                Notification::make()
                    ->title(__("Failed to synchronize OpenDKIM keys!"))
                    ->danger()
                    ->send();
                return;
            }
        }

        Notification::make()
            ->title(__("OpenDKIM keys have been synchronized!"))
            ->success()
            ->send();
    }
}
