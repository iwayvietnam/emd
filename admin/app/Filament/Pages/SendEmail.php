<?php declare(strict_types=1);

namespace App\Filament\Pages;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Pages\Page;
use Filament\Pages\Concerns;

/**
 * SendEmail test page class
 *
 * @package  App
 * @category Filament
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class SendEmail extends Page implements HasForms
{
    use Concerns\HasMaxWidth;
    use Concerns\HasTopbar;
    use Concerns\InteractsWithFormActions;
    use InteractsWithForms;

    protected static ?string $navigationGroup = "System";
    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    protected static ?string $slug = "send-email";
    protected static string $view = 'filament.pages.send-email';

    public function form(Form $form): Form
    {
        return $form;
    }

    public function send(): void
    {
        $data = $this->form->getState();
    }

    /**
     * @return array<int | string, string | Form>
     */
    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        TextInput::make('sender')
                            ->label(__('Sender'))
                            ->email()
                            ->required()
                            ->maxLength(255),
                        Textarea::make('recipients')
                            ->label(__('Recipients'))
                            ->required(),
                    ])
                    ->operation('edit')
                    ->statePath('data')
                    ->inlineLabel(),
            ),
        ];
    }
}
