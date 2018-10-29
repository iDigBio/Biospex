@extends('front.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ __('Team') }}
@stop

{{-- Content --}}
@section('header')
    <header style="background-image: url(/storage/images/page-banners/banner-diversity.jpg);">
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
        <div class="col-10 mx-auto">
            @foreach($categories as $category)
                @include('front.team.partials.categories')
            @endforeach
        </div>
@endsection

@section('footer')
    @include('common.footer')
    @include('common.contributors')
@endsection