<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spiritix\LadaCache\Database\LadaCacheTrait;

class FailedJob extends Model
{
    use LadaCacheTrait;

    /**
     * @ineritDoc
     */
    protected $table = 'failed_jobs';
}