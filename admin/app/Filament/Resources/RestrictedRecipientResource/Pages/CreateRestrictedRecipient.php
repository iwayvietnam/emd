<?php declare(strict_types=1);

namespace App\Filament\Resources\RestrictedRecipientResource\Pages;

use App\Filament\Resources\RestrictedRecipientResource;
use App\Models\RestrictedRecipient;
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
                ->hint(__("Each recipient is on a line."))
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
        return __("Restrict recipients has been created");
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl();
    }

    protected function handleRecordCreation(array $data): Model
    {
        $recipients = self::explodeRecipients($data["recipients"]);
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
            throw new Halt("Error Create Restrict Recipients");
        }
    }

    private static function explodeRecipients(string $recipients): array
    {
        $addresses = [];
        $lines = array_map(
            static fn($line) => strtolower(trim($line)),
            explode(PHP_EOL, trim($recipients))
        );
        foreach ($lines as $line) {
            if (filter_var($line, FILTER_VALIDATE_EMAIL)) {
                $addresses[] = $line;
            } else {
                $parts = array_map(
                    static fn($part) => trim($part),
                    explode(",", $line)
                );
                foreach ($parts as $part) {
                    if (filter_var($part, FILTER_VALIDATE_EMAIL)) {
                        $addresses[] = $part;
                    }
                }
            }
        }
        return $addresses;
    }
}
