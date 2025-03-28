<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ClientAccessResource\Pages;
use App\Models\ClientAccess;
use App\Models\Policy;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/**
 * Client access resource class
 *
 * @package  App
 * @category Filament
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class ClientAccessResource extends Resource
{
    protected static ?string $model = ClientAccess::class;
    protected static ?string $navigationGroup = "Access Control";
    protected static ?string $navigationIcon = "heroicon-o-lock-open";
    protected static ?string $slug = "client-access";

    public static function getNavigationLabel(): string
    {
        return __("Client Accesses");
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make("policy.name")->label(__("Policy")),
                TextColumn::make("client.name")->label(__("Client")),
                TextColumn::make("sender")
                    ->searchable()
                    ->label(__("Sender Address")),
                TextColumn::make("client_ip")->label(__("Client Ip")),
                TextColumn::make("verdict")->label(__("Verdict")),
            ])
            ->filters([
                SelectFilter::make("policy_id")
                    ->options(Policy::all()->pluck("name", "id"))
                    ->label(__("Policy")),
            ])
            ->actions([
                Actions\ActionGroup::make([
                    Actions\Action::make("reset_rate")
                        ->requiresConfirmation()
                        ->action(
                            static fn(
                                ClientAccess $record
                            ) => self::resetRateCounter($record)
                        )
                        ->icon("heroicon-m-check-badge")
                        ->color("primary")
                        ->label("Reset Rate Counter"),
                    Actions\Action::make("reset_quata")
                        ->requiresConfirmation()
                        ->action(
                            static fn(
                                ClientAccess $record
                            ) => self::resetQuotaCounter($record)
                        )
                        ->icon("heroicon-m-check-badge")
                        ->color("primary")
                        ->label("Reset Quota Counter"),
                    Actions\DeleteAction::make(),
                ]),
            ])
            ->defaultSort("created_at", "desc");
    }

    public static function getPages(): array
    {
        return [
            "index" => Pages\ListClientAccesses::route("/"),
            "create" => Pages\CreateClientAccess::route("/create"),
        ];
    }

    private static function resetRateCounter(ClientAccess $record): void
    {
        $record->resetRateCounter();
        Notification::make()
            ->title(__("Rate counter have been reset from the cache!"))
            ->success()
            ->send();
    }

    private static function resetQuotaCounter(ClientAccess $record): void
    {
        $record->resetQuotaCounter();
        Notification::make()
            ->title(__("Quota counter have been reset from the cache!"))
            ->success()
            ->send();
    }
}
