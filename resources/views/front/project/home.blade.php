@extends('front.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ $project->title }}
@stop

@section('header')
    <header style="background-image: url({{ $project->present()->banner_url }});">
        <nav class="header navbar navbar-expand-md box-shadow">
            <a href="/"><img src="/storage/images/biospex_logo.svg" alt="BIOSPEX"
                             class="my-0 mr-md-auto top-logo font-weight-normal"/></a>
            @include('common.nav')
        </nav>
    </header>
@endsection

{{-- Content --}}
@section('content')
    <div class="row">
        <div class="col-sm-10 mx-auto">
            <div class="card white box-shadow pt-2 pb-5 my-5 p-sm-5">
                <h1 class="text-center project-wide text-uppercase">
                    <small style="font-size:16px;">{{ __('Featured BIOSPEX Project') }}</small>
                    <br>{{ $project->title }}</h1>

                <div class="col-12">

                    <div class="d-flex align-items-start justify-content-between mt-4 mb-3">
                        {!! $project->present()->project_expeditions !!}
                        {!! $project->present()->project_events_icon_lg !!}
                        {!! $project->present()->twitter_icon_lg !!}
                        {!! $project->present()->facebook_icon_lg !!}
                        {!! $project->present()->blog_icon_lg !!}
                        {!! $project->present()->contact_email_icon_lg !!}
                    </div>
                </div>

                <hr class="pt-0 pb-4">

                <div class="col-12 col-md-10 offset-md-1">
                    <img src="{{ $project->present()->logo_url }}" class="float-md-right"
                         alt="Project biospex_logo img-fluid" style="width: 300px;">
                    @if($project->contact !== null)
                        <h3>{{ __('Contact') }}</h3>
                        <p>
                            <a href="mailto:{{ $project->contact_email }}" class="text">{{ $project->contact }}</a>
                        </p>
                    @endif

                    @if($project->organization !== null)
                        <h3>{{ __('Organization') }}</h3>
                        @if($project->organization_webiste !== null)
                            <p><a href="{{ $project->organization_webiste }}"
                                  target="_blank">{{ $project->organization }}</a></p>
                        @else
                            <p>{{ $project->project_partners }}</p>
                        @endif
                    @endif

                    @if($project->project_partners !== null)
                        <h3>{{ __('Partners') }}</h3>
                        <p>{{ $project->project_partners }}</p>
                    @endif

                    @if($project->funding_source !== null)
                        <h3>{{ __('Funding Sources') }}</h3>
                        <p>{{ $project->funding_source  }}</p>
                    @endif

                    @if($project->description_long !== null)
                        <h3>{{ __('Description') }}</h3>
                        @if($project->description_short !== null)
                            <p><strong>{{ $project->description_short  }}</strong></p>
                        @endif
                        <p>{{ $project->description_long }}</p>
                    @endif

                    @if($project->incentives !== null)
                        <h3>{{ __('Incentives') }}</h3>
                        <p>{{ $project->incentives }}</p>
                    @endif

                    @if($project->geographic_scope !== null)
                        <h3>{{ __('Geographic Scope') }}</h3>
                        <p>{{ $project->geographic_scope }}</p>
                    @endif

                    @if($project->taxonomic_scope !== null)
                        <h3>{{ __('Taxonomic Scope') }}</h3>
                        <p>{{ $project->taxonomic_scope }}</p>
                    @endif

                    @if($project->temporal_scope !== null)
                        <h3>{{ __('Temporal Scope') }}</h3>
                        <p>{{ $project->temporal_scope }}</p>
                    @endif

                    @if($project->language_skills !== null)
                        <h3>{{ __('Language Skills') }}</h3>
                        <p>{{ $project->language_skills }}</p>
                    @endif

                    @if($project->activities !== null)
                        <h3>{{ __('Activities') }}</h3>
                        <p>{{ $project->activities }}</p>
                    @endif

                    @if($project->resources->isNotEmpty())
                        <h3>{{ __('Resources') }}</h3>
                        @foreach($project->resources as $resource)
                            @if($resource->type === 'File Download')
                                <p><a href="{{ $resource->download->url() }}" target="_blank" data-toggle="tooltip"
                                      title="{{ $resource->description }}">{{ $resource->name }}</a></p>
                            @else
                                <p><a href="{{ $resource->name }}" target="_blank" data-toggle="tooltip"
                                      title="{{ $resource->description }}">{{ $resource->name }}</a></p>
                            @endif
                        @endforeach
                    @endif

                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-8 offset-md-2">
            <h1 class="text-center project-headers" id="expeditions">{{ __('Expeditions') }}</h1>
            <div class="text-center mt-4">
                <button id="expeditionViewToggle" class="btn btn-primary pl-4 pr-4"
                        data-toggle="collapse"
                        data-target="#activeExpeditions,#completedExpeditions"
                        data-value="true"
                >{{ __('View Completed Expeditions') }}</button>
            </div>
            <div class="d-flex align-items-start justify-content-between mt-4 mb-3">
                <span>{{ $project->expeditions->count() }} Expeditions</span>
                <span>{{ $project->transcriptions_count }} Transcriptions</span>
                <span>{{ $project->unique_transcribers_count }} Transcribers</span>
            </div>
            <hr class="header mx-auto">
        </div>
        <div id="activeExpeditions" class="row col-sm-12 mx-auto justify-content-center show">
            @include('front.expedition.partials.expedition', ['expeditions' => $expeditions])
        </div>
        <div id="completedExpeditions" class="row col-sm-12 mx-auto justify-content-center collapse">
            @include('front.expedition.partials.expedition', ['expeditions' => $expeditionsCompleted])
        </div>
    </div>

    <div class="row">
        <div class="col-sm-8 offset-md-2">
            <h1 class="text-center project-headers" id="events">{{ __('Events') }}</h1>
            <div class="text-center mt-4">
                <button id="eventViewToggle" class="btn btn-primary pl-4 pr-4"
                        data-toggle="collapse"
                        data-target="#activeEvents,#completedEvents"
                        data-value="true"
                >{{ __('View Completed Events') }}</button>
            </div>
            <hr class="header mx-auto">
        </div>
        <div id="activeEvents" class="row col-sm-12 mx-auto justify-content-center show">
            @include('front.event.partials.event', ['events' => $events])
        </div>
        <div id="completedEvents" class="row col-sm-12 mx-auto justify-content-center collapse">
            @include('front.event.partials.event', ['events' => $eventsCompleted])
        </div>
        @include('front.event.partials.scoreboard')
    </div>

    @if ($project->amChart !== null)
        <div class="row">
            <div class="col-sm-10 mx-auto mt-8">
                <h1 class="text-center project-headers" id="expeditions">{{ __('Transcriptions') }}</h1>
                <div class="card white box-shadow pt-2 pb-5">
                    <div id="chartdiv" style="height: 1200px; color: #000000; font-size: 0.8em"></div>
                </div>
            </div>
            <script src="//www.amcharts.com/lib/4/core.js"></script>
            <script src="//www.amcharts.com/lib/4/charts.js"></script>
            <script>
                var chart = am4core.createFromConfig({!! $project->amChart->data !!}, "chartdiv", am4charts.XYChart);
            </script>
        </div>
    @endif
    @if ($project->fusion_table_id !== null)
    <div class="row">
        <div class="col-sm-10 mx-auto mt-8">
            <h1 class="text-center project-headers" id="expeditions">{{ __('Heat Map Transcribed Specimens') }}</h1>
            <div class="card white box-shadow pt-2 pb-5 my-5 p-sm-5">
                <iframe width="100%" height="800" scrolling="no" frameborder="no" src="https://fusiontables.google.com/embedviz?q=select+col2+from+{{ $project->fusion_table_id }}&amp;viz=MAP&amp;h=false&amp;lat=34.72404554786575&amp;lng=-93.08009375000002&amp;t=1&amp;z=3&amp;l=col2&amp;y={{ $project->fusion_style_id }}&amp;tmplt={{ $project->fusion_template_id }}&amp;hml=GEOCODE"></iframe>
            </div>
        </div>
    </div>
    @endif
@endsection

@section('footer')
    @include('common.footer')
    @include('common.contributors')
@endsection
