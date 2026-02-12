<?php declare(strict_types=1);

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Resources\Pages\EditRecord;

/**
 * Edit client record class
 *
 * @package  App
 * @category Filament
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class EditClient extends EditRecord
{
    protected static string $resource = ClientResource::class;

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(3)
                ->columnSpan(2)
                ->schema([
                    TextInput::make("name")->label(__("Name")),
                    TextInput::make("sender_address")
                        ->readonly()
                        ->label(__("Sender Address")),
                    TextInput::make("bcc_address")
                        ->email()
                        ->label(__("Bcc Address")),
                ]),
            Textarea::make("description")
                ->columnSpan(2)
                ->label(__("Description")),
        ]);
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return __("Client has been saved!");
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl();
    }
}
