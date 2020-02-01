<div class="col-md-4 mb-4">
    <div class="card px-4 box-shadow h-100">
        <div class="card-body text-center">
            @if(GeneralHelper::eventBefore($event))
                <h3 class="card-text">{{ __('pages.starting') }} {{ $event->present()->start_date_to_string }}</h3>
            @elseif(GeneralHelper::eventAfter($event))
                <h3 class="card-text">{{ __('pages.completed') }}</h3>
            @else
                <h2 class="card-text">{{ __('pages.time_remaining') }}</h2>
                <div class="clockdiv" data-value="{{ $event->present()->scoreboard_date }}">
                    <div>
                        <span class="days"></span>
                        <div class="smalltext">{{ __('pages.days') }}</div>
                    </div>
                    <div>
                        <span class="hours"></span>
                        <div class="smalltext">{{ __('pages.hours') }}</div>
                    </div>
                    <div>
                        <span class="minutes"></span>
                        <div class="smalltext">{{ __('pages.minutes') }}</div>
                    </div>
                    <div>
                        <span class="seconds"></span>
                        <div class="smalltext">{{ __('pages.seconds') }}</div>
                    </div>
                </div>
            @endif
            <h4 class="text-center pt-4">{{ $event->title }}</h4>
            <h5 class="text-center color-action">
                {{ $event->present()->start_date_to_string }} {{ __('pages.to') }}
                {{ $event->present()->end_date_to_string }} {{ str_replace('_', ' ', $event->timezone) }}<br>
                {{ __('for') }}<br>
                {{ $event->project->title }}
            </h5>
        </div>
        @if( ! GeneralHelper::eventBefore($event))
            <div class="text-center">
                <button class="btn btn-primary mb-4 text-uppercase" data-toggle="modal"
                        data-remote="false"
                        data-target="#scoreboard-modal"
                        data-channel="{{ config('config.poll_scoreboard_channel') .'.'. $event->project_id }}"
                        data-event="{{ $event->id }}"
                        data-href="{{ route('ajax.get.scoreboard', [$event->id]) }}">{{ __('pages.scoreboard') }}
                </button>
                @if(\Route::current()->getName() == 'front.events.read' || \Route::current()->getName() == 'admin.events.show')
                    <button class="btn btn-primary mb-4 text-uppercase" data-toggle="modal"
                            data-remote="false"
                            data-target="#step-chart-modal"
                            data-event="{{ $event->id }}"
                            data-href="{{ route('ajax.get.step', [$event->id]) }}">{{ __('pages.event_rate_chart') }}
                    </button>
                @endif
            </div>
        @endif
        <div class="card-footer">
            <div class="d-flex align-items-start justify-content-between mt-4 mb-3">
                {!! $event->project->present()->project_page_icon !!}
                {!! $event->present()->event_show_icon !!}
                @if(GeneralHelper::eventBefore($event) || GeneralHelper::eventActive($event))
                    {!! $event->project->lastPanoptesProject->present()->project_icon !!}
                @endif
                {!! $event->present()->twitter_icon !!}
                {!! $event->present()->facebook_icon !!}
                {!! $event->present()->contact_email_icon !!}
            </div>
        </div>
    </div>
</div>
