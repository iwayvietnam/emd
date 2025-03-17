<?php declare(strict_types=1);

namespace App\Filament\Pages;

use Filament\Pages\Page;

/**
 * Mail queue page class
 *
 * @package  App
 * @category Filament
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class MailQueue extends Page
{
    protected static ?string $navigationGroup = "System";
    protected static ?string $navigationIcon = "heroicon-o-envelope";
    protected static ?string $slug = 'mail queue';
    protected static string $view = "filament.pages.mail-queue";

    public static function getNavigationLabel(): string
    {
        return __('Mail Queue');
    }
}
