<?php namespace App\Models;

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
     * Protect date columns.
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * Fillable columns.
     * @var array
     */
    protected $fillable = array(
        'qualified',
        'short',
        'namespace'
    );

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
