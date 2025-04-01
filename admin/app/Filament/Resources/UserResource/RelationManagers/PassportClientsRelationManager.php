<?php declare(strict_types=1);

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Models\PassportClient;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Hidden;
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

/**
 * User passport clients relation manager class
 *
 * @package  App
 * @category Filament
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class PassportClientsRelationManager extends RelationManager
{
    protected static string $relationship = "clients";
    protected static ?string $title = "Passport Clients";

    public function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make("name")->required()->label(__("Client Name")),
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
            TextInput::make("redirect")
                ->required()
                ->url()
                ->columnSpan(2)
                ->label(__("Redirect URL")),
            Hidden::make("password_client")->default(false),
            Hidden::make("personal_access_client")->default(false),
            Hidden::make("revoked")->default(false),
        ]);
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            TextEntry::make("name")->label(__("Client Name")),
            TextEntry::make("id")->label(__("Client Id")),
            TextEntry::make("encrypted_secret")->label(__("Client Secret")),
            TextEntry::make("redirect")->label(__("Redirect URL")),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute("name")
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
            ->headerActions([
                Actions\CreateAction::make()->createAnother(false),
            ])
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
            ]);
    }
}
