<?php declare(strict_types=1);

namespace App\Filament\Resources\DomainResource\Pages;

use App\Filament\Resources\DomainResource;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
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

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make("name")
                ->rules([
                    static fn() => static function (
                        string $attribute,
                        $value,
                        \Closure $fail,
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
                        \Closure $fail,
                    ) use ($get) {
                        if (!Str::endsWith($value, $get("name"))) {
                            $fail(__(
                                "The email address must match the domain name.",
                            ));
                        }
                    },
                ])
                ->email()
                ->required()
                ->unique()
                ->label(__("Email Address")),
            TextInput::make("organization")
                ->columnSpan(2)
                ->label(__("Organization")),
            Textarea::make("description")
                ->columnSpan(2)
                ->label(__("Description")),
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
