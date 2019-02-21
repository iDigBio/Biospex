<div class="row">
    <div class="col-sm-10 mx-auto">
        <div class="jumbotron box-shadow pt-2 pb-5 my-5 p-sm-5">
            <h1 class="text-center content-header text-uppercase">{{ $event->title }}</h1>
            <p>{{ $event->description }}</p>
            <div class="col-md-12 d-flex">
                <div class="col-md-6">
                    <p>@lang('pages.project'): {!! link_to_route('front.projects.slug', $event->project->title, [$event->project->slug]) !!}</p>
                    @lang('pages.start_date')
                        : {{ $event->present()->start_date_to_string }} {{ str_replace('_', ' ', $event->timezone) }}
                    <br>
                    @lang('pages.end_date')
                        : {{ $event->present()->end_date_to_string }} {{ str_replace('_', ' ', $event->timezone) }}
                </div>
                <div class="col-md-6">
                    <p>@lang('pages.transcriptions'): {{ $event->transcriptions_count }}</p>
                    <p>Team Invite Links</p>
                    @foreach($event->teams as $team)
                        <button type="button" class="btn btn-default btn-copy js-tooltip js-copy"
                                data-toggle="tooltip"
                                data-placement="bottom"
                                data-copy="{{ route('front.events.signup', [$team->uuid]) }}" title="Copy to clipboard">
                            <span class="fa fa-clipboard fa-lg"></span> {{ $team->title }}
                        </button>
                    @endforeach
                </div>
            </div>
            <div class="col-md-12 d-flex justify-content-between mt-4 mb-3">
                {!! $event->project->present()->project_page_icon_lrg !!}
                {!! $event->present()->event_download_users_icon_lrg !!}
                {!! $event->present()->event_download_transcripts_icon_lrg !!}
                {!! $event->present()->event_edit_icon_lrg !!}
                {!! $event->present()->event_delete_icon_lrg !!}
            </div>
        </div>
    </div>
</div>
