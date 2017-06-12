<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
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
