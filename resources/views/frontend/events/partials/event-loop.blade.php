<div class="col-md-3 col-sm-6 col-xs-12">
    <div class="panel panel-default event-card">
        <div class="panel-thumbnail">
            <img src="{{ $event->project->present()->logo_thumb_url }}" alt="{{ $event->project->title }}"
                 class="image img-responsive">
        </div>
        <a href="{{ route('webauth.events.show', [$event->id]) }}">
            <div class="panel-body">
                <p class="event-text">{{ $event->title }}</p>
                <p class="event-text">{{ $event->transcriptions_count == 0 ? 0 : $event->transcriptions_count }} {{ trans('pages.transcriptions') }}</p>
                <p class="progress-description">
                    {{ GeneralHelper::eventHoursLeft($event->start_date, $event->end_date, $event->timezone) }}
                </p>
            </div>
        </a>
        <div class="panel-footer text-center">
            <button type="button" class="btn btn-info btn-lg" data-toggle="modal"
                    data-remote="false"
                    data-target="#scoreboardModal"
                    data-channel="{{ config('config.poll_scoreboard_channel') .'.'. $event->project_id }}"
                    data-event="{{ $event->id }}"
                    data-href="{{ route('home.get.scoreboard', [$event->id]) }}"
            >Open Modal</button>
        </div>
    </div>
</div>
