<?php

namespace App\Http\ViewComposers;

use Auth;
use Illuminate\Contracts\View\View;

class AuthUserComposer
{

    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $authUser = Auth::check() ? Auth::user()->load('profile') : null;

        $view->with('authUser', $authUser);
    }
}