<?php declare(strict_types=1);

namespace App\Filament\Resources\PolicyResource\Pages;

use App\Filament\Resources\PolicyResource;
use Filament\Resources\Pages\CreateRecord;

/**
 * Create policy record class
 *
 * @package  App
 * @category Filament
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class CreatePolicy extends CreateRecord
{
    protected static string $resource = PolicyResource::class;
    protected static bool $canCreateAnother = false;

    protected function getCreatedNotificationTitle(): ?string
    {
        return __("Policy has been created!");
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl();
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data["quota_limit"] = $data["quota_limit"] * static::getResource()::MB;
        return $data;
    }
}
