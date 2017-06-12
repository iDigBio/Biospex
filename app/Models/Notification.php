<?php
namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends BaseEloquentModel
{
    use SoftDeletes;

    /**
     * Enable soft delete.
     *
     * @var boolean
     */
    protected $softDelete = true;

    /**
     * @inheritDoc
     */
    protected $table = 'notifications';

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'user_id',
        'title',
        'message'
    ];

    /**
     * @inheritDoc
     */
    protected $dates = ['deleted_at'];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}