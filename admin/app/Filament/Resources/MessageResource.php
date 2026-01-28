<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\MessageResource\Pages;
use App\Filament\Resources\MessageResource\RelationManagers;
use App\Models\Message;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use BackedEnum;
use UnitEnum;

/**
 * Message resource class.
 *
 * @package  App
 * @category Filament
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class MessageResource extends Resource
{
    protected static ?string $model = Message::class;
    protected static string|UnitEnum|null $navigationGroup = "Email";
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;
    protected static ?string $slug = "message";

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make("subject")
                    ->searchable()
                    ->label(__("Subject"))
                    ->wrap(),
                TextColumn::make("from_email")->label(__("Sender")),
                TextColumn::make("open_count")->label(__("Open Count")),
                TextColumn::make("sent_at")
                    ->label(__("Sent At"))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make("user_id")
                    ->options(User::all()->pluck("name", "id"))
                    ->label(__("User")),
            ])
            ->actions([ViewAction::make(), DeleteAction::make()])
            ->defaultSort("created_at", "desc");
    }

    public static function getRelations(): array
    {
        return [
            MessageResource\RelationManagers\AttachmentsRelationManager::class,
            MessageResource\RelationManagers\DevicesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            "index" => Pages\ListMessages::route("/"),
            "view" => Pages\ViewMessage::route("/{record}"),
        ];
    }
}
