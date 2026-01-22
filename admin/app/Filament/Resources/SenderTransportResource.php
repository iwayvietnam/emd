<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\SenderTransportResource\Pages;
use App\Models\SenderTransport;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use BackedEnum;
use UnitEnum;

/**
 * Sender transport resource class
 *
 * @package  App
 * @category Filament
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class SenderTransportResource extends Resource
{
    protected static ?string $model = SenderTransport::class;
    protected static string | UnitEnum | null $navigationGroup = "Access Control";
    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedListBullet;
    protected static ?string $slug = "sender-transport";

    public static function getNavigationLabel(): string
    {
        return __("Sender Transports");
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make("client.name")->label(__("Client")),
                TextColumn::make("mailTransport.name")->label(
                    __("Transport Name")
                ),
                TextColumn::make("sender")
                    ->searchable()
                    ->label(__("Sender Address")),
                TextColumn::make("transport")->label(__("Transport")),
            ])
            ->actions([DeleteAction::make()])
            ->defaultSort("created_at", "desc");
    }

    public static function getPages(): array
    {
        return [
            "index" => Pages\ListSenderTransports::route("/"),
            "create" => Pages\CreateSenderTransport::route("/create"),
        ];
    }
}
