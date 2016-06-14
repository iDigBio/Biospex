<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class TeamCategory extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'team_categories';

    protected $fillable = [
        'name',
        'label'
    ];

    /**
     * Faq relationship.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function teams()
    {
        return $this->hasMany(Team::class);
    }
    
    /**
     * Set name attribute.
     *
     * @param  string  $value
     * @return string
     */
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = strtolower(str_replace(' ', '-', $value));
    }

    /**
     * Set label attribute.
     *
     * @param  string  $value
     * @return string
     */
    public function setLabelAttribute($value)
    {
        $this->attributes['label'] = ucwords($value);
    }
}