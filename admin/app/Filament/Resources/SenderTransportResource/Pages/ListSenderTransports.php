<?php declare(strict_types=1);

namespace App\Filament\Resources\SenderTransportResource\Pages;

use App\Filament\Resources\SenderTransportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

/**
 * List sender transport records class
 *
 * @package  App
 * @category Filament
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class ListSenderTransports extends ListRecords
{
    protected static string $resource = SenderTransportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
