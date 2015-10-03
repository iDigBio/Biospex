<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\User;
use App\Services\Common\AuthService;
use App\Http\Requests\RegisterFormRequest;

use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;

class AuthController extends Controller
{
    use AuthenticatesAndRegistersUsers, ThrottlesLogins;

    protected $loginPath = '/login';

    protected $redirectPath = '/projects';

    /**
     * @var AuthService
     */
    private $service;

    /**
     * Create a new authentication controller instance.
     *
     * @param AuthService $service
     */
    public function __construct(AuthService $service)
    {
        $this->middleware('guest', ['except' => 'getLogout']);
        $this->service = $service;
    }

    /**
     * @return \Illuminate\View\View
     */
    public function getLogin()
    {
        return view('front.auth.login');
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function getRegister()
    {
        $vars = $this->service->getRegister();

        if ( ! $vars)
            return redirect()->route('home')->with('error', trans('users.inactive_reg'));

        return view('front.auth.register', $vars);
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postRegister(RegisterFormRequest $request)
    {
        if ($this->service->postRegister($request))
            return redirect()->route('auth.get.login');

        return redirect()->back()->withInput();
    }

    public function create()
    {

    }

    public function read()
    {

    }

    public function update()
    {

    }

    public function delete()
    {

    }
}
