<?php declare(strict_types=1);

namespace App\Filament\Resources\MessageResource\Pages;

use App\Filament\Resources\MessageResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;

/**
 * View message page.
 *
 * @package  App
 * @category Filament
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class ViewMessage extends ViewRecord
{
    protected static string $resource = MessageResource::class;
    protected ?string $previousUrl = null;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            TextEntry::make("from_name")->label(__("From Name")),
            TextEntry::make("from_email")->label(__("From Email")),
            TextEntry::make("reply_to")->label(__("Reply To")),
            TextEntry::make("ip_address")->label(__("IP Address")),
            TextEntry::make("recipients")
                ->label(__("Recipients"))
                ->columnSpan(2),
            TextEntry::make("subject")->label(__("Subject"))->columnSpan(2),
            TextEntry::make("content")->label(__("Content"))->columnSpan(2),
            TextEntry::make("last_opened")->label(__("Last Opened")),
            TextEntry::make("hash")->label(__("Tracking Hash")),
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
