<?php declare(strict_types=1);

namespace App\Filament\Resources\RestrictedRecipientResource\Pages;

use App\Filament\Resources\RestrictedRecipientResource;
use App\Models\RestrictedRecipient;
use App\Support\Helper;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;

/**
 * Create restrict recipient record class
 *
 * @package  App
 * @category Filament
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class CreateRestrictedRecipient extends CreateRecord
{
    protected static string $resource = RestrictedRecipientResource::class;
    protected static bool $canCreateAnother = false;

    public function form(Form $form): Form
    {
        return $form->schema([
            Textarea::make("recipients")
                ->required()
                ->columnSpan(2)
                ->hint(__("Each recipient is on a line or seperated by a comma."))
                ->label(__("Recipients")),
            Hidden::make("verdict")->default("REJECT"),
        ]);
    }

    public function getTitle(): string
    {
        return __("Create Restrict Recipients");
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return __("Restrict recipients have been created!");
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl();
    }

    protected function handleRecordCreation(array $data): Model
    {
        $recipients = Helper::explodeRecipients($data["recipients"]);
        if (!empty($recipients)) {
            $verdict = $data["verdict"];
            foreach ($recipients as $recipient) {
                $model = $this->getModel()::firstOrCreate([
                    "recipient" => $recipient,
                    "verdict" => $verdict,
                ]);
            }
            RestrictedRecipient::clearCache();
            return $model;
        } else {
            throw new Halt("Error create restrict recipients!");
        }
    }
}
