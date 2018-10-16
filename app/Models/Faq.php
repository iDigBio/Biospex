<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spiritix\LadaCache\Database\LadaCacheTrait;

class Faq extends Model
{
    use LadaCacheTrait;

    /**
     * @ineritDoc
     */
    protected $table = 'faqs';

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'faq_category_id',
        'question',
        'answer'
    ];

    /**
     * FaqCategory relationship.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function faqCategory()
    {
        return $this->belongsTo(FaqCategory::class);
    }
}