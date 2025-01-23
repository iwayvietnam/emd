<?php declare(strict_types=1);

namespace App\Filament\Resources\DomainResource\Pages;

use App\Filament\Resources\DomainResource;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
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

    public function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make("name")->readonly()->label(__("Name")),
            TextInput::make("email")->readonly()->label(__("Email")),
            Textarea::make("description")
                ->columnSpan(2)
                ->label(__("Description")),
            TextInput::make("dns_mx_record")->label(__("Mx Record")),
            TextInput::make("dns_dmarc_record")->label(__("Dmarc Record")),
            TextInput::make("dns_ptr_record")->label(__("Ptr Record")),
            TextInput::make("dns_spf_record")->label(__("Spf Record")),
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
