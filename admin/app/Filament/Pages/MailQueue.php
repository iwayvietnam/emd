<?php declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\MailServer;
use App\Models\MailServerQueue;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\RenderHook;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Symfony\Component\HttpFoundation\Response;
use BackedEnum;
use UnitEnum;

/**
 * Mail queue page class
 *
 * @package  App
 * @category Filament
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class MailQueue extends Page implements HasTable
{
    use InteractsWithTable;

    const MAIL_QUEUE_FORM_AFTER = "panels::mail.queue.form.after";
    const MAIL_QUEUE_FORM_BEFORE = "panels::mail.queue.form.before";

    protected static string|UnitEnum|null $navigationGroup = "System";
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQueueList;
    protected static ?string $slug = "mail-queue";
    protected string $view = "filament.pages.mail-queue";

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
        // $this->form->fill(session()->get(MailServerQueue::class));
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)->schema([
                    Select::make("mail_server")
                        ->options(MailServer::all()->pluck("name", "id"))
                        ->label(__("Mail Server")),
                    TextInput::make("config_dir")
                        ->default(MailServer::CONFIG_DIR)
                        ->label(__("Config Dir")),
                ]),
            ])
            ->statePath("data");
    }

    protected function getFormActions(): array
    {
        return [
            Action::make("list")
                ->action("listMailQueue")
                ->label(__("List Mail Queue")),
        ];
    }

    public function listMailQueue(): void
    {
        session()->put(MailServerQueue::class, $this->form->getState());
        $this->dispatch('refreshTable');
        // redirect($this->getUrl());
    }

    public function table(Table $table): Table
    {
        return $table
            // ->query(MailServerQueue::query())
            ->records(function (array $columnSearches, int $page, int $recordsPerPage): Paginator {
                return $this->mailServerQueues($columnSearches, $page, $recordsPerPage);
            })
            ->columns([
                TextColumn::make("arrival_time")
                    ->state(
                        static fn(array $record) => date(
                            "Y-m-d H:i:s",
                            (int) $record["arrival_time"],
                        ),
                    )
                    ->label(__("Arrival Time")),
                TextColumn::make("queue_name")->label(__("Queue Name")),
                TextColumn::make("queue_id")->label(__("Queue Id")),
                TextColumn::make("sender")->searchable()->label(__("Sender")),
                TextColumn::make("recipients")
                    ->searchable()
                    ->label(__("Recipients")),
                TextColumn::make("message_size")
                    ->state(
                        static fn(array $record) => Number::fileSize(
                            $record["message_size"],
                        ),
                    )
                    ->label(__("Message Size")),
            ])
            ->bulkActions([
                BulkAction::make("delete-all")
                    ->icon(Heroicon::OutlinedTrash)
                    ->color("danger")
                    ->action(function ($records) {
                        $formState = session()->get(MailServerQueue::class);
                        $server = MailServer::find(
                            $formState["mail_server"] ?? 0,
                        );
                        $server?->deleteQueue(
                            $records
                                ->map(
                                    fn(
                                        array $record,
                                    ) => $record["queue_id"],
                                )
                                ->toArray(),
                            $formState["config_dir"] ?? MailServer::CONFIG_DIR,
                        );
                        $this->dispatch('refreshTable');
                        // redirect($this->getUrl());
                    })
                    ->label(__("Delete")),
            ])
            ->actions([
                ActionGroup::make([
                    Action::make("export")
                        ->icon(Heroicon::OutlinedArrowDownCircle)
                        ->color("primary")
                        ->action(
                            static fn(
                                array $record,
                            ) => self::exportQueueContent($record),
                        )
                        ->label(__("Export Content")),
                    Action::make("flush")
                        ->icon(Heroicon::OutlinedArrowUpCircle)
                        ->color("primary")
                        ->action(function (array $record) {
                            $server = MailServer::find($record["mail_server"]);
                            $server->flushQueue([$record["queue_id"]]);
                            $this->dispatch('refreshTable');
                            // redirect($this->getUrl());
                        })
                        ->label(__("Flush")),
                    Action::make("delete")
                        ->icon(Heroicon::OutlinedTrash)
                        ->color("danger")
                        ->action(function (array $record) {
                            $server = MailServer::find($record["mail_server"]);
                            $server->deleteQueue([$record["queue_id"]]);
                            $this->dispatch('refreshTable');
                            // redirect($this->getUrl());
                        })
                        ->label(__("Delete")),
                ]),
            ]);
    }

    #[On('refreshTable')]
    public function refreshTable(): void
    {
        $this->getTable()->records(function (array $columnSearches, int $page, int $recordsPerPage): Paginator {
            return $this->mailServerQueues($columnSearches, $page, $recordsPerPage);
        });
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            RenderHook::make(self::MAIL_QUEUE_FORM_AFTER),
            $this->getFormContentComponent(),
            RenderHook::make(self::MAIL_QUEUE_FORM_BEFORE),
        ]);
    }

    public function getFormContentComponent(): Component
    {
        return Form::make([EmbeddedSchema::make("form")])
            ->id("form")
            ->livewireSubmitHandler("listMailQueue")
            ->footer([
                Actions::make($this->getFormActions())
                    ->alignment(Alignment::Start)
                    ->fullWidth(false)
                    ->key("form-actions"),
            ]);
    }

    private function mailServerQueues(array $columnSearches, int $page, int $recordsPerPage): Paginator
    {
        $formState = $this->form->getState();
        $server = MailServer::find($formState["mail_server"] ?? 0);
        $queues =
            $server?->listQueue(
                $formState["config_dir"] ?? MailServer::CONFIG_DIR,
            ) ?? [];

        $records = collect($queues)->when(
            filled($columnSearches['sender'] ?? null),
            fn (Collection $data) => $data->filter(
                fn (array $record): bool => str_contains(
                    Str::lower($record['sender']),
                    Str::lower($columnSearches['sender'])
                ),
            ),
        )->when(
            filled($columnSearches['recipients'] ?? null),
            fn (Collection $data) => $data->filter(
                fn (array $record): bool => str_contains(
                    Str::lower($record['recipients']),
                    Str::lower($columnSearches['recipients'])
                ),
            ),
        );
        return new Paginator(
            $records->forPage($page, $recordsPerPage),
            total: count($records),
            perPage: $recordsPerPage,
            currentPage: $page,
        );
    }

    private static function exportQueueContent(
        array $record,
    ): Response {
        $server = MailServer::find($record["mail_server"]);
        $content = $server->queueContent($record["queue_id"]);

        $filePath = tempnam(sys_get_temp_dir(), $record[""]);
        file_put_contents($filePath, $content);
        return response()
            ->download($filePath, $record["queue_id"] . ".eml", [
                "Content-Type" => "application/octet-stream",
            ])
            ->deleteFileAfterSend(true);
    }
}
