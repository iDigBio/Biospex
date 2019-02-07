@extends('front.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ $project->title }}
@stop

@section('header')
    <header style="background-image: url({{ $project->present()->banner_file_url }});">
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
            <div class="jumbotron box-shadow pt-2 pb-5 my-5 p-sm-5">
                <h1 class="text-center project-wide text-uppercase">
                    <small style="font-size:16px;">{{ __('Featured BIOSPEX Project') }}</small>
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
                    <img src="{{ $project->present()->logo_url }}" class="img-fluid"
                         alt="Project biospex_logo">
                    </div>
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
                            <p>{!! $resource->present()->resource !!}</p>
                        @endforeach
                    @endif

                </div>
            </div>
        </div>
    </div>

    <div class="row my-5">
        <div class="col-sm-8 offset-md-2">
            <h1 class="text-center content-header" id="expeditions">{{ __('Expeditions') }}</h1>
            <div class="text-center mt-4">
                <button class="toggle-view-btn btn btn-primary"
                        data-toggle="collapse"
                        data-target="#active-expeditions-main,#completed-expeditions-main"
                        data-value="{{ __('View Active Expeditions') }}"
                >{{ __('View Completed Expeditions') }}</button>
            </div>
            <div class="d-flex align-items-start justify-content-between mt-4 mb-3">
                <span class="text">{{ $project->expeditions->count() }} Expeditions</span>
                <span class="text">{{ $transcriptionsCount }} Transcriptions</span>
                <span class="text">{{ $transcribersCount }} Transcribers</span>
            </div>
            <hr class="header mx-auto">
        </div>
        <div id="active-expeditions-main" class="col-sm-12 show">
            @include('common.expedition-sort', ['type' => 'active', 'route' => route('front.expeditions.sort')])
            <div id="active-expeditions" class="row col-sm-12 mx-auto justify-content-center">
                @include('front.expedition.partials.expedition', ['expeditions' => $expeditions])
            </div>
        </div>
        <div id="completed-expeditions-main" class="col-sm-12 collapse">
            @include('common.expedition-sort', ['type' => 'completed', 'route' => route('front.expeditions.sort')])
            <div id="completed-expeditions" class="row col-sm-12 mx-auto justify-content-center">
                @include('front.expedition.partials.expedition', ['expeditions' => $expeditionsCompleted])
            </div>
        </div>
    </div>

    <div class="row my-5">
        <div class="col-sm-8 offset-md-2">
            <h1 class="text-center content-header" id="events">{{ __('Events') }}</h1>
            <div class="text-center mt-4">
                <button class="toggle-view-btn btn btn-primary"
                        data-toggle="collapse"
                        data-target="#active-events-main,#completed-events-main"
                        data-value="{{ __('View Active Events') }}"
                >{{ __('View Completed Events') }}</button>
            </div>
            <hr class="header mx-auto">
        </div>
        <div id="active-events-main" class="col-sm-12 show">
            @include('common.event-sort', ['type' => 'active', 'route' => route('front.events.sort')])
            <div id="active-events" class="row col-sm-12 mx-auto justify-content-center">
                @include('front.event.partials.event', ['events' => $events])
            </div>
        </div>
        <div id="completed-events-main" class="col-sm-12 collapse">
            @include('common.event-sort', ['type' => 'completed', 'route' => route('front.events.sort')])
            <div id="completed-events" class="row col-sm-12 mx-auto justify-content-center">
                @include('front.event.partials.event', ['events' => $eventsCompleted])
            </div>
        </div>
        @include('common.scoreboard')
    </div>

    @if ($project->amChart !== null && $project->amChart->series !== null && $project->amChart->data !== null)
        <div class="row my-5">
            <div class="col-sm-10 mx-auto mt-8">
                <h1 class="text-center content-header" id="expeditions">{{ __('Transcriptions') }}</h1>
                <div class="jumbotron box-shadow pt-2 pb-5">
                    <div id="chartdiv"
                         style=" width: 100%; height: {{ $amChartHeight }}px; color: #000000; font-size: 0.8em"></div>
                    <div id="legenddiv"
                         style="width: 100%; height: {{ $amLegendHeight }}px; color: #000000; font-size: 0.8em"></div>
                </div>
            </div>
        </div>
    @endif

    @if ($project->fusion_table_id !== null)
        <div class="row my-5">
            <div class="col-sm-10 mx-auto mt-8">
                <h1 class="text-center content-header" id="expeditions">{{ __('Heat Map Transcribed Specimens') }}</h1>
                <div class="jumbotron box-shadow pt-2 pb-5 my-5 p-sm-5">
                    <iframe width="100%" height="800" scrolling="no" frameborder="no"
                            src="https://fusiontables.google.com/embedviz?q=select+col2+from+{{ $project->fusion_table_id }}&amp;viz=MAP&amp;h=false&amp;lat=34.72404554786575&amp;lng=-93.08009375000002&amp;t=1&amp;z=3&amp;l=col2&amp;y={{ $project->fusion_style_id }}&amp;tmplt={{ $project->fusion_template_id }}&amp;hml=GEOCODE"></iframe>
                </div>
            </div>
        </div>
    @endif

@endsection
@section('custom-script')
    @if ($project->amChart !== null && $project->amChart->series !== null && $project->amChart->data !== null)
    <script>
        var legendContainer = am4core.createFromConfig({
            "width": "100%",
            "height": "100%"
        }, "legenddiv", am4core.Container);
        var chart = am4core.createFromConfig(
            {
                "xAxes": [{
                    "type": "DateAxis",
                    "renderer": {
                        "minGridDistance": 50
                    },
                    "startLocation": 0.5,
                    "endLocation": 0.5,
                    "baseInterval": {
                        "timeUnit": "day",
                        "count": 1
                    },
                    "tooltip": {
                        "background": {
                            "fill": "#07BEB8",
                            "strokeWidth": 0,
                            "cornerRadius": 3,
                            "pointerLength": 0
                        },
                        "dy": 5
                    }
                }],
                "yAxes": [{
                    "type": "ValueAxis",
                    "tooltip": {
                        "disabled": true
                    },
                    "calculateTotals": true
                }],
                "cursor": {
                    "type": "XYCursor",
                    "lineX": {
                        "stroke": "#8F3985",
                        "strokeWidth": 4,
                        "strokeOpacity": 0.2,
                        "strokeDasharray": ""
                    },
                    "lineY": {
                        "disabled": true
                    }
                },
                "scrollbarX": {
                    "type": "Scrollbar"
                },
                "legend": {
                    "parent": legendContainer
                },
                "dateFormatter": {
                    "inputDateFormat": "yyyy-MM-dd"
                },

                "series": {!! $project->amChart->series !!},
                "data": {!! $project->amChart->data !!},
            }, "chartdiv", am4charts.XYChart);
    </script>
    @endif
@endsection

