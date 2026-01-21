<?php declare(strict_types=1);

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

/**
 * Edit user record class
 *
 * @package  App
 * @category Filament
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make("name")->required()->label(__("Name")),
            TextInput::make("email")->readonly()->label(__("Email Address")),
            TextInput::make("password")
                ->password()
                ->dehydrateStateUsing(fn($state) => Hash::make($state))
                ->dehydrated(fn($state) => filled($state))
                ->required(false)
                ->label(__("Password")),
        ]);
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return __("User has been saved!");
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl();
    }
}
