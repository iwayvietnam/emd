<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Models\Client;
use App\Models\Domain;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Actions;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use BackedEnum;
use UnitEnum;

/**
 * Client resource class
 *
 * @package  App
 * @category Filament
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class ClientResource extends Resource
{
    protected static ?string $model = Client::class;
    protected static string | UnitEnum | null $navigationGroup = "Access Control";
    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedComputerDesktop;
    protected static ?string $slug = "client";

    public static function getNavigationLabel(): string
    {
        return __("Clients");
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make("name")->sortable()->label(__("Name")),
                TextColumn::make("sender_address")->label(__("Sender Address")),
                TextColumn::make("domain.name")->label(__("Domain")),
                TextColumn::make("created_at")
                    ->dateTime()
                    ->sortable()
                    ->label(__("Created At")),
            ])
            ->filters([
                SelectFilter::make("domain_id")
                    ->options(Domain::all()->pluck("name", "id"))
                    ->label(__("Domain")),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->defaultSort("created_at", "desc");
    }

    public static function getPages(): array
    {
        return [
            "index" => Pages\ListClients::route("/"),
            "create" => Pages\CreateClient::route("/create"),
            "edit" => Pages\EditClient::route("/{record}/edit"),
        ];
    }
}
