<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\MessageFailureResource\Pages;
use App\Models\MessageFailure;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;

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
    protected static ?string $navigationGroup = "Email";
    protected static ?string $navigationIcon = "heroicon-o-envelope";
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
