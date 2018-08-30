<div class="jumbotron">
    <div class="row">
        <div class="col-md-6">
            <h3>{{ $event->title }}</h3>
            <p>{{ $event->description }}</p>
            <p>@lang('pages.project'): {{ $event->project->title }}</p>
            @lang('pages.start_date')
            : {{ $event->start_date->setTimezone($event->timezone)->toDayDateTimeString() }} {{ str_replace('_', ' ', $event->timezone) }}
            <br/>
            @lang('pages.end_date')
            : {{ $event->end_date->setTimezone($event->timezone)->toDayDateTimeString() }} {{ str_replace('_', ' ', $event->timezone) }}
            <br/>
        </div>
        <div class="col-md-6">
            <div class="row">
                <h3>@lang('pages.transcriptions'): {{ $event->transcriptions_count }}</h3>
                <h3>Team Invite Links</h3>
                @foreach($event->teams as $team)
                    <button type="button" class="btn btn-default btn-copy js-tooltip js-copy"
                            data-toggle="tooltip"
                            data-placement="bottom"
                            data-copy="{{ route('web.events.join', [$team->uuid]) }}" title="Copy to clipboard">
                        <span class="fa fa-clipboard fa-lg"></span> {{ $team->title }}
                    </button>
                @endforeach
            </div>
            <div class="row top20">@lang('pages.project_url')
                : {!! link_to_route('home.get.project', $event->project->title, [$event->project->slug]) !!}
            </div>
        </div>
    </div>
</div>
<div class="panel panel-default">
    <div class="panel-body">
        <div class="row">
            <div class="col-md-12">
                <button title="@lang('pages.downloadTitleUsers')" class="btn btn-success btn-sm"
                        type="button"
                        onClick="location.href='{{ route('webauth.events.exportUsers', [$event->id]) }}'">
                    <span class="fa fa-download fa-lrg"></span> @lang('pages.users')
                </button>
                <button title="@lang('pages.downloadTitleTranscriptions')" class="btn btn-success btn-sm"
                        type="button"
                        onClick="location.href='{{ route('webauth.events.exportTranscriptions', [$event->id]) }}'">
                    <span class="fa fa-download fa-lrg"></span> @lang('pages.transcriptions')
                </button>
                <button title="@lang('pages.editTitle')" class="btn btn-warning btn-sm" type="button"
                        onClick="location.href='{{ route('webauth.events.edit', [$event->id]) }}'"><span
                            class="fa fa-cog fa-lrg"></span> @lang('pages.edit')</button>
                @can('delete', $event)
                    <button title="@lang('pages.deleteTitle')" class="btn btn-sm btn-danger"
                            data-href="{{ route('webauth.events.delete', [$event->id]) }}"
                            data-method="delete"
                            data-toggle="confirmation"
                            data-btn-ok-label="Continue" data-btn-ok-icon="fa fa-share fa-lrg"
                            data-btn-ok-class="btn-success"
                            data-btn-cancel-label="Stop" data-btn-cancel-icon="fa fa-ban fa-lrg"
                            data-btn-cancel-class="btn-danger"
                            data-title="Continue action?" data-content="This will delete the item">
                        <span class="fa fa-remove fa-lrg"></span> @lang('pages.delete')
                    </button>
                @endcan
            </div>
        </div>
    </div>
</div>
