<?php declare(strict_types=1);

namespace App\Filament\Resources\DkimKeyResource\Pages;

use App\Filament\Resources\DkimKeyResource;
use App\Models\Domain;
use App\Models\DkimKey;
use Filament\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Pages\CreateRecord;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Crypt\RSA;

/**
 * Create dkim key record class
 *
 * @package  App
 * @category Filament
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class CreateDkimKey extends CreateRecord
{
    protected static string $resource = DkimKeyResource::class;
    protected static bool $canCreateAnother = false;

    public function getTitle(): string
    {
        return __("Create DKIM Key");
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Grid::make(3)->schema([
                Select::make("domain_id")
                    ->options(
                        Domain::whereNotIn(
                            "id",
                            DkimKey::all()->pluck("domain_id")
                        )->pluck("name", "id")
                    )
                    ->required()
                    ->unique()
                    ->searchable()
                    ->label(__("Domain")),
                TextInput::make("selector")
                    ->rules([
                        static fn(Get $get) => function (
                            string $attribute,
                            $value,
                            \Closure $fail
                        ) use ($get) {
                            $exist = DkimKey::where("selector", $value)
                                ->where("domain_id", $get("domain_id"))
                                ->count();
                            if ($exist > 0) {
                                $fail(__("The selector already exist."));
                            }
                        },
                    ])
                    ->required()
                    ->label(__("Selector")),
                Select::make("key_bits")
                    ->required()
                    ->options([
                        1024 => "1024 bits",
                        1536 => "1536 bits",
                        2048 => "2048 bits",
                    ])
                    ->default(1024)
                    ->label(__("Key Bits")),
            ]),
            Textarea::make("private_key")
                ->required()
                ->columnSpan(2)
                ->hintActions([
                    Action::make("genarate")
                        ->label(__("Genarate Private Key"))
                        ->action(
                            static fn(Get $get, Set $set) => $set(
                                "private_key",
                                self::genaratePrivateKey((int) $get("key_bits"))
                            )
                        ),
                ])
                ->label(__("Private Key")),
        ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $privateKey = PublicKeyLoader::loadPrivateKey($data["private_key"]);

        $selector = $data["selector"];
        $dnsRecord = "$selector._domainkey\tIN\tTXT\t ( \"v=DKIM1; k=rsa; h=sha256; t=s; p=";
        $pubLines = explode(
            "\n",
            $privateKey->getPublicKey()->toString("PKCS8")
        );
        foreach ($pubLines as $line) {
            if (strpos($line, "-----") !== 0) {
                $dnsRecord .= trim($line);
            }
        }
        $dnsRecord .= '" ) ;';

        $data["dns_record"] = $dnsRecord;
        $data["domain"] = Domain::find((int) $data["domain_id"])->name;
        return $data;
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()->label(__("Create"));
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return __("DKIM key has been created!");
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl();
    }

    private function genaratePrivateKey(int $keyBits = 1024): string
    {
        return RSA::createKey($keyBits)->toString("PKCS8");
    }
}
