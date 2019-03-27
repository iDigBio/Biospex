<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spiritix\LadaCache\Database\LadaCacheTrait;

class BaseEloquentModel extends Model
{
    use LadaCacheTrait;
    /**
     * @inheritDoc
     */
    protected $connection = 'mysql';

    /**
     * @inheritDoc
     */
    protected $primaryKey = 'id';
}