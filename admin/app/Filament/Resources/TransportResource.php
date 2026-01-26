<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\TransportResource\Pages;
use App\Models\SenderTransport;
use App\Models\Transport;
use Filament\Actions;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use BackedEnum;
use UnitEnum;

/**
 * Transport resource class
 *
 * @package  App
 * @category Filament
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class TransportResource extends Resource
{
    protected static ?string $model = Transport::class;
    protected static string | UnitEnum | null $navigationGroup = "System";
    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedListBullet;
    protected static ?string $slug = "transport";

    public static function getNavigationLabel(): string
    {
        return __("Mail Transports");
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make()->schema([
                TextInput::make("name")
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->label(__("Name")),
                TextInput::make("transport")
                    ->required()
                    ->label(__("Transport"))
                    ->helperText(__("The message delivery transport.")),
                TextInput::make("nexthop")
                    ->label(__("Nexthop"))
                    ->helperText(__("The next-hop destination.")),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make("name")->sortable()->label(__("Name")),
                TextColumn::make("transport")->label(__("Transport")),
                TextColumn::make("nexthop")->label(__("Nexthop")),
                TextColumn::make("created_at")
                    ->dateTime()
                    ->sortable()
                    ->label(__("Created At")),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make()->before(static function (
                    Actions\DeleteAction $action
                ) {
                    if (self::haveSenderTransport([$action->getRecord()->id])) {
                        self::cancelDelete($action);
                    }
                }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            "index" => Pages\ListTransports::route("/"),
        ];
    }

    private static function haveSenderTransport(array $ids): bool
    {
        return SenderTransport::whereIn("transport_id", $ids)->count() > 0;
    }

    private static function cancelDelete(Actions\Action $action): void
    {
        Notification::make()
            ->warning()
            ->title(__("Unable to delete transport"))
            ->body(
                __(
                    "You must delete all sender transports belongs to the transport."
                )
            )
            ->send();
        $action->cancel();
    }
}
