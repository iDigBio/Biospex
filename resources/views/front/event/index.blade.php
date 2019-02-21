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
    <h2 class="text-center pt-4">{{ __('BIOSPEX EVENTS') }}</h2>
    <hr class="header mx-auto" style="width:300px;">
    <div class="row">
        <div class="text-center my-4 mx-auto">
            <button class="toggle-view-btn btn btn-primary"
                    data-toggle="collapse"
                    data-target="#active-events-main,#completed-events-main"
                    data-value="{{ __('VIEW ACTIVE EVENTS') }}"
            >{{ __('VIEW COMPLETED EVENTS') }}</button>
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

