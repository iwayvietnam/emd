<?php declare(strict_types=1);

namespace App\Filament\Resources\MessageFailureResource\Pages;

use App\Filament\Resources\MessageFailureResource;
use Filament\Resources\Pages\ListRecords;

/**
 * Message failures list.
 *
 * @package  App
 * @category Resources
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class ListMessageFailures extends ListRecords
{
    protected static string $resource = MessageFailureResource::class;
}
