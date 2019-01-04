<?php

namespace App\Http\Controllers\Front;

use App\Facades\FlashHelper;
use App\Facades\GeneralHelper;
use App\Http\Controllers\Controller;
use App\Mail\ContactForm;
use App\Repositories\Interfaces\AmChart;
use App\Repositories\Interfaces\Event;
use App\Repositories\Interfaces\Expedition;
use App\Repositories\Interfaces\PanoptesTranscription;
use App\Repositories\Interfaces\Project;
use App\Http\Requests\ContactFormRequest;
use JavaScript;
use Mail;

class HomeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \App\Repositories\Interfaces\Expedition $expeditionContract
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Expedition $expeditionContract)
    {
        $expedition = $expeditionContract->getHomePageProjectExpedition();

        return view('front.home', compact('expedition'));
    }

    /**
     * Show welcome to new registered users.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function welcome()
    {
        return view('front.welcome');
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

        return view('front.layouts.partials.home-project-list', compact('recentProjects'));
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
        return view('front.contact');
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

        FlashHelper::success(trans('messages.contact_success'));

        return redirect()->route('home');
    }

    /**
     * Return vision page.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function vision()
    {
        return view('front.vision');
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

        return view('front.events.scoreboard-content', ['event' => $event]);
    }

    public function test()
    {
        $config = file_get_contents(public_path('chartConfig.json'));
        return view('front.chart', compact('config'));
    }
}
