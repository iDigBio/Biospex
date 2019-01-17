@extends('front.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ __('Projects') }}
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
    <h2 class="text-center pt-4">{{ __('BIOSPEX Projects') }}</h2>
    <hr class="header mx-auto" style="width:300px;">
    <div class="row">
        @include('common.project-sort', ['route' => route('front.projects.sort')])
    </div>
    <div id="projects" class="row col-sm-12 mx-auto justify-content-center">
        @include('front.project.partials.project', ['projects' => $projects])
    </div>
@endsection

@section('footer')
    @include('common.footer')
    @include('common.contributors')
@endsection
