@extends('front.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ __('pages.bingo') }}
@stop

@section('header')
    <header id="header-img" style="background-image: url(/images/page-banners/banner-games.jpg);">
        <nav class="header navbar navbar-expand-md box-shadow">
            <a href="/"><img src="/images/page/biospex_logo.svg" alt="BIOSPEX"
                             class="my-0 mr-md-auto top-logo font-weight-normal"/></a>
            @include('common.nav')
        </nav>
    </header>
@endsection

{{-- Content --}}
@section('content')
    <h2 class="text-center pt-4 text-uppercase">{{ $bingo->title }}</h2>
    <hr class="header mx-auto" style="width:300px;">
    <div class="d-flex align-items-center justify-content-center ">
        @include('front.bingo.partials.bingo-loop')
    </div>
    <div class="row">
        @include('front.bingo.partials.words-table')
    </div>
@endsection