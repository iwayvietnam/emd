<?php declare(strict_types=1);

namespace App\Filament\Resources;

use Amp\Dns\DnsRecord;
use Amp\Dns\DnsException;
use App\Filament\Resources\DkimKeyResource\Pages;
use App\Models\DkimKey;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Symfony\Component\HttpFoundation\Response;
use BackedEnum;
use UnitEnum;

/**
 * Dkim key resource class
 *
 * @package  App
 * @category Filament
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class DkimKeyResource extends Resource
{
    protected static ?string $model = DkimKey::class;
    protected static string|UnitEnum|null $navigationGroup = "Domain";
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;
    protected static ?string $slug = "dkim";

    public static function getNavigationLabel(): string
    {
        return __("DKIM Keys");
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make("domain")->label(__("Domain")),
                TextColumn::make("selector")->label(__("Selector")),
                TextColumn::make("key_bits")->label(__("Key Bits")),
                TextColumn::make("created_at")
                    ->dateTime()
                    ->sortable()
                    ->label(__("Created At")),
            ])
            ->actions([
                ActionGroup::make([
                    Action::make("query_dkim_record")
                        ->infolist([
                            TextEntry::make("dkim_record")
                                ->state(
                                    static fn($record) => self::queryDkimRecord(
                                        $record,
                                    ),
                                )
                                ->html()
                                ->label(__("Result")),
                        ])
                        ->modalHeading(__("Query Dkim Record"))
                        ->modalSubmitAction(false)
                        ->icon(Heroicon::OutlinedEye)
                        ->label(__("Query Dkim Record")),
                    Action::make("export_key")
                        ->label(__("Export Private Key"))
                        ->icon(Heroicon::OutlinedArrowDownTray)
                        ->action(
                            static fn($record) => self::exportPrivateKey(
                                $record,
                            ),
                        ),
                    Action::make("export_csr")
                        ->label(__("Export Dns Record"))
                        ->icon(Heroicon::OutlinedArrowDownTray)
                        ->action(
                            static fn($record) => self::exportDnsRecord(
                                $record,
                            ),
                        ),
                    DeleteAction::make(),
                ]),
            ])
            ->defaultSort("created_at", "desc");
    }

    public static function getPages(): array
    {
        return [
            "index" => Pages\ListDkimKeys::route("/"),
            "create" => Pages\CreateDkimKey::route("/create"),
        ];
    }

    private static function exportPrivateKey(DkimKey $record): Response
    {
        $filePath = tempnam(sys_get_temp_dir(), $record->selector);
        file_put_contents($filePath, $record->private_key);
        return response()
            ->download($filePath, $record->selector . ".key", [
                "Content-Type" => "application/pkcs8",
            ])
            ->deleteFileAfterSend(true);
    }

    private static function exportDnsRecord(DkimKey $record): Response
    {
        $filePath = tempnam(sys_get_temp_dir(), $record->selector);
        file_put_contents($filePath, $record->dns_record);
        return response()
            ->download($filePath, $record->selector . ".txt", [
                "Content-Type" => "plain/txt",
            ])
            ->deleteFileAfterSend(true);
    }

    private static function queryDkimRecord(DkimKey $dkim): string
    {
        try {
            $records = array_map(
                fn($record) => $record->getValue(),
                \Amp\Dns\query(
                    $dkim->selector . "._domainkey." . $dkim->domain,
                    DnsRecord::TXT,
                ),
            );
            return implode("<br />", $records);
        } catch (DnsException $e) {
            return $e->getMessage();
        }
    }
}
