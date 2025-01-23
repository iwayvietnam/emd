<?php declare(strict_types=1);

namespace App\Filament\Resources\MessageFailureResource\Pages;

use App\Filament\Resources\MessageFailureResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;

/**
 * View message failure page.
 *
 * @package  App
 * @category Resources
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class ViewMessageFailure extends ViewRecord
{
    protected static string $resource = MessageFailureResource::class;
    protected ?string $previousUrl = null;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            TextEntry::make("message_subject")->label(__("Message Subject")),
            TextEntry::make("message_id")->label(__("Message ID")),
            TextEntry::make("from_email")->label(__("Sender")),
            TextEntry::make("severity")->label(__("Severity")),
            TextEntry::make("description")->label(__("Description")),
            TextEntry::make("failed_at")->label(__("Failed At")),
        ]);
    }

    public function mount(int|string $record): void
    {
        parent::mount($record);
        $this->previousUrl = url()->previous();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make("back")
                ->url($this->previousUrl ?? self::getResource()::getUrl())
                ->color("gray")
                ->label(__("Back")),
            DeleteAction::make(),
        ];
    }
}
