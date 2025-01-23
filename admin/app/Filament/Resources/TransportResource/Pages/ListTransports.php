<?php declare(strict_types=1);

namespace App\Filament\Resources\TransportResource\Pages;

use App\Filament\Resources\TransportResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

/**
 * List transport records class
 *
 * @package  App
 * @category Filament
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class ListTransports extends ListRecords
{
    protected static string $resource = TransportResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label(__("New Transport"))];
    }
}
