<?php namespace Biospex\Http\Controllers\Front;

use Biospex\Http\Controllers\Controller;
use Biospex\Jobs\SendContactEmail;
use Biospex\Repositories\Contracts\Project;
use Biospex\Http\Requests\ContactFormRequest;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * Get("/", as="home")
     *
     * @return Response
     */
    public function index()
    {
        if (Auth::check())
        {
            return redirect()->route('projects.get.index');
        }

        return view('front.home');
    }

    /**
     * Show public project page.
     *
     * @param $slug
     * @param Project $repository
     * @return \Illuminate\View\View
     */
    public function project($slug, Project $repository)
    {
        $project = $repository->bySlug($slug);

        return view('front.project', compact('project'));
    }

    /**
     * Show help page
     *
     * @Get("help", as="help");
     */
    public function help()
    {
        return view('front.help');
    }

    /**
     * Display contact form.
     *
     * @Get("contact", as="contact")
     *
     * @return \Illuminate\View\View
     */
    public function getContact()
    {
        return view('front.contact');
    }

    /**
     * Send contact form.
     *
     * @param ContactFormRequest $request
     * @param Config $config
     * @return mixed
     */
    public function postContact(ContactFormRequest $request, Config $config)
    {
        $data = $request->only('first_name', 'last_name', 'email', 'message');

        $this->dispatch(new SendContactEmail($data));

        return redirect()->route('home')->with('success', trans('pages.contact_success'));
    }
}
