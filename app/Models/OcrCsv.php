<?php namespace Biospex\Models;

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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function ocrQueue()
    {
        return $this->hasMany(OcrQueue::class);
    }

    public function createOrFirst($attributes)
    {
        return $this->firstOrCreate($attributes);
    }
}