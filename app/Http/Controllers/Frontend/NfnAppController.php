<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class NfnAppController extends Controller
{

    public function __construct()
    {
        
    }

    public function index()
    {
        Log::alert(dd(Request::all()));
    }
}
