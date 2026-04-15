<?php declare(strict_types=1);

namespace App\Filament\Resources\DomainResource\Pages;

use App\Enums\LimitPeriod;
use App\Filament\Resources\DomainResource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;

/**
 * Edit domain record class
 *
 * @package  App
 * @category Filament
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class EditDomain extends EditRecord
{
    protected static string $resource = DomainResource::class;

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make("name")->readonly()->label(__("Name")),
            TextInput::make("email")->readonly()->label(__("Email")),
            TextInput::make("organization")
                ->columnSpan(2)
                ->label(__("Organization")),
            Textarea::make("description")
                ->columnSpan(2)
                ->label(__("Description")),
            Grid::make(4)
                ->columnSpan(2)
                ->schema([
                    TextInput::make("quota_limit")
                        ->required()
                        ->integer()
                        ->minValue(0)
                        ->default(0)
                        ->live()
                        ->label(__("Quota"))
                        ->helperText(
                            __(
                                "Maximum capacity in megabytes that client can send per time unit.",
                            ),
                        ),
                    Select::make("quota_period")
                        ->options(LimitPeriod::class)
                        ->required(
                            static fn(Get $get) => $get("quota_limit") > 0,
                        )
                        ->label(__("Quota Time Unit")),
                    TextInput::make("rate_limit")
                        ->required()
                        ->integer()
                        ->minValue(0)
                        ->default(0)
                        ->live()
                        ->label(__("Rate"))
                        ->helperText(
                            __(
                                "Maximum amount of message that client can send per time unit.",
                            ),
                        ),
                    Select::make("rate_period")
                        ->options(LimitPeriod::class)
                        ->required(
                            static fn(Get $get) => $get("rate_limit") > 0,
                        )
                        ->label(__("Rate Time Unit")),
                ]),
        ]);
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return __("Domain has been saved!");
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl();
    }
}
