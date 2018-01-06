<?php

namespace App\Http\Controllers\Api\V1;

use App\Jobs\NfnClassificationWeDigBioJob;
use Illuminate\Http\Request;

class PanoptesPusherController extends ApiController
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Support\Facades\Response
     */
    public function index()
    {
        return $this->errorNotFound();
    }

    /**
     * Create classification based on pusher.
     *
     * @param Request $request
     * @return mixed|void
     * [2017-10-15 00:12:45] lumen.INFO: {"classification_id":"74498341","project_id":"1558","workflow_id":"2838","user_id":null,"subject_ids":["4002829"],"subject_urls":[{"image/jpeg":"https://panoptes-uploads.zooniverse.org/production/subject_location/0640d1cd-6c4f-43b4-9a10-726ebd43fedb.jpeg"}],"geo":{"country_name":"United States","country_code":"US","city_name":"Tallahassee","coordinates":[-84.2539,30.4203],"latitude":30.4203,"longitude":-84.2539}}
     */
    public function create(Request $request)
    {
        if ( ! $request->isJson()) {
            return;
        }

        $data = json_decode($request->getContent(), true);
        NfnClassificationWeDigBioJob::dispatch($data);
    }

    /**
     * Display the specified resource.
     *
     * @param $id
     * @return \Illuminate\Support\Facades\Response
     */
    public function show($id)
    {
        return $this->errorNotFound();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Support\Facades\Response
     */
    public function update(Request $request, $id)
    {
        return $this->errorNotFound();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $id
     * @return \Illuminate\Support\Facades\Response
     */
    public function delete($id)
    {
        return $this->errorNotFound();
    }
}
