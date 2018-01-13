<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;

class ExpeditionsController extends ApiController
{

    /**
     * ExpeditionController constructor.
     */
    public function __construct()
    {

    }

    /**
     * Display a listing of the resource.
     *
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Request
     */
    public function store(Request $request)
    {
        return $request;
    }

    /**
     * Show expedition.
     *
     * @param $expeditionId
     * @return mixed
     */
    public function show($expeditionId)
    {
        return $expeditionId;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param $expeditionId
     * @return mixed
     */
    public function edit($expeditionId)
    {
        return $expeditionId;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param $expeditionId
     * @return mixed
     */
    public function update(Request $request, $expeditionId)
    {
        return $expeditionId;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $expeditionId
     * @return mixed
     */
    public function destroy($expeditionId)
    {
        return $expeditionId;
    }
}
