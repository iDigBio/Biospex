<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\UuidTrait;
use App\Models\Traits\BelongsToProjectTrait;

class OcrQueue extends Model
{
    use UuidTrait;
    use BelongsToProjectTrait;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'ocr_queues';

    /**
     * fillable  columns.
     * @var array
     */
    protected $fillable = [
        'project_id',
        'uuid',
        'data',
        'subject_count',
        'tries',
        'status',
        'error',
        'attachments'
    ];

    /**
     * Find by uuid.
     *
     * @param $uuid
     * @return mixed
     */
    public function findByUuid($uuid)
    {
        return $this->where('uuid', pack('H*', str_replace('-', '', $uuid)))->get();
    }
}
