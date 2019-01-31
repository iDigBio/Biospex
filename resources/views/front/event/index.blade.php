@extends('front.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ __('Events') }}
@stop

@section('header')
    <header id="header-img" style="background-image: url(/storage/images/page-banners/banner-image-group.jpg);">
        <nav class="header navbar navbar-expand-md box-shadow">
            <a href="/"><img src="/storage/images/biospex_logo.svg" alt="BIOSPEX"
                             class="my-0 mr-md-auto top-logo font-weight-normal"/></a>
            @include('common.nav')
        </nav>
    </header>
@endsection

{{-- Content --}}
@section('content')
    <h2 class="text-center pt-4">{{ __('BIOSPEX Events') }}</h2>

    <div class="row">
        <div class="col-sm-8 offset-md-2">
            <div class="text-center mt-4">
                <button class="toggle-view-btn btn btn-primary"
                        data-toggle="collapse"
                        data-target="#active-events-main,#completed-events-main"
                        data-value="{{ __('View Active Events') }}"
                >{{ __('View Completed Events') }}</button>
            </div>
            <hr class="header mx-auto">
        </div>
    </div>

    <div class="row">
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
    </div>
    @include('common.scoreboard')
@endsection

