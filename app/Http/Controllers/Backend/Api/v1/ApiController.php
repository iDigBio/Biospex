<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;

class ApiController extends Controller
{

    public function __construct()
    {

    }

    public function index()
    {
        return "Index";
    }
}
