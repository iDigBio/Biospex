@extends('front.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ __('Lead Public Digitization Expeditions') }}
@stop

@section('custom-style')
    <link href="https://unpkg.com/aos@next/dist/aos.css" rel="stylesheet">
@endsection

{{-- Content --}}
@section('header')
    <header class="header home">
        <nav class="navbar navbar-expand-md">
            <a href="/"><img src="images/biospex_logo.svg" alt="BIOSPEX"
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
    @include('common.contributors')
@endsection

@section('content')
    <section class="jumbotron text-center">
        <div class="container">
            <img src="/images/logo-tagline.png" align="Biospex Tag Line">
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
            <img src="/images/arrow-curved.svg" alt="-->" class="home-arrow d-none d-sm-none d-md-block">
            <div class="row p-5">

                <div class="col-md-6 mt-5 p-1 pb-md-3">
                    <h2 class="home-header-cta">The Project</h2>

                    <div class="card-project mb-4 px-4 box-shadow" data-aos="fade-down" data-aos-easing="ease-in"
                         data-aos-duration="2000" data-aos-once="true">
                        <h2 class="text-center pt-4">{{ $project->title }}</h2>
                        <hr>
                        <div class="row card-body">
                            <div class="col-7">
                                <ul>
                                    <li>{{ $project->description_short }}</li>
                                    <li>{{ $project->expeditions_count }} {{ __('Expeditions') }}</li>
                                    <li>{{ $project->transcriptions_count }} {{ __('Transcriptions') }}</li>
                                </ul>
                            </div>

                            <div class="col-5">
                                <img class="img-fluid" src="{{ $project->present()->logo_standard_url }}" alt="Card image cap">
                            </div>
                        </div>

                        <div class="card-footer">
                            <div class="d-flex align-items-start justify-content-between mt-4 mb-3">
                                <a href="#"><i class="fas fa-binoculars"></i> <span
                                            class="d-none text d-sm-inline"></span></a>
                                <a href="#"><i class="far fa-calendar-times"></i> <span
                                            class="d-none text d-sm-inline"></span></a>
                                <a href="#"><i class="fab fa-twitter"></i> <span class="d-none text d-sm-inline"></span></a>
                                <a href="#"><i class="far fa-envelope"></i> <span
                                            class="d-none text d-sm-inline"></span></a>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="col-12 col-sm-12 col-md-6 col-lg-6 p-1 p-md-5 tutorial-right-section">
                    <h2 class="home-header-cta flex-nowrap">An Expedition</h2>
                    <div class="card mb-4 box-shadow" data-aos="fade-up" data-aos-duration="1500" data
                         data-aos-anchor-placement="bottom-bottom" data-aos-once="true">
                        <!-- overlay -->
                        <div id="overlay">
                            <div class="overlay-text">
                                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
                                    tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
                                    quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
                                    consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse
                                    cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non
                                    proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
                            </div>
                        </div>
                        <!-- end overlay -->

                        <img class="card-img-top" src="/images/card-exp-image.jpg" alt="Card image cap">
                        <a href="#" class="View-overlay"><h2 class="card-title">Expedition Title Name Here <i
                                        class="fa fa-angle-right text-white align-middle"> </i></h2></a>

                        <div class="card-body text-center">
                            <div class="d-flex align-items-start justify-content-between mb-2">
                                <p><a href="#" class="color-action"><i class="fas fa-project-diagram color-action"></i>
                                        Project Name Here</a></p>
                                <p>53% Complete</p>
                            </div>

                            <div class="d-flex align-items-start justify-content-between">
                                <p><a href="#"><i class="far fa-share-square"></i> Share</a></p>
                                <p><a href="#"><i class="far fa-keyboard"></i> Participate</a></p>
                            </div>
                            <!-- Button trigger modal -->
                            <button type="button" class="btn btn-outline-primary mt-3 text-center" data-toggle="modal"
                                    data-target="#ModalCenter">
                                Launch Scoreboard
                            </button>
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
                        <span class="smallertext">Specimen digitization is easy as 123</span></h2>
                    <ul id="externalIndicators" class="list-inline">
                        <li id="carousel-li-0" data-target="#processCarousel" data-slide-to="0" class="active list-inline-item">1</li>
                        <li id="carousel-li-1" data-target="#processCarousel" data-slide-to="1" class="list-inline-item">2</li>
                        <li id="carousel-li-2" data-target="#processCarousel" data-slide-to="2" class="list-inline-item">3</li>
                    </ul>
                </div>
                <div id="processCarousel" class="carousel slide col-md-6">
                    <div class="carousel-inner box-shadow inside-carousel">
                        <div class="carousel-item active" style="background-image: url(/images/slider/slider1.png);">
                            <div class="circle-slider p-5">
                                <h3 class="text-center">PROJECT</h3>
                                <p>Create a Project for your digital images and use BIOSPEX to run optical character
                                    recognition (OCR) on the images. Bundle the specimen images using the OCR text
                                    string,
                                    or similar traits like State, County, or collection date, into various
                                    Expeditions
                                    that will ignite public interest.</p>
                            </div>
                        </div>
                        <div class="carousel-item" style="background-image: url(/images/slider/slider2.png);">
                            <div class="circle-slider p-5">
                                <h3 class="text-center">EXPEDITIONS</h3>
                                <p>The curator then uses BIOSPEX to deploy the expeditions a few at a time to an
                                    existing
                                    website with a large citizen science community for label transcription.</p>
                            </div>
                        </div>
                        <div class="carousel-item" style="background-image: url(/images/slider/slider3.png);">
                            <div class="circle-slider p-5">
                                <h3 class="text-center">EXPORT</h3>
                                <p>The curator processes the resulting transcriptions in BIOSPEX later and exports
                                    the data back to her local data management system.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
@section('custom-script')
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        AOS.init({
            mirror: true
        });
    </script>
    <script src="https://cdn.jsdelivr.net/gh/cferdinandi/smooth-scroll/dist/smooth-scroll.polyfills.min.js"></script>
    <script>var scroll = new SmoothScroll('a[href*="#"]');</script>
@endsection