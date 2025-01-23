<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sushi\Sushi;

/**
 * Mail queue model class
 *
 * @package  App
 * @category Model
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class MailQueue extends Model
{
    use Sushi;

    protected $schema = [
        'id' => 'integer',
        'name' => 'string',
        'symbol' => 'string',
        'precision' => 'float'
    ];

    public function getRows()
    {
        return [];
    }
}
