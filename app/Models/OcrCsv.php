<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OcrCsv extends Model
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