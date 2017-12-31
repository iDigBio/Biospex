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
     * @inhertiDoc
     */
    protected $dates = ['deleted_at'];

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'qualified',
        'short',
        'namespace'
    ];
}
