<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\SSHKeyAlgorithm;
use App\Filament\Resources\MailServerResource\Pages;
use App\Models\MailServer;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use phpseclib3\Crypt\Common\PrivateKey;
use phpseclib3\Crypt\EC;
use phpseclib3\Crypt\RSA;
use BackedEnum;
use UnitEnum;

/**
 * Mail server resource class
 *
 * @package  App
 * @category Filament
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class MailServerResource extends Resource
{
    protected static ?string $model = MailServer::class;
    protected static string | UnitEnum | null $navigationGroup = "System";
    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedServerStack;
    protected static ?string $slug = "mail-server";

    private static array $rsaKeySizes = [
        1024 => "1024 bits",
        2048 => "2048 bits",
        4096 => "4096 bits",
    ];

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make()->columns(5)->schema([
                TextInput::make("name")
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->label(__("Name")),
                TextInput::make("ip_address")
                    ->required()
                    ->ipv4()
                    ->label(__("Ip Address")),
                TextInput::make("ssh_user")->required()->label(__("SSH User")),
                TextInput::make("ssh_port")
                    ->required()
                    ->integer()
                    ->minValue(0)
                    ->default(22)
                    ->label(__("SSH Port")),
                TextInput::make("sudo_password")
                    ->required()
                    ->password()
                    ->label(__("Sudo Password")),
            ]),
            Textarea::make("ssh_private_key")
                ->columnSpan(2)
                ->required()
                ->hintActions([
                    Actions\Action::make("genarate")
                        ->label(__("Genarate SSH Keys"))
                        ->form([
                            Fieldset::make(__("Key Settings"))->schema([
                                Select::make("key_algorithm")
                                    ->options(SSHKeyAlgorithm::class)
                                    ->default(SSHKeyAlgorithm::Ed25519->value)
                                    ->selectablePlaceholder(false)
                                    ->label(__("Key Algorithm")),
                                Select::make("rsa_key_size")
                                    ->default(2048)
                                    ->options(self::$rsaKeySizes)
                                    ->selectablePlaceholder(false)
                                    ->label(__("Rsa Key Size")),
                            ]),
                        ])
                        ->action(
                            static fn(
                                Set $set,
                                array $data
                            ) => self::genarateSSHKeys($set, $data)
                        ),
                ])
                ->label(__("SSH Private Key")),
            Textarea::make("ssh_public_key")
                ->columnSpan(2)
                ->required()
                ->label(__("SSH Public Key")),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make("name")->label(__("Name")),
            TextEntry::make("ip_address")->label(__("IP Address")),
            TextEntry::make("ssh_public_key")
                ->columnSpan(2)
                ->label(__("SSH Public Key")),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make("name")->sortable()->label(__("Name")),
                TextColumn::make("ip_address")->label(__("Ip Address")),
                TextColumn::make("created_at")
                    ->dateTime()
                    ->sortable()
                    ->label(__("Created At")),
            ])
            ->actions([
                Actions\ViewAction::make(),
                Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            "index" => Pages\ListMailServers::route("/"),
            "create" => Pages\CreateMailServer::route("/create"),
        ];
    }

    private static function genarateSSHKeys(Set $set, array $data): void
    {
        $keyAlgo =
            SSHKeyAlgorithm::tryFrom((int) $data["key_algorithm"]) ??
            SSHKeyAlgorithm::Ed25519;
        $privateKey = self::createKey($keyAlgo, (int) $data["rsa_key_size"]);
        $set("ssh_private_key", $privateKey->toString("OpenSSH"));
        $set(
            "ssh_public_key",
            $privateKey->getPublicKey()->toString("OpenSSH")
        );
    }

    private static function createKey(
        SSHKeyAlgorithm $keyAlgo,
        int $rsaKeySize = 2048
    ): PrivateKey {
        if (!in_array($rsaKeySize, array_keys(self::$rsaKeySizes))) {
            $rsaKeySize = 2048;
        }
        return match ($keyAlgo) {
            SSHKeyAlgorithm::Rsa => RSA::createKey($rsaKeySize),
            default => EC::createKey(strtolower($keyAlgo->name)),
        };
    }
}
