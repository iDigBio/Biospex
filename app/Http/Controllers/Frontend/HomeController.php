<?php 

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Jobs\SendContactEmail;
use App\Repositories\Contracts\AmChart;
use App\Repositories\Contracts\Faq;
use App\Repositories\Contracts\Project;
use App\Http\Requests\ContactFormRequest;
use Illuminate\Contracts\Config\Repository as Config;

class HomeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Project $project)
    {
        $projects = $project->skipCache()->getRandomProjectsForCarousel(5);

        return view('frontend.home', compact('projects'));
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
        $project = $repository->with(['group.users.profile', 'expeditions.stat', 'expeditions.actors', 'amChart'])->where(['slug' => $slug])->first();
        $expeditions = null;
        if ( ! $project->expeditions->isEmpty())
        {
            foreach ($project->expeditions as $expedition)
            {
                if (null === $expedition->deleted_at)
                {
                    $expeditions[] = $expedition;
                }
            }
        }

        return view('frontend.project', compact('project', 'expeditions'));
    }

    /**
     * Load AmChart for project home page.
     *
     * @param AmChart $chart
     * @param $projectId
     * @return mixed
     */
    public function loadAmChart(AmChart $chart, $projectId)
    {
        $record = $chart->skipCache()->where(['project_id' => (int) $projectId])->first();

        return json_decode($record->data);
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
