<?php  

namespace App\Repositories\Eloquent;

use App\Models\NfnTranscription;
use App\Repositories\Contracts\NfnTranscriptionContract;
use Illuminate\Contracts\Container\Container;

class NfnTranscriptionRepository extends EloquentRepository implements NfnTranscriptionContract
{

    /**
     * NfnTranscriptionRepository constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->setContainer($container)
            ->setModel(NfnTranscription::class)
            ->setRepositoryId('biospex.repository.nfnTranscription');
    }
}
