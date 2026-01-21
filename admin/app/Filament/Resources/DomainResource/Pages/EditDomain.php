<?php declare(strict_types=1);

namespace App\Filament\Resources\DomainResource\Pages;

use App\Filament\Resources\DomainResource;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Resources\Pages\EditRecord;

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
