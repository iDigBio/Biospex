<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spiritix\LadaCache\Database\LadaCacheTrait;

class Property extends Model
{
    use LadaCacheTrait;

    /**
     * @inheritDoc
     */
    protected $table = 'properties';

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'qualified',
        'short',
        'namespace'
    ];
}
