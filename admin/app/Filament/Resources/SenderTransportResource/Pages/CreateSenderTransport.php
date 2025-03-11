<?php declare(strict_types=1);

namespace App\Filament\Resources\SenderTransportResource\Pages;

use App\Filament\Resources\SenderTransportResource;
use App\Models\Client;
use App\Models\Transport;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;

/**
 * Create sender transport record class
 *
 * @package  App
 * @category Filament
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class CreateSenderTransport extends CreateRecord
{
    protected static string $resource = SenderTransportResource::class;
    protected static bool $canCreateAnother = false;

    public function form(Form $form): Form
    {
        return $form->schema([
            Select::make("client_id")
                ->options(Client::all()->pluck("name", "id"))
                ->required()
                ->unique()
                ->searchable()
                ->label(__("Client")),
            Select::make("transport_id")
                ->options(Transport::all()->pluck("name", "id"))
                ->required()
                ->label(__("Transport")),
                ->searchable()
            Hidden::make("sender"),
            Hidden::make("transport"),
        ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data["sender"] = Client::find($data["client_id"])->sender_address;
        $transport = Transport::find($data["transport_id"]);
        $data["transport"] = $transport->transport . ":" . $transport->nexthop;
        return $data;
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return __("Sender transport has been created");
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl();
    }
}
