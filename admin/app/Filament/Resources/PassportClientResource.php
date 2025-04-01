<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\PassportClientResource\Pages;
use App\Models\PassportClient;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Tables\Actions;
use Filament\Tables\Columns;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * Passport client resource class.
 *
 * @package  App
 * @category Filament
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class PassportClientResource extends Resource
{
    protected static ?string $model = PassportClient::class;
    protected static ?string $navigationGroup = "System";
    protected static ?string $navigationIcon = "heroicon-o-computer-desktop";
    protected static ?string $slug = "passport-client";

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make("name")->required()->label(__("Client Name")),
            Select::make("provider")
                ->required()
                ->options(array_keys(config("auth.providers")))
                ->label(__("Provider")),
            TextInput::make("secret")
                ->hintActions([
                    Action::make("genarate")
                        ->label(__("Genarate Client Secret"))
                        ->action(
                            static fn(Set $set) => $set(
                                "secret",
                                Str::random(40)
                            )
                        ),
                ])
                ->readonly()
                ->required()
                ->label(__("Client Secret")),
            Hidden::make("password_client")->default(true),
            Hidden::make("personal_access_client")->default(false),
            Hidden::make("revoked")->default(false),
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            TextEntry::make("name")->label(__("Client Name")),
            TextEntry::make("id")->label(__("Client Id")),
            TextEntry::make("encrypted_secret")->label(__("Client Secret")),
            TextEntry::make("provider")->label(__("Provider")),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Columns\TextColumn::make("name")->label(__("Client Name")),
                Columns\TextColumn::make("id")->label(__("Client Id")),
                Columns\IconColumn::make("revoked")
                    ->boolean()
                    ->trueColor("danger")
                    ->falseColor("success")
                    ->label(__("Is Revoked")),
            ])
            ->filters([TernaryFilter::make("revoked")->label(__("Is Revoked"))])
            ->actions([
                Actions\ViewAction::make(),
                Actions\Action::make("revoke")
                    ->action(
                        static fn(PassportClient $client) => $client->revoke()
                    )
                    ->disabled(
                        static fn(PassportClient $client) => $client->revoked
                    )
                    ->requiresConfirmation()
                    ->label(__("Revoke")),
            ])
            ->modifyQueryUsing(
                static fn(Builder $query) => $query->where(
                    "password_client",
                    true
                )
            );
    }

    public static function getPages(): array
    {
        return [
            "index" => Pages\ListPassportClients::route("/"),
        ];
    }
}
