<?php namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Contracts\UserContract;

class DashboardController extends Controller
{
    /**
     * Show the application dashboard.
     * 
     * @param Request $request
     * @param UserContract $userContract
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request, UserContract $userContract)
    {
        $user = $userContract->with('profile')->find($request->user()->id);

        return view('backend.index', compact('user'));
    }
}