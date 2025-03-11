<?php declare(strict_types=1);

namespace App\Filament\Pages;

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
    use Concerns\HasMaxWidth;
    use Concerns\HasTopbar;
    use Concerns\InteractsWithFormActions;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static string $view = 'filament.pages.send-email';
}
