<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Dkim key model class
 *
 * @package  App
 * @category Models
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class DkimKey extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "dkim_keys";

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        "domain_id",
        "domain",
        "selector",
        "key_bits",
        "private_key",
        "dns_record",
    ];

    public static function signingTable(): array
    {
        $rows = [];

        foreach (static::all() as $item) {
            $rows[$item->domain] = implode([
                "*@",
                $item->domain,
                " ",
                $item->selector,
                "._domainkey.",
                $item->domain,
            ]);
        }

        return $rows;
    }

    public static function keyTable(): array
    {
        $rows = [];
        $keyDir = config("emd.opendkim.keys_directory");

        foreach (static::all() as $item) {
            $rows[$item->domain] = implode([
                $item->selector,
                "._domainkey.",
                $item->domain,
                " ",
                $item->domain,
                ":",
                $item->selector,
                ":",
                $keyDir,
                DIRECTORY_SEPARATOR,
                $item->domain,
                DIRECTORY_SEPARATOR,
                $item->selector,
                ".private",
            ]);
        }

        return $rows;
    }
}
