<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExportQueue extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'export_queues';

    /**
     * @var array
     */
    protected $fillable = [
        'group_id',
        'expedition_id',
        'total',
        'remaining',
        'error'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function expedition()
    {
        return $this->belongsTo(Expedition::class);
    }

}
