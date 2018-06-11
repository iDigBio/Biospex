<?php

namespace App\Models;

use Askedio\SoftCascade\Traits\SoftCascadeTrait;
use Illuminate\Database\Eloquent\Model;
use Spiritix\LadaCache\Database\LadaCacheTrait;

class FaqCategory extends Model
{
    use LadaCacheTrait, SoftCascadeTrait;

    /**
     * @inheritDoc
     */
    protected $table = 'faq_categories';

    /**
     * @inheritDoc
     */
    protected $fillable = ['name'];

    /**
     * Soft delete cascades.
     *
     * @var array
     */
    protected $softCascade = [
        'faqs'
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