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
    <div class="row my-5">
        <div class="col-sm-10 mx-auto mt-8">
            <h1 class="text-center content-header text-uppercase" id="expeditions">{{ __('pages.transcriptions') }}</h1>
            <div class="text-center mt-4 mb-4">
                @foreach($years as $year)
                    <button class="btn btn-primary btn-transcription" id="{{ $year }}" data-year="{{ $year }}"
                            data-project="{{ $project->id }}">{{ $year }}
                    </button>
                @endforeach
            </div>
            <div class="jumbotron box-shadow pt-2 pb-5">
                <div id="transcripts"
                     style="color: #000000; font-size: 0.8em"></div>
            </div>
        </div>
    </div>
@endsection
@section('custom-script')
    <script src="//www.amcharts.com/lib/4/core.js"></script>
    <script src="//www.amcharts.com/lib/4/charts.js"></script>
    <script src="//www.amcharts.com/lib/4/themes/animated.js"></script>
    <script>
        let transcripts;
        $(function () {
            let years = Laravel.years;
            let project = Laravel.project;

            if (years.length > 0) {
                let year = years[0];
                $('#'+year).removeClass('btn-primary').addClass('btn-transcription-year');
                let url = "/projects/" + project + "/transcriptions/" + year;
                loadChart(url);
            }
        });

        $('.btn-transcription').on('click', function () {
            transcripts.dispose();
            let year = $(this).data('year');
            let project = $(this).data('project');
            $(this).removeClass('btn-primary').addClass('btn-transcription-year');
            $(this).siblings().removeClass('btn-transcription-year').addClass('btn-primary');
            let url = "/projects/" + project + "/transcriptions/" + year;
            loadChart(url);
        });

        function loadChart(url) {
            let ds = new am4core.DataSource();
            ds.url = url;
            ds.events.on("done", function (ev) {
                buildChart(ev.data.config);
            });
            ds.load();
        }

        function buildChart(config) {
            transcripts = am4core.createFromConfig(config, "transcripts", am4charts.XYChart);
            transcripts.preloader.hiddenState.transitionDuration = 0;
            let cellSize = 1.5;
            transcripts.events.on("datavalidated", function(ev) {
                // Get objects of interest
                let chart = ev.target;
                let categoryAxis = chart.yAxes.getIndex(0);

                // Calculate how we need to adjust chart height
                let adjustHeight = chart.data.length * cellSize - categoryAxis.pixelHeight;

                // get current chart height
                let targetHeight = chart.pixelHeight + adjustHeight;

                // Set it on chart's container
                chart.svgContainer.htmlElement.style.height = targetHeight + "px";
            });
        }
    </script>


@endsection

