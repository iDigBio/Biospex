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
    @if ($project->amChart !== null && $project->amChart->series !== null && $project->amChart->data !== null)
        <div class="row my-5">
            <div class="col-sm-10 mx-auto mt-8">
                <h1 class="text-center content-header text-uppercase" id="expeditions">{{ __('pages.transcriptions') }}</h1>
                <div class="jumbotron box-shadow pt-2 pb-5">
                    <div id="transcriptDiv"
                         style=" width: 100%; height: {{ $amChartHeight }}px; color: #000000; font-size: 0.8em"></div>
                    <div id="transcriptLegendDiv"
                         style="width: 100%; height: {{ $amLegendHeight }}px; color: #000000; font-size: 0.8em"></div>
                </div>
            </div>
        </div>
        @include('common.script-modal')
    @endif
@endsection
@section('custom-script')
    <script src="//www.amcharts.com/lib/4/core.js"></script>
    <script src="//www.amcharts.com/lib/4/charts.js"></script>
    <script src="//www.amcharts.com/lib/4/themes/animated.js"></script>

    @if ($project->amChart !== null && $project->amChart->series !== null && $project->amChart->data !== null)
        <script> $("#script-modal").modal("show");</script>
        <script src="{{ asset('js/amChartTranscript.min.js')}}"></script>
        <script> $("#script-modal").modal("hide");</script>
    @endif
@endsection

