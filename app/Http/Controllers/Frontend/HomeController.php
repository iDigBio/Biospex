<?php 

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Jobs\SendContactEmail;
use App\Repositories\Contracts\Faq;
use App\Repositories\Contracts\Project;
use App\Http\Requests\ContactFormRequest;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        if (Auth::check())
        {
            return redirect()->route('projects.get.index');
        }

        return view('frontend.home');
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
        $project = $repository->with(['group', 'expeditions.stat', 'expeditions.actors'])->where(['slug' => $slug])->first();

        return view('frontend.project', compact('project'));
    }

    /**
     * Show faq page.
     *
     * @param Faq $faq
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function faq(Faq $faq)
    {
        $faqs = $faq->orderBy(['id' => 'asc'])->get();
                
        return view('frontend.faq', compact('faqs'));
    }

    /**
     * Display contact form.
     *
     * @return \Illuminate\View\View
     */
    public function getContact()
    {
        return view('frontend.contact');
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

    public function team()
    {
        return view('frontend.team');
    }

    public function vision()
    {
        return view('frontend.vision');
    }
}
