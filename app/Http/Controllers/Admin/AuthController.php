<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendLostPasswordJob;
use App\Jobs\ResetPasswordJob;
use App\Http\Requests\UserLoginRequest;
use App\Jobs\UserLogInJob;
use App\Jobs\UserLogOutJob;

class AuthController extends Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
    }

}
