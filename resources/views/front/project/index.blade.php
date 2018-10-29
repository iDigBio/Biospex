@extends('front.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ __('Projects') }}
@stop

@section('header')
    <header style="background-image: url(/storage/images/page-banners/banner-binoculars.jpg);">
        <nav class="header navbar navbar-expand-md box-shadow">
            <a href="/"><img src="/storage/images/biospex_logo.svg" alt="BIOSPEX" class="my-0 mr-md-auto top-logo font-weight-normal"/></a>
            @include('common.nav')
        </nav>
    </header>
@endsection

{{-- Content --}}
@section('content')
    <h2 class="text-center pt-4">{{ __('BIOSPEX Projects') }}</h2>
    <hr class="header mx-auto" style="width:300px;">
    <div class="container">
        <div class="row">

            <div class="col-md-4">
                <div class="card-project mb-4 px-4 box-shadow">
                    <h2 class="text-center pt-4">Project Tile Project Name</h2>
                    <hr>
                    <div class="row card-body">
                        <div class="col-7">
                            <ul>
                                <li>FSU Herbautiom Collection</li>
                                <li>346 Expeditions</li>
                                <li>655 Transcriptions</li>
                                <li>400 Unique Transcriptions</li>
                            </ul>
                        </div>

                        <div class="col-5">
                            <img class="img-fluid" src="/storage/images/biospex-logo-greyscale" alt="Card image cap">
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="d-flex align-items-start justify-content-between mt-4 mb-3">
                            <a href="#"><i class="fas fa-binoculars"></i> <span class="d-none text d-sm-inline"></span></a>
                            <a href="#"><i class="far fa-calendar-times"></i> <span
                                        class="d-none text d-sm-inline"></span></a>
                            <a href="#"><i class="fab fa-twitter"></i> <span class="d-none text d-sm-inline"></span></a>
                            <a href="#"><i class="far fa-envelope"></i> <span
                                        class="d-none text d-sm-inline"></span></a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('footer')
    @include('common.footer')
    @include('common.contributors')
@endsection
