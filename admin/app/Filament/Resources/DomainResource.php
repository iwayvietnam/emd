<?php declare(strict_types=1);

namespace App\Filament\Resources;

use Amp\Dns\DnsRecord;
use Amp\Dns\DnsException;
use App\Filament\Resources\DomainResource\Pages;
use App\Models\Client;
use App\Models\Domain;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions;
use Filament\Tables\Columns;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use BackedEnum;
use UnitEnum;

/**
 * Domain resource class
 *
 * @package  App
 * @category Filament
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class DomainResource extends Resource
{
    const RECORD_FORMAT = "%-10s %-56s\r\n";

    protected static ?string $model = Domain::class;
    protected static string | UnitEnum | null $navigationGroup = "System";
    protected static string | BackedEnum | null $navigationIcon = "heroicon-s-cog";
    protected static ?string $slug = "domain";

    public static function getNavigationLabel(): string
    {
        return __("Domains");
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Columns\TextColumn::make("name")->sortable()->label(__("Name")),
                Columns\TextColumn::make("email")->label(__("Email")),
                Columns\TagsColumn::make("clients.name")
                    ->limit(2)
                    ->label(__("Clients")),
                Columns\TextColumn::make("created_at")
                    ->dateTime()
                    ->sortable()
                    ->label(__("Created At")),
            ])
            ->actions([
                Actions\ActionGroup::make([
                    Actions\EditAction::make(),
                    Actions\DeleteAction::make()->before(static function (
                        Actions\DeleteAction $action
                    ) {
                        if (self::haveClient([$action->getRecord()->id])) {
                            self::cancelDelete($action);
                        }
                    }),
                    Actions\Action::make("query_mx_records")
                        ->infolist([
                            TextEntry::make("mx_records")
                                ->state(
                                    static fn($record) => self::queryMxRecords(
                                        $record
                                    )
                                )
                                ->html()
                                ->label(__("Result")),
                        ])
                        ->modalHeading(__("Query MX Records"))
                        ->modalSubmitAction(false)
                        ->icon("heroicon-m-eye")
                        ->label(__("Query MX Records")),
                    Actions\Action::make("query_txt_records")
                        ->infolist([
                            TextEntry::make("txt_records")
                                ->state(
                                    static fn($record) => self::queryTxtRecords(
                                        $record
                                    )
                                )
                                ->html()
                                ->label(__("Result")),
                        ])
                        ->modalHeading(__("Query TXT Records"))
                        ->modalSubmitAction(false)
                        ->icon("heroicon-m-eye")
                        ->label(__("Query TXT Records")),
                    Actions\Action::make("query_dmarc_record")
                        ->infolist([
                            TextEntry::make("dmarc_record")
                                ->state(
                                    static fn(
                                        $record
                                    ) => self::queryDmarcRecord($record)
                                )
                                ->html()
                                ->label(__("Result")),
                        ])
                        ->modalHeading(__("Query Dmarc Record"))
                        ->modalSubmitAction(false)
                        ->icon("heroicon-m-eye")
                        ->label(__("Query Dmarc Record")),
                ]),
            ])
            ->defaultSort("created_at", "desc");
    }

    public static function getPages(): array
    {
        return [
            "index" => Pages\ListDomains::route("/"),
            "create" => Pages\CreateDomain::route("/create"),
            "edit" => Pages\EditDomain::route("/{record}/edit"),
        ];
    }

    private static function haveClient(array $ids): bool
    {
        return Client::whereIn("domain_id", $ids)->count() > 0;
    }

    private static function cancelDelete(Actions\Action $action): void
    {
        Notification::make()
            ->warning()
            ->title(__("Unable to delete domain"))
            ->body(__("You must delete all clients belongs to the domain."))
            ->send();
        $action->cancel();
    }

    private static function queryMxRecords(Domain $domain): string
    {
        try {
            $records = array_map(
                static fn($record) => self::prettyPrint($record),
                \Amp\Dns\query($domain->name, DnsRecord::MX)
            );
            return implode("<br />", $records);
        } catch (DnsException $e) {
            return $e->getMessage();
        }
    }

    private static function queryTxtRecords(Domain $domain): string
    {
        try {
            $records = array_map(
                static fn($record) => self::prettyPrint($record),
                \Amp\Dns\query($domain->name, DnsRecord::TXT)
            );
            return implode("<br />", $records);
        } catch (DnsException $e) {
            return $e->getMessage();
        }
    }

    private static function queryDmarcRecord(Domain $domain): string
    {
        try {
            $records = array_map(
                static fn($record) => self::prettyPrint($record),
                \Amp\Dns\query("_dmarc." . $domain->name, DnsRecord::TXT)
            );
            return implode("<br />", $records);
        } catch (DnsException $e) {
            return $e->getMessage();
        }
    }

    private static function prettyPrint(DnsRecord $record): string
    {
        return sprintf(
            self::RECORD_FORMAT,
            DnsRecord::getName($record->getType()),
            $record->getValue()
        );
    }
}
