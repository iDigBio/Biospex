@extends('front.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ __('pages.api') }}
@stop

{{-- Content --}}
@section('header')
    <header style="background-image: url(/storage/images/page-banners/banner-field.jpg);">
        <nav class="header-admin navbar navbar-expand-md box-shadow">
            <a href="/"><img src="/storage/images/biospex_logo.svg" alt="BIOSPEX"
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
                    <a class="btn btn-primary mr-4" href="https://biospex.docs.apiary.io/#">API Docs</a>
                    <a class="btn btn-primary mr-4" href="https://github.com/iDigBio/Biospex">GitHub</a>
                    <a class="btn btn-primary mr-4" rel="nofollow" data-method="delete" href="{{ route('api.get.logout') }}">Sign Out</a>
                </div>
                <div id="app" class="col-md-10 mx-auto my-4">
                    <!-- let people make clients -->
                    <passport-clients></passport-clients>
                </div>
            </div>
        </div>
    </div>
@endsection
