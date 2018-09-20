<?php

namespace App\Http\Controllers\Frontend;

use App\Facades\Flash;
use App\Http\Controllers\Controller;
use App\Mail\ContactForm;
use App\Repositories\Interfaces\AmChart;
use App\Repositories\Interfaces\Event;
use App\Repositories\Interfaces\PanoptesTranscription;
use App\Repositories\Interfaces\Project;
use App\Http\Requests\ContactFormRequest;
use Mail;

class HomeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Project $projectContract
     * @param PanoptesTranscription $panoptesTranscriptionContract
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Project $projectContract, PanoptesTranscription $panoptesTranscriptionContract)
    {
        return view('front.home');
        /*
        $carouselProjects = $projectContract->getRandomProjectsForCarousel(5);
        $recentProjects = $projectContract->getRecentProjects(5);
        $transcriptionCount = number_format($panoptesTranscriptionContract->getTotalTranscriptions());
        $contributorCount = number_format($panoptesTranscriptionContract->getContributorCount());

        return view('frontend.home', compact('carouselProjects', 'recentProjects', 'transcriptionCount', 'contributorCount'));
        */
    }

    /**
     * Show welcome to new registered users.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function welcome()
    {
        return view('frontend.welcome');
    }


    /**
     * Return project list for home page.
     *
     * @param Project $projectContract
     * @param $count
     * @return mixed
     */
    public function projects(Project $projectContract, $count = 5)
    {
        $recentProjects = $projectContract->getRecentProjects($count + 5);

        return view('frontend.layouts.partials.home-project-list', compact('recentProjects'));
    }
}
