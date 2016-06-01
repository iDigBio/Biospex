<?php 

namespace App\Models;

use Jenssegers\Mongodb\Model as Eloquent;

class Occurrence extends Eloquent
{
    /**
     * Redefine connection to use mongodb
     */
    protected $connection = 'mongodb';

    /**
     * Set primary key
     */
    protected $primaryKey = '_id';

    /**
     * set guarded properties
     */
    protected $guarded = ['_id'];
}
