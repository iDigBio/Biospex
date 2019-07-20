@extends('front.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ __('API') }}
@stop

{{-- Content --}}
@section('header')
    <header style="background-image: url(/images/page-banners/banner-field.jpg);">
        <nav class="header-admin navbar navbar-expand-md box-shadow">
            <a href="/"><img src="/images/page/biospex_logo.svg" alt="BIOSPEX"
                             class="my-0 mr-md-auto top-logo-admin font-weight-normal"/></a>
            @include('common.nav')
        </nav>
    </header>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8 my-4 mx-auto">
            <div class="card white px-4 box-shadow h-100">
                <h3 class="text-center pt-4 text-uppercase">{{ __('pages.biospex') }} {{ __('pages.api') }}</h3>
                <hr class="header mx-auto" style="width:300px;">
                <div class="d-flex align-items-start justify-content-between my-4 mx-auto">
                    <a class="btn btn-primary mr-4" href="https://biospex.docs.apiary.io/#">{{ __('pages.api_docs') }}</a>
                    <a class="btn btn-primary mr-4" href="https://github.com/iDigBio/Biospex">{{ __('pages.github') }}</a>
                    <a class="btn btn-primary mr-4" href="{{ route('api.get.login') }}">{{ __('pages.login') }}</a>
                    <a class="btn btn-primary mr-4" href="{{ route('api.get.register') }}">{{ __('pages.register') }}</a>
                </div>
            </div>
        </div>
    </div>
@endsection
