<?php declare(strict_types=1);

namespace App\Filament\Pages;

use App\Mail\SendMessage;
use App\Models\Message;
use App\Models\MessageFailure;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Str;

/**
 * Send email test page class
 *
 * @package  App
 * @category Filament
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class SendEmail extends Page implements HasForms
{
    use InteractsWithForms;

    const QUEUE_NAME = "default";
    const UPLOAD_DIR = "attachments";

    protected static ?string $navigationGroup = "System";
    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    protected static string $view = 'filament.pages.send-email';

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('sender')
                    ->label(__('Sender'))
                    ->email()
                    ->required(),
                Textarea::make('recipients')
                    ->label(__('Recipients'))
                    ->required(),
                TextInput::make('subject')
                    ->label(__('Subject'))
                    ->required(),
                RichEditor::make('content')
                    ->label(__('Content'))
                    ->required()
                    ->disableToolbarButtons([
                        'attachFiles',
                    ]),
                FileUpload::make('attachments')
                    ->label(__('Attachments'))
                    ->multiple()
                    ->directory(
                        config("emd.api_upload_dir", self::UPLOAD_DIR)
                    ),
                Toggle::make('should_queue')
                    ->label(__('Should Queue')),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('send')
                ->label(__('Send'))
                ->submit('send'),
        ];
    }

    public function send(): void
    {
        $request = request();
        $data = $this->form->getState();
        $shouldQueue = (bool) $data['should_queue'];
        $recipients = $data['recipients'];

        $message = new Message([
            "user_id" => $request->user()->id,
            "from_name" => $data['sender'],
            "from_email" => $data['sender'],
            "reply_to" => $data['sender'],
            "message_id" => Str::uuid() . '@' . config("emd.app_domain", "yourdomain.com"),
            "subject" => $data['subject'],
            "content" => $data['content'],
            "ip_address" => $request->ip(),
            "recipient" => $data['recipient'],
        ]);
        if ($shouldQueue) {
            // code...
        }
        Notification::make() 
            ->success()
            ->title(__('Message has been sent!'))
            ->send(); 
    }

    public static function explodeRecipients(string $recipients): array
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
