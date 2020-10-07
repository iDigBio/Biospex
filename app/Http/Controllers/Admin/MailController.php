<?php
/**
 * Copyright (C) 2015  Biospex
 * biospex@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MailFormRequest;
use App\Mail\SiteMailer;
use App\Repositories\Interfaces\User;
use Illuminate\Support\Facades\Mail;

class MailController extends Controller
{
    /**
     * @var \App\Repositories\Interfaces\User
     */
    private $userContract;

    /**
     * UsersController constructor.
     *
     * @param \App\Repositories\Interfaces\User $userContract
     */
    public function __construct(User $userContract)
    {
        $this->userContract = $userContract;
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('admin.mail.index');
    }

    /**
     * Show the form for user edit.
     *
     * @param \App\Http\Requests\MailFormRequest $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function send(MailFormRequest $request)
    {
        $users = $this->userContract->getUsersForMailer($request->get('recipients'));
        $recipients = $users->reject(function($user){
            return $user->email === config('mail.from.address');
        })->pluck('email');

        Mail::to(config('mail.from.address'))
            ->bcc($recipients)
            ->send(new SiteMailer($request->get('subject'), $request->get('message')));

        \Flash::success(t('Your message has been sent.'));

        return redirect()->route('admin.mail.index');
    }
}
