<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spiritix\LadaCache\Database\LadaCacheTrait;

class FaqCategory extends Model
{
    use LadaCacheTrait;

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