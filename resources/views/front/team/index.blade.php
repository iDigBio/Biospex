@extends('front.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ __('pages.team') }}
@stop

{{-- Content --}}
@section('header')
    <header id="header-img" style="background-image: url(/storage/images/page-banners/banner-diversity.jpg);">
        <nav class="header navbar navbar-expand-md box-shadow">
            <a href="/"><img src="/storage/images/page/biospex_logo.svg" alt="BIOSPEX"
                             class="my-0 mr-md-auto top-logo font-weight-normal"/></a>
            @include('common.nav')
        </nav>
    </header>
@endsection

@section('content')
    <h2 class="text-center pt-4 text-uppercase">{{ __('pages.biospex') }} {{ __('pages.team') }}</h2>
    <hr class="header mx-auto" style="width:300px;">
    @foreach($categories as $category)
        <div class="row col-sm-10 mx-auto mt-4 justify-content-center">
            @include('front.team.partials.categories')
        </div>
    @endforeach
@endsection
