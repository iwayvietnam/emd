<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\LimitPeriod;
use App\Filament\Resources\PolicyResource\Pages;
use App\Models\ClientAccess;
use App\Models\Policy;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Number;
use BackedEnum;
use UnitEnum;

/**
 * Policy resource class
 *
 * @package  App
 * @category Filament
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class PolicyResource extends Resource
{
    const MB = 1048576;

    protected static ?string $model = Policy::class;
    protected static string | UnitEnum | null $navigationGroup = "Access Control";
    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedShieldCheck;
    protected static ?string $slug = "policy";

    public static function getNavigationLabel(): string
    {
        return __("Policies");
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make("name")
                ->required()
                ->unique(ignoreRecord: true)
                ->label(__("Name")),
            Textarea::make("description")
                ->columnSpan(2)
                ->label(__("Description")),
            TextInput::make("quota_limit")
                ->required()
                ->integer()
                ->minValue(0)
                ->default(0)
                ->live()
                ->label(__("Quota"))
                ->helperText(
                    __(
                        "Maximum capacity in megabytes that client can send per time unit."
                    )
                ),
            Select::make("quota_period")
                ->options(LimitPeriod::class)
                ->required(static fn(Get $get) => $get("quota_limit") > 0)
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
                        "Maximum amount of message that client can send per time unit."
                    )
                ),
            Select::make("rate_period")
                ->options(LimitPeriod::class)
                ->required(static fn(Get $get) => $get("rate_limit") > 0)
                ->label(__("Rate Time Unit")),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make("name")->sortable()->label(__("Name")),
                TextColumn::make("quota_limit")
                    ->state(
                        static fn(Policy $policy) => implode([
                            Number::fileSize($policy->quota_limit),
                            " ",
                            LimitPeriod::tryFrom(
                                $policy->quota_period
                            )?->getLabel(),
                        ])
                    )
                    ->label(__("Quota")),
                TextColumn::make("rate_limit")
                    ->state(
                        static fn(Policy $policy) => implode([
                            $policy->rate_limit,
                            " Message ",
                            LimitPeriod::tryFrom(
                                $policy->rate_period
                            )?->getLabel(),
                        ])
                    )
                    ->label(__("Rate")),
                TextColumn::make("created_at")
                    ->dateTime()
                    ->sortable()
                    ->label(__("Created At")),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make()->before(static function (
                    Actions\DeleteAction $action
                ) {
                    if (self::haveClientAccess([$action->getRecord()->id])) {
                        self::cancelDelete($action);
                    }
                }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            "index" => Pages\ListPolicies::route("/"),
            "create" => Pages\CreatePolicy::route("/create"),
            "edit" => Pages\EditPolicy::route("/{record}/edit"),
        ];
    }

    private static function haveClientAccess(array $ids): bool
    {
        return ClientAccess::whereIn("policy_id", $ids)->count() > 0;
    }

    private static function cancelDelete(Actions\Action $action): void
    {
        Notification::make()
            ->warning()
            ->title(__("Unable to delete policy"))
            ->body(
                __("You must delete all client accesses belongs to the policy.")
            )
            ->send();
        $action->cancel();
    }
}
