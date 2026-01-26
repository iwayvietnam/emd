<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\MessageFailureResource\Pages;
use App\Models\MessageFailure;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use BackedEnum;
use UnitEnum;

/**
 * Message failure resource manager.
 *
 * @package  App
 * @category Filament
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class MessageFailureResource extends Resource
{
    protected static ?string $model = MessageFailure::class;
    protected static string | UnitEnum | null $navigationGroup = "Email";
    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedTicket;
    protected static ?string $slug = "message-failure";

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make("message.subject")
                    ->label(__("Message"))
                    ->wrap(),
                TextColumn::make("severity")->label(__("Severity"))->wrap(),
                TextColumn::make("description")
                    ->label(__("Description"))
                    ->wrap(),
                TextColumn::make("failed_at")
                    ->label(__("Failed At"))
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([ViewAction::make(), DeleteAction::make()])
            ->defaultSort("failed_at", "desc");
    }

    public static function getPages(): array
    {
        return [
            "index" => Pages\ListMessageFailures::route("/"),
            "view" => Pages\VIewMessageFailure::route("/{record}"),
        ];
    }
}
