<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
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
    public function category()
    {
        return $this->belongsTo(FaqCategory::class);
    }
}