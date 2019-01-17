@extends('front.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ __('Resources') }}
@stop

@section('header')
    <header id="header-img" style="background-image: url(/storage/images/page-banners/banner-seaoates.jpg);">
        <nav class="header navbar navbar-expand-md box-shadow">
            <a href="/"><img src="/storage/images/biospex_logo.svg" alt="BIOSPEX"
                             class="my-0 mr-md-auto top-logo font-weight-normal"/></a>
            @include('common.nav')
        </nav>
    </header>
@endsection

@section('content')
    <h2 class="text-center pt-4">{{ __('BIOSPEX Resources') }}</h2>
    <hr class="header mx-auto" style="width:300px;">
    <div class="row col-sm-12 mx-auto justify-content-center" id="public-expeditions">
        @include('front.resource.partials.resource', ['resources' => $resources])
    </div>
@endsection

@section('footer')
    @include('common.footer')
    @include('common.contributors')
@endsection