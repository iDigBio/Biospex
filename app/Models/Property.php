<?php namespace Biospex\Models;

use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'properties';

    /**
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * @var array
     */
    protected $fillable = [
        'qualified',
        'short',
        'namespace'
    ];

    /**
     * Find by qualified name
     *
     * @param $name
     * @return mixed
     */
    public function findByQualified($name)
    {
        return $this->where('qualified', '=', $name)->first();
    }

    /**
     * Find by short name
     *
     * @param $name
     * @return mixed
     */
    public function findByShort($name)
    {
        return $this->where('short', '=', $name)->first();
    }
}
