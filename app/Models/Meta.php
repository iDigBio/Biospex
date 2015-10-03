<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\BelongsToProjectTrait;

class Meta extends Model
{
    use SoftDeletes;
    use BelongsToProjectTrait;

    /**
     * Protect date columns.
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'metas';

    /**
     * Fillable columns.
     * @var array
     */
    protected $fillable = [
        'project_id',
        'xml'
    ];
}
