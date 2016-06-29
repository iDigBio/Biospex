<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class FaqCategory extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'faq_categories';

    protected $fillable = [
        'name',
        'label'
    ];

    /**
     * Faq relationship.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function faqs()
    {
        return $this->hasMany(Faq::class);
    }
}