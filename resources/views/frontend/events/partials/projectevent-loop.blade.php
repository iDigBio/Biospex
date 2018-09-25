<div class="panel panel-default">
    <div class="panel-heading">
        <h4>{{ $event->title }}</h4>
        @lang('pages.start_date')
        : {{ $event->present()->start_date_to_string }} {{ str_replace('_', ' ', $event->timezone) }}
        <br/>
        @lang('pages.end_date')
        : {{ $event->present()->end_date_to_string }} {{ str_replace('_', ' ', $event->timezone) }}
        <br/>
    </div>
    <div class="panel-body text-center">
        <button type="button" class="btn btn-info btn-lg" data-toggle="modal"
                data-remote="false"
                data-target="#scoreboardModal"
                data-channel="{{ config('config.poll_scoreboard_channel') .'.'. $event->project_id }}"
                data-event="{{ $event->id }}"
                data-href="{{ route('home.get.scoreboard', [$event->id]) }}"
        >Scoreboard</button>
    </div>
</div>