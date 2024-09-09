<?php

namespace App\Http\Controllers\Front;

use App\Facades\DateHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\EventJoinRequest;
use App\Models\EventTeam;
use App\Services\Models\EventUserModelService;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;

class EventTeamUserController extends Controller
{
    /**
     * Group join page for events.
     */
    public function create(EventTeam $team): \Illuminate\Contracts\View\View
    {
        if (! isset($team->uuid)) {
            Session::flash('error', t('The event team could not be found. Please check you are using the correct link or contact event coordinator.'));
        }

        $team->load(['event']);

        $active = DateHelper::eventBefore($team->event) || DateHelper::eventActive($team->event);

        return View::make('front.event.signup', compact('team', 'active'));
    }

    /**
     * Store user for event group.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(
        EventUserModelService $eventUserModelService,
        EventTeam $eventTeam,
        EventJoinRequest $request,
        $uuid
    ) {

        $user = $eventUserModelService->updateOrCreate(['nfn_user' => $request->get('nfn_user')], ['nfn_user' => $request->get('nfn_user')]);

        if ($user !== null) {
            $team = $eventTeam->with(['event'])->find($request->get('team_id'));
            $team->users()->syncWithoutDetaching([$user->id]);

            return Redirect::route('front.events.show', [$team->event->id])->with('success', t('Thank you for your registration.'));
        }

        return Redirect::route('front.events_team_user.create', [$uuid])
            ->with('danger', t('The event team could not be found. Please check you are using the correct link or contact event coordinator.'));
    }
}
