<?php declare(strict_types=1);

namespace App\Filament\Resources\DomainResource\Pages;

use App\Filament\Resources\DomainResource;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

/**
 * Create domain record class
 *
 * @package  App
 * @category Filament
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class CreateDomain extends CreateRecord
{
    protected static string $resource = DomainResource::class;
    protected static bool $canCreateAnother = false;

    public function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make("name")
                ->rules([
                    static fn() => static function (
                        string $attribute,
                        $value,
                        \Closure $fail
                    ) {
                        if (!filter_var($value, FILTER_VALIDATE_DOMAIN)) {
                            $fail(__("The domain name is invalid."));
                        }
                    },
                ])
                ->required()
                ->unique()
                ->label(__("Name")),
            TextInput::make("email")
                ->rules([
                    static fn(Get $get) => static function (
                        string $attribute,
                        $value,
                        \Closure $fail
                    ) use ($get) {
                        if (!Str::endsWith($value, $get("name"))) {
                            $fail(
                                __(
                                    "The email address must match the domain name."
                                )
                            );
                        }
                    },
                ])
                ->email()
                ->required()
                ->unique()
                ->label(__("Email Address")),
            Textarea::make("description")
                ->columnSpan(2)
                ->label(__("Description")),
            TextInput::make("dns_mx_record")->label(__("Mx Record")),
            TextInput::make("dns_dmarc_record")->label(__("Dmarc Record")),
            TextInput::make("dns_ptr_record")->label(__("Ptr Record")),
            TextInput::make("dns_spf_record")->label(__("Spf Record")),
        ]);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return __("Domain has been created!");
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl();
    }
}
