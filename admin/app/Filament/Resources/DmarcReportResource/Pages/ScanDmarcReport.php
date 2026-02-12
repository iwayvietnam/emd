<?php declare(strict_types=1);

namespace App\Filament\Resources\DmarcReportResource\Pages;

use App\Filament\Resources\DmarcReportResource;
use App\Mail\Dmarc\Scanner;
use Elastic\Elasticsearch\ClientBuilder;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Webklex\PHPIMAP\ClientManager;

/**
 * Scan DMARC report class
 *
 * @package  App
 * @category Filament
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class ScanDmarcReport extends CreateRecord
{
    protected static string $resource = DmarcReportResource::class;
    protected static bool $canCreateAnother = false;

    private static array $imapEncryptions = [
        "none" => "None",
        "starttls" => "STARTTLS",
        "tls" => "TLS",
        "ssl" => "SSL",
    ];

    public function getTitle(): string
    {
        return __("Scan DMARC Report");
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Fieldset::make(__("IMAP Mailbox"))
                ->schema([
                    Grid::make(4)
                        ->columnSpan(2)
                        ->schema([
                            TextInput::make("host")
                                ->required()
                                ->columnSpan(2)
                                ->label(__("IMAP Host")),
                            TextInput::make("port")
                                ->required()
                                ->numeric()
                                ->default(143)
                                ->label(__("IMAP Port")),
                            Select::make("encryption")
                                ->required()
                                ->options(self::$imapEncryptions)
                                ->default("starttls")
                                ->label(__("Encryption")),
                        ]),
                    Grid::make(4)
                        ->columnSpan(2)
                        ->schema([
                            TextInput::make("username")
                                ->required()
                                ->label(__("User Name")),
                            TextInput::make("password")
                                ->required()
                                ->password()
                                ->label(__("Password")),
                            TextInput::make("report_folder")
                                ->required()
                                ->label(__("Report Folder")),
                            TextInput::make("archive_folder")
                                ->required()
                                ->label(__("Archive Folder")),
                        ]),
                ])
                ->columnSpan(2),
            Toggle::make("index_report")
                ->inline(false)
                ->live()
                ->label(__("Index Report")),
            Fieldset::make(__("Elastic Search API"))
                ->schema([
                    Grid::make(3)
                        ->columnSpan(2)
                        ->schema([
                            TextInput::make("elastic_host")
                                ->required()
                                ->url()
                                ->label(__("Host")),
                            TextInput::make("elastic_api_key")
                                ->required()
                                ->label(__("API Key")),
                            TextInput::make("elastic_api_id")
                                ->required()
                                ->label(__("API Id")),
                        ]),
                ])
                ->columnSpan(2)
                ->hidden(static fn(Get $get) => !$get("index_report")),
        ]);
    }

    protected function handleRecordCreation(array $data): Model
    {
        $cm = new ClientManager([
            "options" => [
                "soft_fail" => true,
            ],
        ]);

        $esClient = null;
        if (!empty($data["index_report"])) {
            $esClient = ClientBuilder::create()
                ->setHosts([$data["elastic_host"]])
                ->setApiKey($data["elastic_api_key"], $data["elastic_api_id"])
                ->build();
        }

        $scanner = new Scanner(
            $cm
                ->make([
                    "host" => $data["host"],
                    "port" => $data["port"],
                    "encryption" => $data["encryption"],
                    "username" => $data["username"],
                    "password" => $data["password"],
                ])
                ->connect(),
            $esClient,
        );
        $scanner->scan($data["report_folder"], $data["archive_folder"]);
        $model = self::getResource()::getModel();
        return new $model();
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return __("Scan DMARC Report Completed!");
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()->label(__("Scan"));
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl();
    }
}
