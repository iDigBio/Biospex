<?php
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
use Biospex\Repo\Project\ProjectInterface;
use Biospex\Form\Contact\ContactForm;

class HomeController extends BaseController
{
    /**
     * @var Biospex\Repo\Project\ProjectInterface
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
    public function __construct(
        ProjectInterface $project,
        ContactForm $contactForm
    ) {
        $this->beforeFilter('csrf', ['on' => 'post']);

        $this->project = $project;
        $this->contactForm = $contactForm;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        return View::make('home');
    }

    /**
     * Show public project page
     *
     * @param $slug
     * @return \Illuminate\View\View
     */
    public function project($slug)
    {
        $project = $this->project->bySlug($slug);

        return View::make('project', compact('project'));
    }

    /**
     * Show help page
     */
    public function help()
    {
        return View::make('help');
    }

    /**
     * Display contact form.
     *
     * @return \Illuminate\View\View
     */
    public function contact()
    {
        return View::make('contact');
    }

    /**
     * Send contact form.
     *
     * @return \Illuminate\View\View
     */
    public function sendContactForm()
    {
        $result = $this->contactForm->check(Input::all());

        if ($result) {
            \Event::fire('user.sendcontact', [
                'view'    => 'emails.contact',
                'subject' => trans('emails.contact_subject'),
                'data'    => [
                    'first_name'    => Input::get('first_name'),
                    'last_name'     => Input::get('last_name'),
                    'email'         => Input::get('email'),
                    'email_message' => Input::get('message'),
                ],
            ]);

            Session::flash('success', trans('pages.contact_success'));

            return Redirect::route('home');
        }

        Session::flash('error', trans('pages.contact_fail'));

        return Redirect::route('contact')->withInput()->withErrors($this->contactForm->errors());
    }
}
