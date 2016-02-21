<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invite extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'invites';

    protected $fillable = [
        'group_id',
        'email',
        'code'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Find invite by code
     *
     * @param $code
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function findByCode($code)
    {
        return $this->whereCode($code)->first();
    }

    /**
     * Find duplicate
     *
     * @param $id
     * @param $email
     * @return mixed
     */
    public function checkDuplicate($id, $email)
    {
        return $this->whereGroupId($id)->whereEmail($email)->first();
    }

    /**
     * Retrun invites by group id
     *
     * @param $id
     * @return mixed
     */
    public function findByGroupId($id)
    {
        return $this->whereGroupId($id)->get();
    }
}
