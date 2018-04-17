<div class="panel panel-default">
    <div class="panel-heading">
        <h4>{{ $event->title }}</h4>
        @lang('pages.start_date')
        : {{ $event->start_date->setTimezone($event->timezone)->toDayDateTimeString() }} {{ str_replace('_', ' ', $event->timezone) }}
        <br/>
        @lang('pages.end_date')
        : {{ $event->end_date->setTimezone($event->timezone)->toDayDateTimeString() }} {{ str_replace('_', ' ', $event->timezone) }}
        <br/>
    </div>
    <div class="panel-body" style="font-size: small">
        <div class="row">
            <div class="col-md-4"><b>@lang('pages.groups')</b></div>
            <div class="col-md-6">
                <b>@lang('pages.event_board_percent')</b>
            </div>
            <div class="col-md-2">
                <b>@lang('pages.event_board_count')</b>
            </div>
        </div>
        @foreach($event->groups->sortByDesc('transcriptionCount') as $group)
            @include('frontend.events.partials.board-group-loop')
        @endforeach
        <div class="row top5">
            <div class="col-md-5">
                @lang('pages.event_board_total')
            </div>
            <div class="col-md-5"></div>
            <div class="col-md-2">{{ $event->transcriptionCount }}</div>
        </div>
    </div>
</div>