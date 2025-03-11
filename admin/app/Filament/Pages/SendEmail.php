<?php declare(strict_types=1);

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Pages\Concerns;

/**
 * SendEmail test page class
 *
 * @package  App
 * @category Filament
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class SendEmail extends Page
{
    const QUEUE_NAME = "default";

    use Concerns\HasMaxWidth;
    use Concerns\HasTopbar;
    use Concerns\InteractsWithFormActions;

    protected static ?string $navigationGroup = "System";
    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    protected static ?string $slug = "send-email";
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
                    ->multiple(),
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
        $data = $this->form->getState();
        $shouldQueue = (bool) $data['should_queue'];
        if ($shouldQueue) {
            // code...
        }
        Notification::make() 
            ->success()
            ->title(__('Message has been sent!'))
            ->send(); 
    }
}
