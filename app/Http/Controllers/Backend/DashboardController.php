<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Interfaces\User;

class DashboardController extends Controller
{
    /**
     * Show the application dashboard.
     * 0
     * @param User $userContract
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(User $userContract)
    {
        $user = $userContract->findWith(request()->user()->id, ['profile']);

        return view('backend.index', compact('user'));
    }
}