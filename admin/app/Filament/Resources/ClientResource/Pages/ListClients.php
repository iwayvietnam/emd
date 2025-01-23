<?php declare(strict_types=1);

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

/**
 * List client records class
 *
 * @package  App
 * @category Filament
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class ListClients extends ListRecords
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label(__("New Client"))];
    }
}
