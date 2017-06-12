<?php

namespace App\Http\Controllers\Backend;

use App\Repositories\Contracts\UserContract;
use App\Http\Controllers\Controller;

class ServerController extends Controller
{

    /**
     * Display listing of resource.
     *
     * @param UserContract $userContract
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(UserContract $userContract)
    {
        $user = $userContract->with(['profile'])->find(request()->user()->id);

        return view('backend.servers.index', compact('user'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store()
    {
        
    }

    /**
     * Display the specified resource.
     *
     * @param UserContract $userContract
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show(UserContract $userContract)
    {
        $user = $userContract->with('profile')->find(request()->user()->id);

        ob_start () ;
        phpinfo () ;
        $page = ob_get_contents () ;
        ob_end_clean () ;
        preg_match('/<body[^>]*>(.*?)<\/body>/is', $page, $matches);
        $phpInfo = $matches[1];


        return view('backend.servers.show', compact('user', 'phpInfo'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        //
    }
}
