<?php 

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;
use Spiritix\LadaCache\Database\LadaCacheTrait;

class Occurrence extends Model
{
    use LadaCacheTrait;

    /**
     * @inheritDoc
     */
    protected $connection = 'mongodb';

    /**
     * @inheritDoc
     */
    protected $primaryKey = '_id';

    /**
     * @inheritDoc
     */
    protected $guarded = ['_id'];
}
