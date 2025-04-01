<?php declare(strict_types=1);

namespace App\Filament\Resources\PassportClientResource\Pages;

use App\Filament\Resources\PassportClientResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

/**
 * List passport client records class
 *
 * @package  App
 * @category Filament
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class ListPassportClients extends ListRecords
{
    protected static string $resource = PassportClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->createAnother(false)
                ->label(__("New Passport Client")),
        ];
    }
}
