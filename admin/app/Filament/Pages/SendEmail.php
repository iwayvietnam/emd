<?php declare(strict_types=1);

namespace App\Filament\Pages;

use App\Mail\Message\SendMessage;
use App\Models\Client;
use App\Models\Message;
use App\Models\MessageFailure;
use App\Support\Helper;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Symfony\Component\Mailer\Exception\ExceptionInterface as MailerException;
use BackedEnum;
use UnitEnum;

/**
 * Send email test page class
 *
 * @package  App
 * @category Filament
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class SendEmail extends Page
{
    const QUEUE_NAME = "default";
    const UPLOAD_DIR = "attachments";

    protected static string | UnitEnum | null $navigationGroup = "Email";
    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedInbox;
    protected static ?string $slug = "send-email";
    protected string $view = "filament.pages.send-email";

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public static function getNavigationLabel(): string
    {
        return __("Send Email");
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make("sender")
                ->label(__("Sender"))
                ->email()
                ->required(),
            Textarea::make("recipients")
                ->label(__("Recipients"))
                ->required(),
            TextInput::make("subject")->label(__("Subject"))->required(),
            RichEditor::make("content")
                ->label(__("Content"))
                ->required()
                ->disableToolbarButtons(["attachFiles"]),
            FileUpload::make("attachments")
                ->label(__("Attachments"))
                ->multiple()
                ->previewable(false)
                ->moveFiles()
                ->disk("local")
                ->directory(
                    config("emd.api.upload_dir", self::UPLOAD_DIR) .
                        "/" .
                        request()->user()->email
                ),
            Toggle::make("should_queue")->label(__("Should Queue")),
        ])
        ->statePath("data");
    }

    protected function getFormActions(): array
    {
        return [Action::make("send")->label(__("Send"))->submit("send")];
    }

    public function send(): void
    {
        $user = request()->user();
        $ipAddress = request()->ip();

        $data = $this->form->getState();
        $shouldQueue = (bool) $data["should_queue"];

        $recipients = Helper::explodeRecipients($data["recipients"]);
        $client = Client::firstWhere('sender_address', $data["sender"]);
        foreach ($recipients as $recipient) {
            $message = new Message([
                "user_id" => $user->id,
                "from_name" => $client?->name ?? $user->name,
                "from_email" => $data["sender"],
                "reply_to" => $data["sender"],
                "message_id" =>
                    Str::uuid() .
                    "@" .
                    config("emd.app_domain", "yourdomain.com"),
                "subject" => $data["subject"],
                "content" => $data["content"],
                "ip_address" => $ipAddress,
                "recipient" => $recipient,
            ]);

            $message->uploads = $data["attachments"] ?? [];
            $message->save();

            try {
                $this->form->fill();
                if ($shouldQueue) {
                    Mail::to($message->recipient)->queue(
                        (new SendMessage($message))->onQueue(
                            config("emd.mail.queue_name", self::QUEUE_NAME)
                        )
                    );
                } else {
                    Mail::to($message->recipient)->send(
                        new SendMessage($message)
                    );
                }
                $message->sent_at = now();
                $message->save();
            } catch (MailerException $e) {
                logger()->error($e);
                MessageFailure::create([
                    "message_id" => $message->id,
                    "severity" => __("Send message failed!"),
                    "description" => $e->getMessage(),
                    "failed_at" => now(),
                ]);
                Notification::make()
                    ->danger()
                    ->title(__("Send message failed!"))
                    ->body($e->getMessage())
                    ->send();
                return;
            }
        }

        Notification::make()
            ->success()
            ->title(__("Message has been sent!"))
            ->send();
    }
}
