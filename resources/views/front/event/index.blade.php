@extends('front.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ __('Events') }}
@stop

@section('header')
    <header style="background-image: url(/storage/images/page-banners/banner-binoculars.jpg);">
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
    <hr class="header mx-auto" style="width:300px;">
    <div class="row">
        <div class="col-md-6 mx-auto mb-4 text-center">
            <span data-name="title" data-order="asc" data-url="{{ route('events.public.get.sort') }}" data-target="public-events"
                  class="sortPage mr-2" style="color: #e83f29; cursor: pointer;"><i
                        class="fas fa-sort"></i> {{ __('TITLE') }}</span>
            <span data-name="project" data-order="asc" data-url="{{ route('events.public.get.sort') }}" data-target="public-events"
                  class="sortPage ml-2" style="color: #e83f29; cursor: pointer;"><i
                        class="fas fa-sort"></i> {{ __('PROJECT') }}</span>
        </div>
    </div>
    <div class="row" id="public-expeditions">
        @include('front.event.partials.event', ['events' => $events])
    </div>
    <hr class="header mx-auto m mt-5 mb-5" style="width:500px;">
    <div class="text-center mb-5">
        <button class="completedButton btn btn-primary pl-4 pr-4"
                data-url="{{ route('events.completed.get.sort') }}"
                data-target="completed-events">{{ __('COMPLETED') }}</button>
    </div>
    <div id="completed" class="row" style="display: none">
        <div class="col-md-6 mx-auto mb-4 text-center">
            <span data-name="title"
                  data-order="asc"
                  data-url="{{ route('events.completed.get.sort') }}"
                  data-target="completed-events"
                  class="sortPage mr-2" style="color: #e83f29; cursor: pointer;">
                <i class="fas fa-sort"></i> {{ __('TITLE') }}</span>
            <span data-name="project"
                  data-order="asc"
                  data-url="{{ route('events.completed.get.sort') }}"
                  data-target="completed-events" class="sortPage ml-2" style="color: #e83f29; cursor: pointer;">
                <i class="fas fa-sort"></i> {{ __('PROJECT') }}</span>
        </div>
    </div>
    <div class="row" id="completed-events" style="display: none"></div>
    @include('front.event.partials.scoreboard')
@endsection

@section('footer')
    @include('common.footer')
    @include('common.contributors')
@endsection
