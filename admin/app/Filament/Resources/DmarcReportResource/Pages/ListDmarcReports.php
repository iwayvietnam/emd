<?php declare(strict_types=1);

namespace App\Filament\Resources\DmarcReportResource\Pages;

use App\Filament\Resources\DmarcReportResource;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * List DMARC reports class
 *
 * @package  App
 * @category Filament
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class ListDmarcReports extends ListRecords
{
    protected static string $resource = DmarcReportResource::class;

    public function getTitle(): string
    {
        return __("DMARC Reports");
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make("org_name")
                    ->searchable()
                    ->label(__("Org Name")),
                TextColumn::make("domain")->searchable()->label(__("Domain")),
                TextColumn::make("policy")->label(__("Policy")),
                TextColumn::make("adkim")
                    ->state(
                        static fn($record) => static::getResource()::alignment(
                            $record->adkim,
                        ),
                    )
                    ->label(__("Dkim Alignment")),
                TextColumn::make("aspf")
                    ->state(
                        static fn($record) => static::getResource()::alignment(
                            $record->aspf,
                        ),
                    )
                    ->label(__("Spf Alignment")),
                TextColumn::make("percentage")->label(__("Percentage")),
                TextColumn::make("date_begin")
                    ->dateTime("Y-M-d")
                    ->label(__("Date Begin")),
                TextColumn::make("date_end")
                    ->dateTime("Y-M-d")
                    ->label(__("Date End")),
            ])
            ->filters([
                Filter::make("filter")
                    ->form([
                        TextInput::make("org_name")->label(__("Org Name")),
                        TextInput::make("domain")->label(__("Domain")),
                    ])
                    ->query(
                        static fn(Builder $query, array $data) => $query
                            ->when(
                                $data["org_name"],
                                static fn(
                                    Builder $query,
                                    string $org,
                                ) => $query->where(
                                    "org_name",
                                    "like",
                                    "%" . trim($org) . "%",
                                ),
                            )
                            ->when(
                                $data["domain"],
                                static fn(
                                    Builder $query,
                                    string $domain,
                                ) => $query->where(
                                    "domain",
                                    "like",
                                    "%" . trim($domain) . "%",
                                ),
                            ),
                    ),
            ])
            ->defaultSort("date_begin", "desc");
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make("scan")
                ->url(static::getResource()::getUrl("scan"))
                ->label(__("Scan DMARC")),
        ];
    }
}
