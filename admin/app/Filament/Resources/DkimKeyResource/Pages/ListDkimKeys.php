<?php declare(strict_types=1);

namespace App\Filament\Resources\DkimKeyResource\Pages;

use App\Filament\Resources\DkimKeyResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

/**
 * List dkim key record class
 *
 * @package  App
 * @category Filament
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class ListDkimKeys extends ListRecords
{
    protected static string $resource = DkimKeyResource::class;

    public function getTitle(): string
    {
        return __("DKIM Keys");
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make("gen")
                ->url(static::getResource()::getUrl("gen"))
                ->label(__("Generate DKIM Key")),
        ];
    }
}
