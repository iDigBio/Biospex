@extends('front.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ __('Lead Public Digitization Expeditions') }}
@stop

@section('custom-style')
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
@endsection

{{-- Content --}}
@section('header')
    <header class="header home">
        <nav class="navbar navbar-expand-md">
            <a href="/"><img src="/storage/images/biospex_logo.svg" alt="BIOSPEX"
                             class="my-0 mr-md-auto top-logo font-weight-normal"/></a>
            @include('common.nav')
        </nav>
        <div class="container text-center">
            <div class="row py-5">
                <div class="col-12" data-aos="fade-right" data-aos-easing="ease-in" data-aos-duration="2000">
                    <h1 class="text-white align-middle home-banner-tag">{{ __('Provision, advertise and lead expeditions.') }}
                        <br>
                        <a href="#learn-more" data-scroll class="btn btn-primary mt-4" data-aos="fade-right"
                           data-aos-easing="ease-out" data-aos-duration="3000">Learn More</a>
                    </h1>

                </div>
            </div>
        </div>
    </header>
    @include('front.layout.contributors')
@endsection

@section('content')
    <section class="home-heading text-center">
        <div class="container">
            <img src="/storage/images/logo-tagline.png" align="Biospex Tag Line">
            <p class="text-justify mt-4">{{ __('BIOSPEX is a base camp for launching, advertising and managing targeted efforts to digitize
            the world\'s 3 billion biodiversity research specimens in ways that involve the public. It enables you
            to package projects in one or a series of digitized expeditions, launch the expeditions at crowdsourcing
            tools, widely recruit others to participate, and layer resrouces on the experience to advance science
            literacy. In the end, you can download the new data for specimen curation, research, conservation, natural
            resource management, public policy, or other activities.') }}</p>
        </div>
    </section>
    <!--
    Tutorial Section -->
    <section class="tutorial-2" id="learn-more">
        <div class="container" style="position:relative;">
            <img src="/storage/images/arrow-curved.svg" alt="-->" class="home-arrow d-none d-sm-none d-md-block">
            <div class="row p-5">

                <div class="col-sm-6 mt-5 p-1 pb-md-3">
                    <h2 class="home-header-cta">{{ __('A Project') }}</h2>

                    <div class="card mb-4 px-4 box-shadow" data-aos="fade-down" data-aos-easing="ease-in"
                         data-aos-duration="2000" data-aos-once="true">
                        <h2 class="text-center pt-4">{{ $expedition->project->title }}</h2>
                        <hr>
                        <div class="row card-body pb-2">
                            <div class="col-12">
                                <div class="col-4 float-right">
                                    <img class="img-fluid" src="{{ $expedition->project->present()->logo_url }}"
                                         alt="Card image cap">
                                </div>
                                <p>{{ $expedition->project->description_short }}</p>
                            </div>

                            <div class="col-12">
                                <ul class="text">
                                    <li class="mt-3">{{ $expedition->project->expeditions_count }} {{ __('Expeditions') }}</li>
                                    <li>{{ CountHelper::projectTranscriberCount($expedition->project->id) }} {{ __('Transcribers') }}</li>
                                    <li>{{ CountHelper::projectTranscriptionCount($expedition->project->id) }} {{ __('Transcriptions') }}</li>
                                </ul>
                            </div>
                        </div>

                        <div class="card-footer pb-4">
                            <div class="d-flex align-items-start justify-content-between mt-4">
                                {!! $expedition->project->present()->project_page_icon_lrg !!}
                                {!! $expedition->project->present()->project_events_icon_lrg !!}
                                {!! $expedition->project->present()->organization_icon_lrg !!}
                                {!! $expedition->project->present()->twitter_icon_lrg !!}
                                {!! $expedition->project->present()->facebook_icon_lrg !!}
                                {!! $expedition->project->present()->blog_icon_lrg !!}
                                {!! $expedition->project->present()->contact_email_icon_lrg !!}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 p-1 p-md-5 tutorial-right-section">
                    <h2 class="home-header-cta flex-nowrap">An Expedition</h2>
                    <div class="card black mb-4 box-shadow" data-aos="fade-up" data-aos-duration="1500"
                         data-aos-anchor-placement="bottom-bottom" data-aos-once="true">
                        <div class="card-top m-0 p-0">
                            <img class="card-img-top" src="{{ $expedition->present()->logo_url }}" alt="Card image cap">
                            <div class="card-img-overlay">
                                <h2 class="card-title text-center pt-4">{{ $expedition->title }}</h2>
                                <i class="card-info fas fa-info-circle fa-2x float-right"
                                   style="top: 20rem; left: 31.25rem;"></i>
                                <p style="width: 500px;">{{ $expedition->description }}</p>
                            </div>
                        </div>

                        <div class="card-body white"
                             style="border-bottom-left-radius: 10px; border-bottom-right-radius: 10px;">
                            <div class="d-flex justify-content-between">
                                <div class="p-2">
                                    <p>{{ $expedition->stat->transcriptions_completed }} {{ __('Transcriptions') }}</p>
                                </div>
                                <div class="p-2"><p>{{ $expedition->stat->percent_completed }}% {{ __('Complete') }}</p>
                                </div>
                            </div>
                            <hr>
                            <div class="d-flex align-items-start justify-content-between mt-4 mx-auto">
                                {!! $expedition->project->present()->project_page_icon_lrg !!}
                                @isset($expedition->nfnWorkflow)
                                    {!! $expedition->nfnWorkflow->present()->nfn_url_lrg !!}
                                @endisset
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <section>
        <div class="row">
            <div class="col d-flex justify-content-center">
                <div class="col-md-4 text-center" role="group" aria-label="External carousel buttons">
                    <h2>The Process<br>
                        <span class="smallertext">{{ __('Specimen digitization is easy as 123') }}</span></h2>
                    <ul id="external-carousel-btns" class="list-inline">
                        <li data-target="#processCarousel" data-slide-to="0"
                            class="carousel-li-0 active list-inline-item">1
                        </li>
                        <li data-target="#processCarousel" data-slide-to="1" class="carousel-li-1 list-inline-item">2
                        </li>
                        <li data-target="#processCarousel" data-slide-to="2" class="carousel-li-2 list-inline-item">3
                        </li>
                    </ul>

                    <div class="carousel-item carousel-div div-0 active">
                        <h3 class="text-center" style="color: #8cc640">{{ __('STEP 1') }}</h3>
                        <p class="text-justify smallertext">{{ __('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.') }}</p>
                    </div>
                    <div class="carousel-item carousel-div div-1">
                        <h3 class="text-center" style="color: #8cc640">{{ __('STEP 2') }}</h3>
                        <p class="text-justify smallertext">{{ __('Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.') }}</p>
                    </div>
                    <div class="carousel-item carousel-div div-2">
                        <h3 class="text-center" style="color: #8cc640">{{ __('STEP 3') }}</h3>
                        <p class="text-justify smallertext">{{ __('Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.') }}</p>
                    </div>
                </div>
                <div id="processCarousel" class="carousel slide col-md-6">
                    <div class="carousel-inner box-shadow inside-carousel">
                        <div class="carousel-item active"
                             style="background-image: url(/storage/images/slider/slider1.png);">
                            <div class="circle-slider p-5">
                                <h3 class="text-center">{{ __('PROJECT') }}</h3>
                                <p>{{ __('Create a Project for your digital images and use BIOSPEX to run optical character
                                    recognition (OCR) on the images. Bundle the specimen images using the OCR text
                                    string, or similar traits like State, County, or collection date, into various
                                    Expeditions that will ignite public interest.') }}</p>
                            </div>
                        </div>
                        <div class="carousel-item" style="background-image: url(/storage/images/slider/slider2.png);">
                            <div class="circle-slider p-5">
                                <h3 class="text-center">{{ __('EXPEDITIONS') }}</h3>
                                <p>{{ __('The curator then uses BIOSPEX to deploy the expeditions a few at a time to an
                                    existing website with a large citizen science community for label transcription.') }}</p>
                            </div>
                        </div>
                        <div class="carousel-item" style="background-image: url(/storage/images/slider/slider3.png);">
                            <div class="circle-slider p-5">
                                <h3 class="text-center">{{ __('EXPORT') }}</h3>
                                <p>{{ __('The curator processes the resulting transcriptions in BIOSPEX later and exports
                                    the data back to her local data management system.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
@section('custom-script')
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            mirror: true
        });
    </script>
    <script src="https://cdn.jsdelivr.net/gh/cferdinandi/smooth-scroll/dist/smooth-scroll.polyfills.min.js"></script>
    <script>var scroll = new SmoothScroll('a[href*="#"]');</script>

@endsection