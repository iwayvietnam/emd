<?php declare(strict_types=1);

namespace App\Filament\Resources\PolicyResource\Pages;

use App\Filament\Resources\PolicyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

/**
 * List policy records class
 *
 * @package  App
 * @category Filament
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class ListPolicies extends ListRecords
{
    protected static string $resource = PolicyResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label(__("New Policy"))];
    }
}
