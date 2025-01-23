<?php declare(strict_types=1);

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * List user records class
 *
 * @package  App
 * @category Filament
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make("name")->label(__("Name")),
                TextColumn::make("email")->label(__("Email")),
                TextColumn::make("created_at")
                    ->dateTime()
                    ->sortable()
                    ->label(__("Created At")),
            ])
            ->actions([EditAction::make()]);
    }

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label(__("New User"))];
    }
}
