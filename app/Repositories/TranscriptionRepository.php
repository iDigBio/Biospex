<?php  

namespace App\Repositories;

use App\Repositories\Contracts\Transcription;
use App\Repositories\Contracts\CacheableInterface;
use App\Repositories\Traits\CacheableRepository;

class TranscriptionRepository extends Repository implements Transcription, CacheableInterface
{

    use CacheableRepository;

    /**
     * @return mixed
     */
    public function model()
    {
        return \App\Models\Transcription::class;
    }

}
