<?php

namespace App\Models;

class FaqCategory extends BaseEloquentModel
{

    /**
     * @inheritDoc
     */
    protected $table = 'faq_categories';

    /**
     * @inheritDoc
     */
    protected $fillable = ['name'];

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