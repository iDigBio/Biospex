<?php namespace Biospex\Http\Controllers;
/**
 * HomeController.php
 *
 * @package    Biospex Package
 * @version    1.0
 * @author     Robert Bruhn <bruhnrp@gmail.com>
 * @license    GNU General Public License, version 3
 * @copyright  (c) 2014, Biospex
 * @link       http://biospex.org
 *
 * This file is part of Biospex.
 * Biospex is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Biospex is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Biospex.  If not, see <http://www.gnu.org/licenses/>.
 */

use Biospex\Repositories\Contracts\ProjectInterface;
use Biospex\Http\Requests\ContactFormRequest;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Redirect;

/**
 * Class HomeController
 *
 * @Resource("home", only={"index", "project/{slug}"})
 * @Controller(prefix="locale")
 * @Middleware("localizationRedirect")
 * @Middleware("localeSessionRedirect")
 * @package Biospex\Http\Controllers
 */
class HomeController extends Controller {

    /**
     * @var ProjectInterface
     */
    protected $project;

    /**
     * @var ContactForm
     */
    protected $contactForm;

    /**
     * Constructor
     *
     * @param ProjectInterface $project
     * @param ContactForm $contactForm
     */
    public function __construct(ProjectInterface $project)
    {
        //$this->middleware('csrf', ['on' => 'post']);
        $this->project = $project;
    }

    /**
     * Display a listing of the resource.
     *
     * Get("/", as="home")
     *
     * @return Response
     */
    public function index()
    {
        return view('home');
    }

    /**
     * Show public project page
     *
     *
     * @param $slug
     * @return \Illuminate\View\View
     */
    public function project($slug)
    {
        $project = $this->project->bySlug($slug);

        return view('project', compact('project'));
    }

    /**
     * Show help page
     *
     * @Get("help", as="help");
     */
    public function help()
    {
        return view('help');
    }

    /**
     * Display contact form.
     *
     * @Get("contact", as="contact")
     *
     * @return \Illuminate\View\View
     */
    public function contact()
    {
        return view('contact');
    }

    /**
     * Send contact form.
     *
     * @Post("contact", as="contact.send")
     *
     * @param ContactFormRequest $request
     * @return mixed
     */
    public function sendContactForm(ContactFormRequest $request)
    {
        Event::fire('user.sendcontact', [
            'view'    => 'emails.contact',
            'subject' => trans('emails.contact_subject'),
            'data'    => [
                'first_name' => $request->get('first_name'),
                'last_name'  => $request->get('last_name'),
                'email'      => $request->get('email'),
                'email_message'    => $request->get('message'),
            ],
        ]);

        //Session::flash('success', trans('pages.contact_success'));

        return Redirect::route('home')->with('message', trans('pages.contact_success'));

    }
}