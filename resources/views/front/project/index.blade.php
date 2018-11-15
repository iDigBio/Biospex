@extends('front.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ __('Projects') }}
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
    <h2 class="text-center pt-4">{{ __('BIOSPEX Projects') }}</h2>
    <hr class="header mx-auto" style="width:300px;">
    <div class="row">
        <div class="col-md-6 mx-auto mb-4 text-center">
            <span id="name" data-url="{{ route('projects.get.sort') }}" data-target="public-expeditions"
                  class="mr-2 sortPage" style="color: #e83f29; cursor: pointer;"><i
                        class="fas fa-sort"></i> {{ __('NAME') }}</span>
            <span id="group" data-url="{{ route('projects.get.sort') }}" data-target="public-expeditions"
                  class="ml-2 sortPage" style="color: #e83f29; cursor: pointer;"><i
                        class="fas fa-sort"></i> {{ __('GROUP') }}</span>
        </div>
    </div>
    <div class="row" id="public-expeditions">
        @include('front.project.partials.project', ['projects' => $projects])
    </div>
@endsection

@section('footer')
    @include('common.footer')
    @include('common.contributors')
@endsection
