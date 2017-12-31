<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spiritix\LadaCache\Database\LadaCacheTrait;

class OcrCsv extends Model
{
    use LadaCacheTrait;

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