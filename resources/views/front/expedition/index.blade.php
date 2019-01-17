@extends('front.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ __('Expeditions') }}
@stop

@section('header')
    <header id="header-img" style="background-image: url(/storage/images/page-banners/banner-image-girl.jpg);">
        <nav class="header navbar navbar-expand-md box-shadow">
            <a href="/"><img src="/storage/images/biospex_logo.svg" alt="BIOSPEX"
                             class="my-0 mr-md-auto top-logo font-weight-normal"/></a>
            @include('common.nav')
        </nav>
    </header>
@endsection

{{-- Content --}}
@section('content')
    <h2 class="text-center pt-4">{{ __('BIOSPEX Expeditions') }}</h2>
    <div class="row">
        <div class="col-sm-8 offset-md-2">
            <div class="text-center mt-4">
                <button class="toggle-view-btn  btn btn-primary pl-4 pr-4"
                        data-toggle="collapse"
                        data-target="#active-expeditions-main,#completed-expeditions-main"
                        data-value="{{ __('View Active Expeditions') }}"
                >{{ __('View Completed Expeditions') }}</button>
            </div>
            <hr class="header mx-auto">
        </div>
    </div>
    <div class="row">
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
@endsection

@section('footer')
    @include('common.footer')
    @include('common.contributors')
@endsection
