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
            <!-- sort-up sort-down
            $(this).find($(".fa")).removeClass('fa-chevron-down').addClass('fa-chevron-up');
            -->
            <span id="name" class="mr-2 sort-projects" style="color: #e83f29; cursor: pointer;"><i class="fas fa-sort"></i> NAME</span>
            <span id="group" class="ml-2 sort-projects" style="color: #e83f29; cursor: pointer;"><i class="fas fa-sort"></i> GROUP</span>
        </div>
    </div>
    <div class="row" id="public-projects">
        @include('front.project.partials.project', ['projects' => $projects])
    </div>
@endsection

@section('footer')
    @include('common.footer')
    @include('common.contributors')
@endsection
