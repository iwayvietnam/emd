<?php declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\MailServer;
use App\Models\MailServerQueue;
use Filament\Actions\Action as FormAction;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Number;

/**
 * Mail queue page class
 *
 * @package  App
 * @category Filament
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class MailQueue extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationGroup = "System";
    protected static ?string $navigationIcon = "heroicon-o-envelope";
    protected static ?string $slug = "mail-queue";
    protected static string $view = "filament.pages.mail-queue";

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public static function getNavigationLabel(): string
    {
        return __("Mail Queue");
    }

    public function mount(): void
    {
        $this->form->fill(session()->get(MailServerQueue::class));
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make("mail_server")
                    ->options(MailServer::all()->pluck("name", "id"))
                    ->label(__("Mail Server")),
                Grid::make(2)->schema([
                    TextInput::make("sender")->label(__("Sender"))->email(),
                    TextInput::make("recipient")
                        ->label(__("Recipient"))
                        ->email(),
                ]),
            ])
            ->statePath("data");
    }

    protected function getFormActions(): array
    {
        return [
            FormAction::make("list")
                ->label(__("List Mail Queue"))
                ->submit("listMailQueue"),
        ];
    }

    public function listMailQueue(): void
    {
        session()->put(MailServerQueue::class, $this->form->getState());
        redirect($this->getUrl());
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(MailServerQueue::query())
            ->columns([
                TextColumn::make("queue_name")->label(__("Queue Name")),
                TextColumn::make("queue_id")->label(__("Queue Id")),
                TextColumn::make("sender")->label(__("Sender")),
                TextColumn::make("recipients")->label(__("Recipients")),
                TextColumn::make("message_size")
                    ->state(
                        static fn(MailServerQueue $record) => Number::fileSize(
                            $record->message_size
                        )
                    )
                    ->label(__("Message Size")),
            ])
            ->bulkActions([
                BulkAction::make("delete-all")
                    ->icon("heroicon-m-trash")
                    ->color("danger")
                    ->action(function ($records) {
                        $formState = session()->get(MailServerQueue::class);
                        $server = MailServer::find($formState["mail_server"] ?? 0);
                        $server?->deleteQueue(
                            $records->map(
                                fn (MailServerQueue $record) => $record->queue_id
                            )->toArray()
                        );
                        redirect($this->getUrl());
                    })
                    ->label(__("Delete")),
            ])
            ->actions([
                ActionGroup::make([
                    TableAction::make("export")
                        ->icon("heroicon-m-arrow-down-circle")
                        ->color("primary")
                        ->label(__("Export Content")),
                    TableAction::make("flush")
                        ->icon("heroicon-m-arrow-up-circle")
                        ->color("primary")
                        ->action(function (MailServerQueue $record) {
                            $server = MailServer::find($record->mail_server);
                            $server->flushQueue([$record->queue_id]);
                            redirect($this->getUrl());
                        })
                        ->label(__("Flush")),
                    TableAction::make("delete")
                        ->icon("heroicon-m-trash")
                        ->color("danger")
                        ->action(function (MailServerQueue $record) {
                            $server = MailServer::find($record->mail_server);
                            $server->deleteQueue([$record->queue_id]);
                            redirect($this->getUrl());
                        })
                        ->label(__("Delete")),
                ]),
            ]);
    }

    private static function exportQueueContent(MailServerQueue $record): Response
    {
        $server = MailServer::find($record->mail_server);
        $content = $server->queueContent($record->queue_id);

        $filePath = tempnam(sys_get_temp_dir(), $record->queue_id);
        file_put_contents($filePath, $content);
        return response()
            ->download($filePath, $record->queue_id . ".eml", [
                "Content-Type" => "application/octet-stream",
            ])
            ->deleteFileAfterSend(true);
    }
}
