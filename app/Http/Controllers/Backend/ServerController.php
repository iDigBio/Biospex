<?php

namespace App\Http\Controllers\Backend;

use App\Interfaces\User;
use App\Http\Controllers\Controller;
use Appstract\Opcache\OpcacheFacade as Opcache;

class ServerController extends Controller
{

    /**
     * Display listing of resource.
     *
     * @param User $userContract
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(User $userContract)
    {
        $user = $userContract->findWith(request()->user()->id, ['profile']);

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
     * @param User $userContract
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show(User $userContract)
    {
        $user = $userContract->findWith(request()->user()->id, ['profile']);

        ob_start () ;
        phpinfo () ;
        $page = ob_get_contents () ;
        ob_end_clean () ;
        preg_match('/<body[^>]*>(.*?)<\/body>/is', $page, $matches);
        $phpInfo = $matches[1];


        return view('backend.servers.show', compact('user', 'phpInfo'));
    }

    public function clear()
    {
        $user = request()->user();

        if ( ! $user->isAdmin('admins')) {
            return redirect()->guest('/login');
        }

        OPcache::clear();

        return redirect()->intended('/projects');
    }
}
