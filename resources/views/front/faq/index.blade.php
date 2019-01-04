@extends('front.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ __('Faq') }}
@stop

@section('header')
    <header id="header-img" style="background-image: url(/storage/images/page-banners/banner-maps.jpg);">
        <nav class="header navbar navbar-expand-md box-shadow">
            <a href="/"><img src="/storage/images/biospex_logo.svg" alt="BIOSPEX"
                             class="my-0 mr-md-auto top-logo font-weight-normal"/></a>
            @include('common.nav')
        </nav>
    </header>
@endsection

@section('content')
    <h2 class="text-center col-6 mx-auto pt-4">
        {{ __('Find your BIOSPEX answers for your frequently asked questions answered here.') }}</h2>
    <hr class="header mx-auto" style="width:300px;">
    <div class="col-12 col-md-10 offset-md-1">
        <div class="card white box-shadow py-5 my-5 p-sm-5">
            @each('front.faq.partials.category-loop', $categories, 'category')
        </div>
    </div>
    <div class="text-center mb-4">
        <h2 class="col-6 pt-4 mx-auto">
            {{ __('Don\'t see your question listed above?') }}</h2>
        <a href="{{ route('contact.get.index') }}" class="btn btn-primary mx-auto">{{ __('CONTACT US') }}</a>
    </div>
@endsection

@section('footer')
    @include('common.footer')
    @include('common.contributors')
@endsection
