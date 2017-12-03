<?php

namespace App\Http\Controllers\ApiAuth;

use App\Http\Controllers\Controller;

class ApiAuthController extends Controller
{

    public function index()
    {
        return view('frontend.api.index');
    }
}
