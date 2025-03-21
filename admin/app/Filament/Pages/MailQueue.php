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
                        static fn(MailServerQueue $queue) => Number::fileSize(
                            $queue->message_size
                        )
                    )
                    ->label(__("Message Size")),
            ])
            ->actions([
                ActionGroup::make([
                    TableAction::make("details")
                        ->icon("heroicon-m-eye")
                        ->color("primary")
                        ->label(__("View Details")),
                    TableAction::make("flush")
                        ->icon("heroicon-m-arrow-up-circle")
                        ->color("primary")
                        ->requiresConfirmation()
                        ->action(function (MailServerQueue $queue) {
                            $server = MailServer::find($queue->mail_server);
                            $server->flushQueue([$queue->queue_id]);
                        })
                        ->label(__("Flush")),
                    TableAction::make("delete")
                        ->icon("heroicon-m-trash")
                        ->color("danger")
                        ->requiresConfirmation()
                        ->action(function (MailServerQueue $queue) {
                            $server = MailServer::find($queue->mail_server);
                            $server->deleteQueue([$queue->queue_id]);
                        })
                        ->label(__("Delete")),
                ]),
            ]);
    }
}
