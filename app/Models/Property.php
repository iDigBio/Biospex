<?php 

namespace App\Models;


class Property extends BaseEloquentModel
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
