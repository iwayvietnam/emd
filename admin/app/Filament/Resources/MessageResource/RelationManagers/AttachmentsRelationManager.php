<?php declare(strict_types=1);

namespace App\Filament\Resources\MessageResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Actions\Action;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Storage;

/**
 * Message attachments relation manager.
 *
 * @package  App
 * @category Filament
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class AttachmentsRelationManager extends RelationManager
{
    protected static string $relationship = "attachments";

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make("file_name")->label(__("File Name")),
                TextColumn::make("file_mime")->label(__("File Mime")),
                TextColumn::make("file_size")->label(__("File Size")),
            ])
            ->actions([
                Action::make("download")
                    ->label(__("Download"))
                    ->action(
                        static fn($record) => Storage::download(
                            $record->file_path
                        )
                    ),
            ]);
    }
}
