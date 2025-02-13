<?php declare(strict_types=1);

namespace App\Filament\Resources\MailServerResource\Pages;

use App\Filament\Resources\MailServerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

/**
 * List mail server records class
 *
 * @package  App
 * @category Filament
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class ListMailServers extends ListRecords
{
    protected static string $resource = MailServerResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label(__("New Mail Server"))];
    }
}
