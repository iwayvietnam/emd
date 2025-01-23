<?php declare(strict_types=1);

namespace App\Filament\Resources\DomainResource\Pages;

use App\Filament\Resources\DomainResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

/**
 * List domain records class
 *
 * @package  App
 * @category Filament
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class ListDomains extends ListRecords
{
    protected static string $resource = DomainResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label(__("New Domain"))];
    }
}
