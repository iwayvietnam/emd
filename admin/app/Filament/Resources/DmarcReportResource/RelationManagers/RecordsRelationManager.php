<?php declare(strict_types=1);

namespace App\Filament\Resources\DmarcReportResource\RelationManagers;

use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions;
use Filament\Tables\Columns;
use Filament\Tables\Table;

/**
 * Dmarc records relation manager class
 *
 * @package  App
 * @category Filament
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class RecordsRelationManager extends RelationManager
{
    protected static string $relationship = "records";

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            TextEntry::make("source_ip")->label(__("Source Ip")),
            TextEntry::make("count")->label(__("Message Count")),
            TextEntry::make("header_from")->label(__("Header From")),
            TextEntry::make("disposition")->label(__("Disposition")),
            TextEntry::make("envelope_from")->label(__("Envelope From")),
            TextEntry::make("envelope_to")->label(__("Envelope To")),
            TextEntry::make("dkim")->label(__("Dkim Aligned")),
            TextEntry::make("spf")->label(__("Spf Aligned")),
            TextEntry::make("reason")->label(__("Reason")),
            TextEntry::make("dkim_domain")->label(__("Dkim Domain")),
            TextEntry::make("dkim_selector")->label(__("Dkim Selector")),
            TextEntry::make("dkim_result")->label(__("Dkim Result")),
            TextEntry::make("spf_domain")->label(__("Spf Domain")),
            TextEntry::make("spf_result")->label(__("Spf Result")),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Columns\TextColumn::make("source_ip")->label(__("Source Ip")),
                Columns\TextColumn::make("count")->label(__("Message Count")),
                Columns\TextColumn::make("header_from")->label(
                    __("Header From")
                ),
                Columns\TextColumn::make("disposition")->label(
                    __("Disposition")
                ),
                Columns\TextColumn::make("dkim")->label(__("Dkim Aligned")),
                Columns\TextColumn::make("spf")->label(__("Spf Aligned")),
            ])
            ->actions([Actions\ViewAction::make()]);
    }
}
