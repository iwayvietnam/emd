<?php declare(strict_types=1);

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\Domain;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

/**
 * Create user record class
 *
 * @package  App
 * @category Filament
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
    protected static bool $canCreateAnother = false;

    public function form(Form $form): Form
    {
        $domains = [];
        return $form->schema([
            TextInput::make("name")->required()->label(__("Name")),
            TextInput::make("email")
                ->email()
                ->required()
                ->unique()
                ->endsWith(Domain::all()->pluck("name", "id"))
                ->validationMessages([
                    "unique" => __("The email address has already been taken."),
                    "ends_with" => __(
                        "The email address does not belong to any domains."
                    ),
                ])
                ->label(__("Email Address")),
            TextInput::make("password")
                ->password()
                ->dehydrateStateUsing(fn($state) => Hash::make($state))
                ->dehydrated(fn($state) => filled($state))
                ->required()
                ->label(__("Password")),
        ]);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return __("User has been created!");
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl();
    }
}
