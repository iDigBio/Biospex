<?php namespace App\Models;

use Jenssegers\Mongodb\Model as Eloquent;

class AmChart extends Eloquent
{
    /**
     * Redefine connection to use mongodb
     */
    protected $connection = 'mongodb';

    /**
     * Collection name.
     * 
     * @var string
     */
    protected $collection = 'amcharts';

    /**
     * Set primary key
     */
    protected $primaryKey = '_id';

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * set guarded properties
     */
    protected $guarded = ['_id'];

    /**
     * Fill data.
     * 
     * @var array
     */
    protected $fillable = ['project_id', 'data'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
