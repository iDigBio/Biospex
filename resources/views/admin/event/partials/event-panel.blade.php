<div class="row">
    <div class="col-sm-10 mx-auto">
        <div class="jumbotron box-shadow pt-2 pb-5 my-5 p-sm-5">
            <h1 class="text-center content-header text-uppercase">{{ $event->title }}</h1>
            <p class="text-center">{{ $event->description }}</p>
            <div class="col-md-12 d-flex">
                <div class="col-md-6">
                    <p>{{ __('pages.project') }}
                        :
                        <a href="{{ route('front.projects.slug', ['slug' => $event->project->slug]) }}">{{ $event->project->title }}</a>
                    </p>
                    {{ __('pages.start_date') }}
                    : {{ $event->present()->start_date_to_string }} {{ str_replace('_', ' ', $event->timezone) }}
                    <br>
                    {{ __('pages.end_date') }}
                    : {{ $event->present()->end_date_to_string }} {{ str_replace('_', ' ', $event->timezone) }}
                </div>
                <div class="col-md-6">
                    <p>{{ __('pages.digitizations') }}: {{ $event->transcriptions_count }}</p>
                    <p>{{ __('pages.team_invite_link') }}:</p>
                    @foreach($event->teams as $team)
                        {!! $team->present()->team_join_url_icon !!}
                    @endforeach
                </div>
            </div>
            <div class="col-md-12 d-flex justify-content-between mt-4 mb-3">
                {!! $event->project->present()->project_page_icon_lrg !!}
                @if(GeneralHelper::eventActive($event))
                    {!! $event->project->lastPanoptesProject->present()->project_icon_lrg !!}
                @endif
                {!! $event->present()->event_download_users_icon_lrg !!}
                {!! $event->present()->event_download_digitizations_icon_lrg !!}
                {!! $event->present()->event_edit_icon_lrg !!}
                @can('isOwner', $event)
                    {!! $event->present()->event_delete_icon_lrg !!}
                @endcan
            </div>
        </div>
    </div>
</div>
