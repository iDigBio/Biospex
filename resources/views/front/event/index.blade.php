@extends('front.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ __('Events') }}
@stop

@section('header')
    <header id="header-img" style="background-image: url(/storage/images/page-banners/banner-binoculars.jpg);">
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
                <button id="eventViewToggle" class="btn btn-primary pl-4 pr-4"
                        data-toggle="collapse"
                        data-target="#activeEvents,#completedEvents"
                        data-value="true"
                >{{ __('View Completed Events') }}</button>
            </div>
            <hr class="header mx-auto">
        </div>
    </div>

    <div class="row">
        <div id="activeEvents" class="col-sm-12 show">
            <div class="d-flex justify-content-center col-sm-12 mb-4">
            <span data-name="active"
                  data-sort="title"
                  data-order="asc"
                  data-url="{{ route('events.post.sort') }}"
                  data-target="active-events"
                  class="sortPage mr-2" style="color: #e83f29; cursor: pointer;">
            <i class="fas fa-sort"></i> {{ __('TITLE') }}</span>
                <span data-name="active"
                      data-sort="project"
                      data-order="asc"
                      data-url="{{ route('events.post.sort') }}"
                      data-target="active-events"
                      class="sortPage ml-2" style="color: #e83f29; cursor: pointer;">
            <i class="fas fa-sort"></i> {{ __('PROJECT') }}</span>
            </div>
            <div id="active-events" class="row col-sm-12 mx-auto justify-content-center">
                @include('front.event.partials.event', ['events' => $events])
            </div>
        </div>
        <div id="completedEvents" class="col-sm-12 collapse">
            <div class="d-flex justify-content-center col-sm-12 mb-4">
            <span data-name="completed"
                  data-sort="title"
                  data-order="asc"
                  data-url="{{ route('events.post.sort') }}"
                  data-target="completed-events"
                  class="sortPage mr-2" style="color: #e83f29; cursor: pointer;">
            <i class="fas fa-sort"></i> {{ __('TITLE') }}</span>
                <span data-name="completed"
                      data-sort="project"
                      data-order="asc"
                      data-url="{{ route('events.post.sort') }}"
                      data-target="completed-events"
                      class="sortPage ml-2" style="color: #e83f29; cursor: pointer;">
            <i class="fas fa-sort"></i> {{ __('PROJECT') }}</span>
            </div>
            <div id="completed-events" class="row col-sm-12 mx-auto justify-content-center">
                @include('front.event.partials.event', ['events' => $eventsCompleted])
            </div>
        </div>
    </div>
    @include('front.event.partials.scoreboard')
@endsection

@section('footer')
    @include('common.footer')
    @include('common.contributors')
@endsection

@section('custom-script')
    @include('common.score-board-js')
@endsection
