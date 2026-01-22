<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\DmarcReportResource\Pages;
use App\Filament\Resources\DmarcReportResource\RelationManagers;
use App\Models\DmarcReport;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use BackedEnum;
use UnitEnum;

/**
 * Dmarc report resource class
 *
 * @package  App
 * @category Filament
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class DmarcReportResource extends Resource
{
    protected static ?string $model = DmarcReport::class;
    protected static string | UnitEnum | null $navigationGroup = "Domain";
    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedChartBarSquare;
    protected static ?string $slug = "dmarc";

    public static function getNavigationLabel(): string
    {
        return __("DMARC Reports");
    }

    public static function getRelations(): array
    {
        return [RelationManagers\RecordsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            "index" => Pages\ListDmarcReports::route("/"),
            "scan" => Pages\ScanDmarcReport::route("/scan"),
            "view" => Pages\ViewDmarcReport::route("/{record}"),
        ];
    }

    public static function alignment(string $value): string
    {
        return match ($value) {
            "r" => "relaxed",
            "s" => "strict",
            default => $value,
        };
    }
}
