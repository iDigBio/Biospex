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
        $carouselProjects = $projectContract->getRandomProjectsForCarousel(5);
        $recentProjects = $projectContract->getRecentProjects(5);
        $transcriptionCount = number_format($panoptesTranscriptionContract->getTotalTranscriptions());
        $contributorCount = number_format($panoptesTranscriptionContract->getContributorCount());

        return view('frontend.home', compact('carouselProjects', 'recentProjects', 'transcriptionCount', 'contributorCount'));
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
     * Show public project page.
     *
     * @param $slug
     * @param Project $projectContract
     * @return \Illuminate\View\View
     */
    public function project($slug, Project $projectContract)
    {
        $project = $projectContract->getProjectPageBySlug($slug);

        return view('frontend.project', compact('project'));
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

    /**
     * Load AmChart for project home page.
     *
     * @param AmChart $amChartContract
     * @param $projectId
     * @return mixed
     */
    public function loadAmChart(AmChart $amChartContract, $projectId)
    {
        $record = $amChartContract->findBy('project_id', $projectId);

        if ($record === null) {
            return '';
        }

        return json_decode($record->data);
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
     * @return mixed
     */
    public function postContact(ContactFormRequest $request)
    {
        $contact = $request->only('first_name', 'last_name', 'email', 'message');

        Mail::to(config('mail.from.address'))->send(new ContactForm($contact));

        Flash::success(trans('messages.contact_success'));

        return redirect()->route('home');
    }

    /**
     * Return vision page.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function vision()
    {
        return view('frontend.vision');
    }

    /**
     * @param $eventId
     * @param \App\Repositories\Interfaces\Event $eventContract
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function scoreboard($eventId, Event $eventContract)
    {
        $event = $eventContract->getEventScoreboard($eventId, ['id']);

        if (! request()->ajax() || is_null($event)) {
            return response()->json(['html' => 'Error retrieving the Event']);
        }

        return view('frontend.events.scoreboard-content', ['event' => $event]);
    }
}
