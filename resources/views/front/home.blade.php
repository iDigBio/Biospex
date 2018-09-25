@extends('front.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ __('Lead Public Digitization Expeditions') }}
@stop

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
                    <h1 class="text-white align-middle home-banner-tag">{{ __('Provision, advertise and lead expeditions.') }}<br>
                        <a href="#learn-more" data-scroll class="btn btn-primary mt-4" data-aos="fade-right" data-aos-easing="ease-out" data-aos-duration="3000">Learn More</a>
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
            <h1>{{ __('BIOSPEX Expeditions') }}</h1>
            <p>{{ __('BIOSPEX Expeditions are your way to contribute to the digitization of collections all over the world') }}</p>
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

                    <div class="card-project mb-4 px-4 box-shadow" data-aos="fade-down" data-aos-easing="ease-in" data-aos-duration="2000" data-aos-once="true">
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
                                <img class="img-fluid" src="/images/we-dig-fl-plants.svg" alt="Card image cap">
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
                    <div class="card mb-4 box-shadow" data-aos="fade-up" data-aos-duration="1500" data data-aos-anchor-placement="bottom-bottom" data-aos-once="true">
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
@endsection
@section('footer')
    @include('common.footer')
@endsection