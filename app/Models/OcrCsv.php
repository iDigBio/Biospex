<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spiritix\LadaCache\Database\LadaCacheTrait;

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

    /**
     * Mutator for subjects column.
     *
     * @param $value
     */
    public function setSubjectsAttribute($value)
    {
        $this->attributes['subjects'] = serialize($value);
    }

    /**
     * Accessor for subjects column.
     *
     * @param $value
     * @return mixed
     */
    public function getSubjectsAttribute($value)
    {
        return empty($value) ? [] : unserialize($value);
    }
}