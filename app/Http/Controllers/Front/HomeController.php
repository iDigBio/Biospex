<?php

namespace App\Http\Controllers\Front;

use App\Facades\FlashHelper;
use App\Http\Controllers\Controller;
use App\Mail\ContactForm;
use App\Repositories\Interfaces\AmChart;
use App\Repositories\Interfaces\Event;
use App\Repositories\Interfaces\Expedition;
use App\Http\Requests\ContactFormRequest;
use App\Repositories\Interfaces\PanoptesTranscription;
use Mail;

class HomeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \App\Repositories\Interfaces\Expedition $expeditionContract
     * @param \App\Repositories\Interfaces\PanoptesTranscription $panoptesTranscription
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Expedition $expeditionContract, PanoptesTranscription $panoptesTranscription)
    {
        $expedition = $expeditionContract->getHomePageProjectExpedition();
        $contributorCount = $panoptesTranscription->getContributorCount();
        $transcriptionCount = $panoptesTranscription->getTotalTranscriptions();

        return view('front.home', compact('expedition', 'contributorCount', 'transcriptionCount'));
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
}
