<?php 

namespace App\Models;

class OcrCsv extends BaseEloquentModel
{
    /**
     * @inheritDoc
     */
    protected $table = 'ocr_csv';

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'subjects'
    ];

    /**
     * OcrQueue relationship.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function ocrQueue()
    {
        return $this->hasMany(OcrQueue::class);
    }
}