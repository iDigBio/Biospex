@extends('front.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ __('Team') }}
@stop

{{-- Content --}}
@section('header')
    <header id="header-img" style="background-image: url(/storage/images/page-banners/banner-diversity.jpg);">
        <nav class="header navbar navbar-expand-md box-shadow">
            <a href="/"><img src="/storage/images/biospex_logo.svg" alt="BIOSPEX"
                             class="my-0 mr-md-auto top-logo font-weight-normal"/></a>
            @include('common.nav')
        </nav>
    </header>
@endsection

@section('content')
    <h2 class="text-center pt-4">{{ __('BIOSPEX Team') }}</h2>
    <hr class="header mx-auto" style="width:300px;">
    @foreach($categories as $category)
        <div class="row col-sm-10 mx-auto mt-4 justify-content-center">
            @include('front.team.partials.categories')
        </div>
    @endforeach
@endsection
