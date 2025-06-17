<?php

/*
 * Copyright (C) 2014 - 2025, Biospex
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
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactFormRequest;
use App\Mail\ContactForm;
use Mail;
use Redirect;

/**
 * Class ContactController
 */
class ContactController extends Controller
{
    /**
     * Display contact form.
     */
    public function index(): \Illuminate\View\View
    {
        return \View::make('front.contact');
    }

    /**
     * Send contact form.
     */
    public function create(ContactFormRequest $request): \Illuminate\Http\RedirectResponse
    {
        $contact = $request->only('name', 'email', 'message');

        Mail::to(config('mail.from.address'))->send(new ContactForm($contact));

        return Redirect::route('home')->with('success', t('Your message has been sent. Thank you.'));
    }
}
