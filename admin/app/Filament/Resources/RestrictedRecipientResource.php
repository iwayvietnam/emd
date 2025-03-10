<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\RestrictedRecipientResource\Pages;
use App\Models\RestrictedRecipient;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Tables\Actions;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Restricted recipient resource class
 *
 * @package  App
 * @category Filament
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class RestrictedRecipientResource extends Resource
{
    protected static ?string $model = RestrictedRecipient::class;
    protected static ?string $navigationGroup = "Access Control";
    protected static ?string $navigationIcon = "heroicon-o-lock-closed";
    protected static ?string $slug = "restricted";

    public static function getNavigationLabel(): string
    {
        return __("Restricted Recipients");
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make("recipient")
                    ->searchable()
                    ->label(__("Recipient")),
                TextColumn::make("verdict")->label(__("Verdict")),
                TextColumn::make("created_at")
                    ->dateTime()
                    ->sortable()
                    ->label(__("Created At")),
            ])
            ->actions([
                Actions\Action::make("verify")
                    ->infolist([
                        TextEntry::make("verify")->state(
                            static fn($record) => self::verifyRecipient(
                                $record->recipient
                            )
                        ),
                    ])
                    ->modalSubmitAction(false)
                    ->icon("heroicon-m-eye")
                    ->label(__("Verify")),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ])->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            "index" => Pages\ListRestrictedRecipients::route("/"),
            "create" => Pages\CreateRestrictedRecipient::route("/create"),
        ];
    }

    private static function verifyRecipient(string $recipient): string
    {
        [$_, $domain] = explode("@", $recipient);
        if (!checkdnsrr($domain, "MX")) {
            return __("No suitable MX records found.");
        }
        getmxrr($domain, $hosts, $weight);
        if (!empty($hosts)) {
            $mx = $hosts[array_search(min($weight), $weight)];
            return self::testConnect($mx, $recipient);
        }
        return $recipient;
    }

    private static function testConnect(string $mx, string $recipient): string
    {
        $appDomain = env("APP_DOMAIN", "emd.org.vn");
        $connect = @fsockopen($mx, 25);
        if (preg_match("/^220/i", $out = fgets($connect))) {
            fputs($connect, "HELO $appDomain\r\n");
            $helo = fgets($connect);

            fputs($connect, "MAIL FROM: <sender@$appDomain>\r\n");
            $from = fgets($connect);

            fputs($connect, "RCPT TO: <$recipient>\r\n");
            $to = fgets($connect);

            fputs($connect, "QUIT");
        }
        fclose($connect);

        return $to;
    }
}
