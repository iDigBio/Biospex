<?php 

namespace App\Models;

class Property extends BaseEloquentModel
{

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
