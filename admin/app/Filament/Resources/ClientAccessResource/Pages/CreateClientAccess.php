<?php declare(strict_types=1);

namespace App\Filament\Resources\ClientAccessResource\Pages;

use App\Filament\Resources\ClientAccessResource;
use App\Models\Client;
use App\Models\ClientAccess;
use App\Models\Policy;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;

/**
 * Create client access record class
 *
 * @package  App
 * @category Filament
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class CreateClientAccess extends CreateRecord
{
    protected static string $resource = ClientAccessResource::class;
    protected static bool $canCreateAnother = false;

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make("client_id")
                ->options(Client::all()->pluck("name", "id"))
                ->required()
                ->searchable()
                ->label(__("Client")),
            Select::make("policy_id")
                ->options(Policy::all()->pluck("name", "id"))
                ->required()
                ->searchable()
                ->label(__("Policy")),
            Textarea::make("ip_addresses")
                ->required()
                ->columnSpan(2)
                ->hint(__("Each ip address is on a line."))
                ->label(__("Ip Addresses")),
            Hidden::make("verdict")->default("OK"),
        ]);
    }

    protected function handleRecordCreation(array $data): Model
    {
        $addresses = array_filter(
            explode(PHP_EOL, trim($data["ip_addresses"])),
            static fn($ip) => filter_var($ip, FILTER_VALIDATE_IP),
        );
        if (!empty($addresses)) {
            $client = Client::find($data["client_id"]);
            foreach ($addresses as $ip) {
                $model = $this->getModel()::firstOrCreate([
                    "client_id" => $data["client_id"],
                    "policy_id" => $data["policy_id"],
                    "sender" => $client->sender_address,
                    "client_ip" => $ip,
                    "verdict" => $data["verdict"],
                ]);
            }
            return $model;
        } else {
            throw new Halt("Error Create Client Accesses");
        }
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return __("Client accesses has been created!");
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl();
    }
}
