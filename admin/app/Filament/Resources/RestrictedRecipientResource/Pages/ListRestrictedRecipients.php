<?php declare(strict_types=1);

namespace App\Filament\Resources\RestrictedRecipientResource\Pages;

use App\Filament\Resources\RestrictedRecipientResource;
use App\Models\MailServer;
use App\Models\RestrictedRecipient;
use Filament\Actions;
use Filament\Forms\Components\Select;
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
            Actions\CreateAction::make()->label(
                __("Create Restrict Recipients"),
            ),
            Actions\Action::make("sync")
                ->schema([
                    Select::make("mail_server")
                        ->options(MailServer::all()->pluck("name", "id"))
                        ->required()
                        ->label(__("Mail Server")),
                ])
                ->action(
                    static fn(array $data) => self::syncRecipientRestrictions(
                        (int) $data["mail_server"],
                    ),
                )
                ->label(__("Sync To Mail Server")),
        ];
    }

    private static function syncRecipientRestrictions(int $id): void
    {
        $restrictions = RestrictedRecipient::recipientRestrictions();

        if (!empty($restrictions)) {
            try {
                MailServer::find($id)->syncRecipientRestrictions($restrictions);
            } catch (\Throwable $th) {
                logger()->error($th);
                Notification::make()
                    ->title(__("Failed to synchronize recipient restrictions!"))
                    ->danger()
                    ->send();
                return;
            }
        }

        Notification::make()
            ->title(__("Recipient restrictions have been synchronized!"))
            ->success()
            ->send();
    }
}
