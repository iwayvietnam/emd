<?php declare(strict_types=1);

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions;
use Filament\Tables\Columns;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Laravel\Passport\Token;

/**
 * User OAuth token relation manager class
 *
 * @package  App
 * @category Filament
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class OAuthTokensRelationManager extends RelationManager
{
    protected static string $relationship = "tokens";
    protected static ?string $title = "OAuth Tokens";

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            TextEntry::make("client.name")->label(__("Client Name")),
            TextEntry::make("name")->label(__("Token Name")),
            TextEntry::make("id")->label(__("Token Id")),
            TextEntry::make("expires_at")->label(__("Expires At")),
            TextEntry::make("scopes")->label(__("Scopes")),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute("name")
            ->columns([
                Columns\TextColumn::make("client.name")->label(__("Client Name")),
                Columns\TextColumn::make("name")->label(__("Token Name")),
                Columns\TextColumn::make("expires_at")->label(__("Expires At")),
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
                        static fn(Client $token) => self::revokeClient($token)
                    )
                    ->label(__("Revoke")),
            ]);
    }

    private static function revokeClient(Token $token): void
    {
        $token->revoke();
    }
}
