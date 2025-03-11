<?php declare(strict_types=1);

namespace App\Filament\Resources\ClientResource\Pages;

use App\Models\Domain;
use App\Filament\Resources\ClientResource;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

/**
 * Create client record class
 *
 * @package  App
 * @category Filament
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class CreateClient extends CreateRecord
{
    protected static string $resource = ClientResource::class;
    protected static bool $canCreateAnother = false;

    public function form(Form $form): Form
    {
        return $form->schema([
            Grid::make(3)->schema([
                Select::make("domain_id")
                    ->options(Domain::all()->pluck("name", "id"))
                    ->required()
                    ->searchable()
                    ->label(__("Domain")),
                TextInput::make("name")
                    ->required()
                    ->unique()
                    ->label(__("Name")),
                TextInput::make("sender_address")
                    ->rules([
                        static fn(Get $get) => static function (
                            string $attribute,
                            $value,
                            \Closure $fail
                        ) use ($get) {
                            $domain = Domain::find($get("domain_id"));
                            if (!Str::endsWith($value, $domain->name)) {
                                $fail(
                                    __(
                                        "The sender address must match the domain name."
                                    )
                                );
                            }
                        },
                    ])
                    ->email()
                    ->required()
                    ->unique()
                    ->label(__("Sender Address")),
            ]),
            Textarea::make("description")
                ->columnSpan(2)
                ->label(__("Description")),
        ]);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return __("Client has been created!");
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl();
    }
}
