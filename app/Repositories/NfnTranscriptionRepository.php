<?php  

namespace App\Repositories;

use App\Models\NfnTranscription as Model;
use App\Interfaces\NfnTranscription;

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
