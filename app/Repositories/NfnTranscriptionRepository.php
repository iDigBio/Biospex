<?php  

namespace App\Repositories;

use App\Repositories\Contracts\NfnTranscription;
use App\Repositories\Contracts\CacheableInterface;
use App\Repositories\Traits\CacheableRepository;

class NfnTranscriptionRepository extends Repository implements NfnTranscription, CacheableInterface
{

    use CacheableRepository;

    /**
     * @return mixed
     */
    public function model()
    {
        return \App\Models\NfnTranscription::class;
    }

}
