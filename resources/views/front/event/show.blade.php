@extends('front.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ __('pages.events') }}
@stop

@section('header')
    <header id="header-img" style="background-image: url(/images/page-banners/banner-image-group.jpg);">
        <nav class="header navbar navbar-expand-md box-shadow">
            <a href="/"><img src="/images/page/biospex_logo.svg" alt="BIOSPEX"
                             class="my-0 mr-md-auto top-logo font-weight-normal"/></a>
            @include('common.nav')
        </nav>
    </header>
@endsection

{{-- Content --}}
@section('content')
    <h2 class="text-center pt-4 text-uppercase">{{ $event->title }}</h2>
    <hr class="header mx-auto" style="width:300px;">
    <div class="d-flex align-items-center justify-content-center ">
        @include('front.event.partials.event-loop')
    </div>
    @if(GeneralHelper::eventBefore($event) || GeneralHelper::eventActive($event))
    <div class="row">
        <p class="text-center col-6 mx-auto mt-4">{!! $event->project->lastPanoptesProject->present()->project_link !!}</p>
    </div>
    <div class="row">
        <p class="text-justify col-6 mx-auto mt-4">{!! __('html.event_join_show') !!}</p>
    </div>
    @endif

    @if($chart)
    <div class="row my-5">
        <div class="col-sm-10 mx-auto mt-8">
            <h1 class="text-center content-header text-uppercase"
                id="expeditions">{{ __('Some Title Here') }}</h1>
            <div class="jumbotron box-shadow pt-2 pb-5">
                <div id="chartdiv" class="d-flex" style="width:100%; height: 500px"></div>
                <div class="hide" id="eventUrl" data-href="{{ route('ajax.get.step', [$event->id]) }}"></div>
            </div>
        </div>
    </div>
    @endif
    @include('common.scoreboard')
@endsection

@section('custom-script')
    @if($chart)
        <script src="//www.amcharts.com/lib/4/core.js"></script>
        <script src="//www.amcharts.com/lib/4/charts.js"></script>
        <script src="//www.amcharts.com/lib/4/themes/animated.js"></script>
        <script src="{{ asset('js/amChartEventStep.min.js')}}"></script>
    @endif
@endsection

