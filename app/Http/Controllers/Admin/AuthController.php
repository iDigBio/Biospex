<?php

namespace Biospex\Http\Controllers\Admin;

use Biospex\Http\Controllers\Controller;
use Biospex\Jobs\SendLostPasswordJob;
use Biospex\Jobs\ResetPasswordJob;
use Biospex\Http\Requests\UserLoginRequest;
use Biospex\Jobs\UserLogInJob;
use Biospex\Jobs\UserLogOutJob;

class AuthController extends Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
    }

}
