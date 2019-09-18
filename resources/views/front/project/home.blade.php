@extends('front.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ $project->title }}
@stop

@section('header')
    <header style="background-image: url({{ $project->present()->banner_file_url }});">
        <nav class="header navbar navbar-expand-md box-shadow">
            <a href="/"><img src="/images/page/biospex_logo.svg" alt="BIOSPEX"
                             class="my-0 mr-md-auto top-logo font-weight-normal"/></a>
            @include('common.nav')
        </nav>
    </header>
@endsection

{{-- Content --}}
@section('content')
    <div class="row">
        <div class="col-sm-10 mx-auto">
            <div class="jumbotron box-shadow pt-2 pb-5 my-5 p-sm-5">
                <h1 class="text-center project-wide text-uppercase">
                    <small style="font-size:16px;">{{ __('pages.project_page_title') }}</small>
                    <br>{{ $project->title }}</h1>
                <div class="col-12">
                    <div class="d-flex align-items-start justify-content-between mt-4 mb-3">
                        {!! $project->present()->project_expeditions_icon_lrg !!}
                        {!! $project->present()->project_events_icon_lrg !!}
                        {!! $project->present()->twitter_icon_lrg !!}
                        {!! $project->present()->facebook_icon_lrg !!}
                        {!! $project->present()->blog_icon_lrg !!}
                        {!! $project->present()->contact_email_icon_lrg !!}
                    </div>
                </div>

                <hr class="pt-0 pb-4">

                <div class="col-12 col-md-10 offset-md-1">
                    <div class="col-5 float-right">
                        <img src="{{ $project->logo->url() }}" class="img-fluid"
                             alt="{{ $project->title }} logo">
                    </div>
                    @if($project->contact !== null)
                        <h3>{{ __('pages.contact') }}</h3>
                        <p>
                            <a href="mailto:{{ $project->contact_email }}" class="text">{{ $project->contact }}</a>
                        </p>
                    @endif

                    @if($project->organization !== null)
                        <h3>{{ __('pages.organization') }}</h3>
                        @if($project->organization_webiste !== null)
                            <p><a href="{{ $project->organization_webiste }}"
                                  target="_blank">{{ $project->organization }}</a></p>
                        @else
                            <p>{{ $project->organization }}</p>
                        @endif
                    @endif

                    @if($project->project_partners !== null)
                        <h3>{{ __('pages.partners') }}</h3>
                        <p>{{ $project->project_partners }}</p>
                    @endif

                    @if($project->funding_source !== null)
                        <h3>{{ __('pages.funding_source') }}</h3>
                        <p>{{ $project->funding_source  }}</p>
                    @endif

                    @if($project->description_long !== null)
                        <h3>{{ __('pages.description') }}</h3>
                        @if($project->description_short !== null)
                            <p><strong>{{ $project->description_short  }}</strong></p>
                        @endif
                        <p>{{ $project->description_long }}</p>
                    @endif

                    @if($project->incentives !== null)
                        <h3>{{ __('pages.incentives') }}</h3>
                        <p>{{ $project->incentives }}</p>
                    @endif

                    @if($project->geographic_scope !== null)
                        <h3>{{ __('pages.geographic_scope') }}</h3>
                        <p>{{ $project->geographic_scope }}</p>
                    @endif

                    @if($project->taxonomic_scope !== null)
                        <h3>{{ __('pages.taxonomic_scope') }}</h3>
                        <p>{{ $project->taxonomic_scope }}</p>
                    @endif

                    @if($project->temporal_scope !== null)
                        <h3>{{ __('pages.temporal_scope') }}</h3>
                        <p>{{ $project->temporal_scope }}</p>
                    @endif

                    @if($project->language_skills !== null)
                        <h3>{{ __('pages.language_skills') }}</h3>
                        <p>{{ $project->language_skills }}</p>
                    @endif

                    @if($project->activities !== null)
                        <h3>{{ __('pages.activities') }}</h3>
                        <p>{{ $project->activities }}</p>
                    @endif

                    @if($project->resources->isNotEmpty())
                        <h3>{{ __('pages.resources') }}</h3>
                        @foreach($project->resources as $resource)
                            <p>{!! $resource->present()->resource !!}</p>
                        @endforeach
                    @endif

                </div>
            </div>
        </div>
    </div>

    <div class="row my-5">
        <div class="col-sm-8 offset-md-2">
            <h1 class="text-center content-header text-uppercase" id="expeditions">{{ __('pages.expeditions') }}</h1>
            <div class="text-center mt-4">
                <button class="toggle-view-btn btn btn-primary text-uppercase"
                        data-toggle="collapse"
                        data-target="#active-expeditions-main,#completed-expeditions-main"
                        data-value="{{ __('pages.view') }} {{ __('pages.active') }} {{ __('pages.expeditions') }}"
                >{{ __('pages.view') }} {{ __('pages.completed') }} {{ __('pages.expeditions') }}</button>
            </div>
            <div class="d-flex align-items-start justify-content-between mt-4 mb-3">
                <span class="text">{{ $project->expeditions_count }} Expeditions</span>
                <span class="text">{{ $transcriptionsCount }} Transcriptions</span>
                <span class="text">{{ $transcribersCount }} Transcribers</span>
            </div>
            <hr class="header mx-auto">
        </div>
        <div id="active-expeditions-main" class="col-sm-12 show">
            @include('common.expedition-sort', ['type' => 'active', 'route' => route('front.expeditions.sort'), 'id' => $project->id])
            <div id="active-expeditions" class="row col-sm-12 mx-auto justify-content-center">
                @include('front.expedition.partials.expedition', ['expeditions' => $expeditions])
            </div>
        </div>
        <div id="completed-expeditions-main" class="col-sm-12 collapse">
            @include('common.expedition-sort', ['type' => 'completed', 'route' => route('front.expeditions.sort'), 'id' => $project->id])
            <div id="completed-expeditions" class="row col-sm-12 mx-auto justify-content-center">
                @include('front.expedition.partials.expedition', ['expeditions' => $expeditionsCompleted])
            </div>
        </div>
    </div>

    <div class="row my-5">
        <div class="col-sm-8 offset-md-2">
            <h1 class="text-center content-header" id="events">{{ __('pages.events') }}</h1>
            <div class="text-center mt-4">
                <button class="toggle-view-btn btn btn-primary text-uppercase"
                        data-toggle="collapse"
                        data-target="#active-events-main,#completed-events-main"
                        data-value="{{ __('pages.view') }} {{ __('pages.active') }} {{ __('pages.events') }}"
                >{{ __('pages.view') }} {{ __('pages.completed') }} {{ __('pages.events') }}</button>
            </div>
            <hr class="header mx-auto">
        </div>
        <div id="active-events-main" class="col-sm-12 show">
            @include('common.event-sort', ['type' => 'active', 'route' => route('front.events.sort'), 'id' => $project->id])
            <div id="active-events" class="row col-sm-12 mx-auto justify-content-center">
                @include('front.event.partials.event', ['events' => $events])
            </div>
        </div>
        <div id="completed-events-main" class="col-sm-12 collapse">
            @include('common.event-sort', ['type' => 'completed', 'route' => route('front.events.sort'), 'id' => $project->id])
            <div id="completed-events" class="row col-sm-12 mx-auto justify-content-center">
                @include('front.event.partials.event', ['events' => $eventsCompleted])
            </div>
        </div>
        @include('common.scoreboard')
    </div>

    @if ($project->amChart !== null && $project->amChart->series !== null && $project->amChart->data !== null)
        <div class="row my-5">
            <div class="col-sm-10 mx-auto mt-8">
                <h1 class="text-center content-header text-uppercase"
                    id="expeditions">{{ __('pages.transcriptions') }}</h1>
                <div class="d-flex justify-content-center col-sm-12 mb-4">
                    <a href="{{ route('front.projects.image', $project->id) }}" target="_blank"
                       class="btn btn-primary mr-4 text-uppercase">{{ __('View Active Chart') }}</a>
                </div>
                <hr class="header mx-auto">
                <div class="jumbotron box-shadow pt-2 pb-5 text-center">
                    {!! $project->present()->project_chart !!}
                </div>
            </div>
        </div>

        <div class="row my-5">
            <div class="col-sm-10 mx-auto mt-8">
                <h1 class="text-center content-header text-uppercase"
                    id="expeditions">{{ __('pages.project_map_title') }}</h1>
                <div class="jumbotron box-shadow pt-2 pb-5">
                    <div id="mapDiv" class="d-flex" style="width:100%; height: 500px"></div>
                    <div id="mapLegendDiv" style="width:100%; height: 100px"></div>
                    <div class="hide" id="projectUrl"
                         data-href="{{ route('front.projects.state', [$project->id]) }}"></div>
                </div>
            </div>
        </div>
        @include('common.script-modal')
    @endif

@endsection
@section('custom-script')
    <script src="//www.amcharts.com/lib/4/core.js"></script>
    <script src="//www.amcharts.com/lib/4/maps.js"></script>
    <script src="//www.amcharts.com/lib/4/themes/animated.js"></script>
    <script src="https://www.amcharts.com/lib/4/geodata/usaLow.js"></script>

    @if ($project->amChart !== null && $project->amChart->series !== null && $project->amChart->data !== null)
        <script src="{{ asset('js/amChartMap.min.js')}}"></script>
    @endif
@endsection

