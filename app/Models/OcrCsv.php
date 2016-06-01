<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OcrCsv extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'ocr_csv';

    /**
     * @var array
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