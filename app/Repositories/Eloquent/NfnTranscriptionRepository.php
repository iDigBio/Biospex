<?php  

namespace App\Repositories\Eloquent;

use App\Models\NfnTranscription as Model;
use App\Repositories\Interfaces\NfnTranscription;

class NfnTranscriptionRepository extends EloquentRepository implements NfnTranscription
{

    /**
     * Specify Model class name
     *
     * @return \Illuminate\Database\Eloquent\Model|string
     */
    public function model()
    {
        return Model::class;
    }
}
