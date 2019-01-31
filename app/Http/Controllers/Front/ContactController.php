<?php

namespace App\Http\Controllers\Front;

use App\Facades\FlashHelper;
use App\Http\Controllers\Controller;
use App\Mail\ContactForm;
use App\Http\Requests\ContactFormRequest;
use Mail;

class ContactController extends Controller
{
    public function __construct()
    {
        //dd(request()->all());
    }
    /**
     * Display contact form.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('front.contact');
    }

    /**
     * Send contact form.
     *
     * @param ContactFormRequest $request
     * @return mixed
     */
    public function create(ContactFormRequest $request)
    {
        $contact = $request->only('name', 'email', 'message');

        Mail::to(config('mail.from.address'))->send(new ContactForm($contact));

        FlashHelper::success(__('Your message has been sent. Thank you.'));

        return redirect()->route('home');
    }
}
