<?php declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\MailServer;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;

/**
 * Mail queue page class
 *
 * @package  App
 * @category Filament
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class MailQueue extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationGroup = "System";
    protected static ?string $navigationIcon = "heroicon-o-envelope";
    protected static ?string $slug = 'mail queue';
    protected static string $view = "filament.pages.mail-queue";

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public static function getNavigationLabel(): string
    {
        return __('Mail Queue');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make("mail_server")
                    ->options(MailServer::all()->pluck("name", "id"))
                    ->required()
                    ->label(__("Mail Server")),
            ])
            ->statePath("data");
    }

    protected function getFormActions(): array
    {
        return [Action::make("list")->label(__("List Mail Queue"))->submit("listMailQueue")];
    }

    public function listMailQueue(): void
    {
    }
}
