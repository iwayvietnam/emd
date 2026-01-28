<?php declare(strict_types=1);

namespace App\Filament\Resources\DmarcReportResource\Pages;

use App\Filament\Resources\DmarcReportResource;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;

/**
 * View DMARC report class
 *
 * @package  App
 * @category Filament
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class ViewDmarcReport extends ViewRecord
{
    protected static string $resource = DmarcReportResource::class;

    public function getTitle(): string
    {
        return __("View DMARC Report");
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Fieldset::make(__("Metadata"))->schema([
                TextEntry::make("report_id")->label(__("Report Id")),
                TextEntry::make("org_name")->label(__("Org Name")),
                TextEntry::make("org_email")->label(__("Org Email")),
                TextEntry::make("extra_contact")->label(__("Extra Contact")),
                TextEntry::make("date_begin")->label(__("Date Begin")),
                TextEntry::make("date_end")->label(__("Date End")),
            ]),
            Fieldset::make(__("Policy"))->schema([
                TextEntry::make("domain")->label(__("Domain")),
                TextEntry::make("percentage")->label(__("Percentage")),
                TextEntry::make("adkim")
                    ->state(
                        static fn($record) => static::getResource()::alignment(
                            $record->aspf,
                        ),
                    )
                    ->label(__("Dkim Alignment")),
                TextEntry::make("aspf")
                    ->state(
                        static fn($record) => static::getResource()::alignment(
                            $record->aspf,
                        ),
                    )
                    ->label(__("Spf Alignment")),
                TextEntry::make("policy")->label(__("Policy")),
                TextEntry::make("subdomain_policy")->label(
                    __("Subdomain Policy"),
                ),
            ]),
        ]);
    }
}
