<?php
/*
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
use App\Services\User\UserService;
use Mail;
use Redirect;
use View;

/**
 * Class MailController
 */
class MailController extends Controller
{
    /**
     * UserController constructor.
     */
    public function __construct(protected UserService $userService) {}

    /**
     * Show mail form
     */
    public function index(): \Illuminate\View\View
    {
        return View::make('admin.mail.index');
    }

    /**
     * Show the form for user edit.
     */
    public function send(MailFormRequest $request): \Illuminate\Http\RedirectResponse
    {
        $recipients = $this->userService->getUsersForMailer($request->get('recipients'));

        Mail::to(config('mail.from.address'))
            ->bcc($recipients)
            ->send(new SiteMailer($request->get('subject'), $request->get('message')));

        return Redirect::route('admin.mail.index')->with('success', t('Your message has been sent.'));
    }
}
