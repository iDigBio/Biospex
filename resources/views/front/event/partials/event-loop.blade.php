<div class="col-md-4 mb-4">
    <div class="card px-4 box-shadow h-100">
        <div class="card-body text-center">
            @if(event_before($event))
                <h3 class="card-text">{{ t('Starting') }} {{ $event->present()->start_date_to_string }}</h3>
            @elseif(event_after($event))
                <h3 class="card-text">{{ t('Completed') }}</h3>
            @else
                <h2 class="card-text">{{ t('Time Remaining') }}</h2>
                <div class="clockdiv" data-value="{{ $event->present()->scoreboard_date }}">
                    <div>
                        <span class="days"></span>
                        <div class="smalltext">{{ t('Days') }}</div>
                    </div>
                    <div>
                        <span class="hours"></span>
                        <div class="smalltext">{{ t('Hours') }}</div>
                    </div>
                    <div>
                        <span class="minutes"></span>
                        <div class="smalltext">{{ t('Minutes') }}</div>
                    </div>
                    <div>
                        <span class="seconds"></span>
                        <div class="smalltext">{{ t('Seconds') }}</div>
                    </div>
                </div>
            @endif
            <h4 class="text-center pt-4">{{ $event->title }}</h4>
            <h5 class="text-center color-action">
                {{ $event->present()->start_date_to_string }}<br>
                {{ t('to') }}<br>
                {{ $event->present()->end_date_to_string }}<br>
                {{ str_replace('_', ' ', $event->timezone) }}<br>
                {{ t('for') }} {{ isset($project) ? $project->title : $event->project->title }}
            </h5>
        </div>
        @if( ! event_before($event))
            <div class="text-center">
                <button class="btn btn-primary mb-4 text-uppercase" data-toggle="modal"
                        data-remote="false"
                        data-target="#scoreboard-modal"
                        data-channel="{{ config('config.poll_board_channel') .'.'. $event->project_id }}"
                        data-event="{{ $event->id }}"
                        data-href="{{ route('event.get.scoreboard', [$event->id]) }}">{{ t('Scoreboard') }}
                </button>

                @if($event->teams->isNotEmpty())
                    <button class="btn btn-primary mb-4 text-uppercase" data-toggle="modal"
                            data-remote="false"
                            data-target="#step-chart-modal"
                            data-event="{{ $event->id }}"
                            data-teams="{{ $event->teams->pluck('title')->implode(',') }}"
                            data-timezone="{{ event_rate_chart_timezone($event->timezone) }}"
                            data-href="{{ route('event.rate.index', [$event->id]) }}">{{ t('Rate Chart') }}
                    </button>
                @endif
            </div>
        @endif
        <div class="card-footer">
            <div class="d-flex align-items-start justify-content-between mt-4 mb-3">
                {!! isset($project) ? $project->present()->project_page_icon : $event->project->present()->project_page_icon !!}
                {!! $event->present()->event_show_icon !!}
                @if(event_before($event) || event_active($event))
                    {!! isset($project) ?
                            $project->lastPanoptesProject->present()->project_icon :
                            $event->project->lastPanoptesProject->present()->project_icon !!}
                @endif
                {!! $event->present()->twitter_icon !!}
                {!! $event->present()->facebook_icon !!}
                {!! $event->present()->contact_email_icon !!}
            </div>
        </div>
    </div>
</div>
