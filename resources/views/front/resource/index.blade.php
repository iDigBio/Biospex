@extends('front.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ __('pages.resources') }}
@stop

@section('header')
    <header id="header-img" style="background-image: url(/storage/images/page-banners/banner-seaoates.jpg);">
        <nav class="header navbar navbar-expand-md box-shadow">
            <a href="/"><img src="/storage/images/page/biospex_logo.svg" alt="BIOSPEX"
                             class="my-0 mr-md-auto top-logo font-weight-normal"/></a>
            @include('common.nav')
        </nav>
    </header>
@endsection

@section('content')
    <h2 class="text-center pt-4 text-uppercase">{{ __('pages.biospex') }} {{ __('pages.resources') }}</h2>
    <hr class="header mx-auto" style="width:300px;">
    <div class="row col-sm-12 mx-auto mt-4 justify-content-center">
        @include('front.resource.partials.resource')
    </div>
@endsection

@section('footer')
    @include('front.layout.contributors')
@endsection