<?php declare(strict_types=1);

namespace App\Filament\Resources\MessageResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

/**
 * Message tracking devices relation manager.
 *
 * @package  App
 * @category Filament
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class DevicesRelationManager extends RelationManager
{
    protected static string $relationship = "Devices";

    public function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make("user_agent")->label(__("User Agent"))->wrap(),
            TextColumn::make("ip_address")->label(__("IP Address")),
            TextColumn::make("opened_at")->label(__("Opened At"))->dateTime(),
        ]);
    }
}
