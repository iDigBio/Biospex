<?php

namespace App\Models;

class Notice extends BaseEloquentModel
{
    /**
     * @inheritDoc
     */
    protected $table = 'notices';

    protected $fillable = [
        'message',
        'enabled'
    ];
}