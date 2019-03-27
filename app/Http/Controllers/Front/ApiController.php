<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;

class ApiController extends Controller
{

    /**
     * Index of api.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('front.api-index');
    }

    /**
     * Api dashboard.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function dashboard()
    {
        return view('apiauth.dashboard');
    }
}
