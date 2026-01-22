<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use BackedEnum;
use UnitEnum;

/**
 * User resource class
 *
 * @package  App
 * @category Filament
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static string | UnitEnum | null $navigationGroup = "System";
    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedUser;
    protected static ?string $slug = "user";

    public static function getRelations(): array
    {
        return [
            RelationManagers\PassportClientsRelationManager::class,
            RelationManagers\AccessTokensRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            "index" => Pages\ListUsers::route("/"),
            "create" => Pages\CreateUser::route("/create"),
            "edit" => Pages\EditUser::route("/{record}/edit"),
        ];
    }
}
